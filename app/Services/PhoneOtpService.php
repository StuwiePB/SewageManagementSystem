<?php

namespace App\Services;

use App\Support\BruneiPhone;
use App\Support\PhoneVerificationSession;
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
        $fake = config('services.twilio.fake_otp');
        if (is_string($fake) && strlen($fake) === 6 && ctype_digit($fake)) {
            return $fake;
        }

        return str_pad((string) random_int(0, 999_999), 6, '0', STR_PAD_LEFT);
    }

    private function dispatchSms(string $toE164, string $code): void
    {
        if (config('services.twilio.fake_otp')) {
            Log::info('Phone OTP (fake mode)', ['to' => $toE164, 'code' => $code]);

            return;
        }

        $driver = (string) config('services.sms.driver', 'twilio');
        if ($driver === 'http') {
            $this->sendViaHttpProvider($toE164, $code);

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

    private function cacheKey(string $normalized): string
    {
        return 'phone_otp_v1:'.hash('sha256', $normalized);
    }
}
