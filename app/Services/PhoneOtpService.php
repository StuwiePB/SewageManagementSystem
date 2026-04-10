<?php

namespace App\Services;

use App\Support\BruneiPhone;
use App\Support\PhoneVerificationSession;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class PhoneOtpService
{
    public function send(string $rawPhone): void
    {
        if (! BruneiPhone::isValid($rawPhone)) {
            throw new \InvalidArgumentException('invalid_phone');
        }

        $normalized = BruneiPhone::normalize($rawPhone);

        PhoneVerificationSession::clear();

        $code = $this->generateCode();
        $payload = [
            'hash' => hash('sha256', $code),
            'attempts' => 0,
        ];
        Cache::put($this->cacheKey($normalized), $payload, now()->addMinutes(10));

        $this->dispatchSms($normalized, $code);
    }

    public function verify(string $rawPhone, string $code): bool
    {
        if (! BruneiPhone::isValid($rawPhone)) {
            return false;
        }

        $normalized = BruneiPhone::normalize($rawPhone);
        $key = $this->cacheKey($normalized);
        $payload = Cache::get($key);

        if (! is_array($payload) || empty($payload['hash'])) {
            return false;
        }

        if (($payload['attempts'] ?? 0) >= 5) {
            Cache::forget($key);

            return false;
        }

        $digits = preg_replace('/\D+/', '', $code) ?? '';
        if (strlen($digits) !== 6) {
            return false;
        }

        if (! hash_equals($payload['hash'], hash('sha256', $digits))) {
            $payload['attempts'] = ($payload['attempts'] ?? 0) + 1;
            if ($payload['attempts'] >= 5) {
                Cache::forget($key);
            } else {
                Cache::put($key, $payload, now()->addMinutes(10));
            }

            return false;
        }

        Cache::forget($key);
        PhoneVerificationSession::markVerified($normalized);

        return true;
    }

    private function generateCode(): string
    {
        $fake = config('services.sms.fake_otp');
        if (is_string($fake) && strlen($fake) === 6 && ctype_digit($fake)) {
            return $fake;
        }

        return str_pad((string) random_int(0, 999_999), 6, '0', STR_PAD_LEFT);
    }

    private function dispatchSms(string $toE164, string $code): void
    {
        if (config('services.sms.fake_otp')) {
            Log::info('Phone OTP (fake mode — no SMS sent; clear SMS_FAKE_OTP and TWILIO_FAKE_OTP for real delivery)', ['to' => $toE164, 'code' => $code]);

            return;
        }

        $driver = (string) config('services.sms.driver', 'twilio');
        if ($driver === 'http') {
            $this->sendViaHttpProvider($toE164, $code);

            return;
        }

        if ($driver === 'telesign') {
            $this->sendViaTelesign($toE164, $code);

            return;
        }

        if ($driver === 'log') {
            Log::info('Phone OTP (log driver)', ['to' => $toE164, 'code' => $code]);

            return;
        }

        $this->sendViaTwilio($toE164, $code);
    }

    private function sendViaTwilio(string $toE164, string $code): void
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');

        if (! $sid || ! $token || ! $from) {
            throw new \RuntimeException('sms_not_configured');
        }

        $client = new Client($sid, $token);
        $body = __('Your BruDMS verification code is :code. It expires in 10 minutes.', ['code' => $code]);

        $params = ['body' => $body];
        if (str_starts_with((string) $from, 'MG')) {
            $params['messagingServiceSid'] = $from;
        } else {
            $params['from'] = $from;
        }

        $client->messages->create($toE164, $params);
    }

    private function sendViaHttpProvider(string $toE164, string $code): void
    {
        $url = (string) config('services.sms.http.url');
        $apiKey = (string) config('services.sms.http.api_key');
        $sender = (string) config('services.sms.http.sender');
        $method = strtoupper((string) config('services.sms.http.method', 'POST'));

        if ($url === '' || $apiKey === '') {
            throw new \RuntimeException('sms_not_configured');
        }

        $payload = [
            'to' => $toE164,
            'message' => __('Your BruDMS verification code is :code. It expires in 10 minutes.', ['code' => $code]),
            'sender' => $sender,
        ];

        $response = Http::timeout(15)
            ->withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
                'Accept' => 'application/json',
            ])
            ->send($method, $url, ['json' => $payload]);

        if (! $response->successful()) {
            throw new \RuntimeException('sms_delivery_failed');
        }
    }

    /**
     * Telesign SMS Verify with our 6-digit code (see Telesign "verify with own OTP" flow).
     *
     * @see https://developer.telesign.com/enterprise/docs/sms-verify-api-get-started
     */
    private function sendViaTelesign(string $toE164, string $code): void
    {
        $customerId = (string) config('services.telesign.customer_id');
        $apiKey = (string) config('services.telesign.api_key');
        $host = rtrim((string) config('services.telesign.host', 'https://rest-ww.telesign.com'), '/');

        if ($customerId === '' || $apiKey === '') {
            throw new \RuntimeException('sms_not_configured');
        }

        $phoneNumber = preg_replace('/\D+/', '', $toE164) ?? '';
        if ($phoneNumber === '') {
            throw new \RuntimeException('sms_delivery_failed');
        }

        $body = [
            'phone_number' => $phoneNumber,
            'verify_code' => $code,
            'ucid' => (string) config('services.telesign.ucid', 'BACS'),
        ];

        if ($ip = $this->publicClientIp()) {
            $body['originating_ip'] = $ip;
        }

        $response = Http::timeout(20)
            ->withBasicAuth($customerId, $apiKey)
            ->asForm()
            ->acceptJson()
            ->post($host.'/v1/verify/sms', $body);

        $this->assertTelesignSendSucceeded($response);
    }

    private function publicClientIp(): ?string
    {
        $ip = request()?->ip();
        if (! is_string($ip) || $ip === '') {
            return null;
        }

        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return null;
        }

        return $ip;
    }

    /**
     * @throws \RuntimeException telesign_trial_destination|sms_delivery_failed
     */
    private function assertTelesignSendSucceeded(Response $response): void
    {
        $json = $response->json();
        $errors = data_get($json, 'errors', []);

        foreach ($this->telesignErrorRows($errors) as $row) {
            $errCode = (int) ($row['code'] ?? 0);
            if ($errCode === -10033) {
                Log::notice('Telesign -10033: trial number not authorized. Complete portal verification (SMS/call code) for this exact number; see support.telesign.com test numbers article.');

                throw new \RuntimeException('telesign_trial_destination');
            }
            if ($errCode === -20002) {
                throw new \RuntimeException('telesign_product_disabled');
            }
        }

        if (! $response->successful()) {
            Log::warning('Telesign Verify SMS failed', [
                'status' => $response->status(),
                'body' => $json ?? $response->body(),
            ]);
            throw new \RuntimeException('sms_delivery_failed');
        }

        if (is_array($errors) && $errors !== []) {
            Log::warning('Telesign Verify SMS errors in response', ['errors' => $errors]);

            throw new \RuntimeException('sms_delivery_failed');
        }

        $statusCode = data_get($json, 'status.code');
        if ($statusCode !== null && (int) $statusCode >= 400) {
            Log::warning('Telesign Verify SMS non-success status', ['status' => data_get($json, 'status')]);

            throw new \RuntimeException('sms_delivery_failed');
        }

        Log::info('Telesign Verify SMS accepted', [
            'reference_id' => data_get($json, 'reference_id'),
            'status' => data_get($json, 'status'),
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function telesignErrorRows(mixed $errors): array
    {
        if (! is_array($errors)) {
            return [];
        }

        $out = [];
        foreach ($errors as $row) {
            if (is_array($row)) {
                $out[] = $row;
            }
        }

        return $out;
    }

    private function cacheKey(string $normalized): string
    {
        return 'phone_otp_v1:'.hash('sha256', $normalized);
    }
}
