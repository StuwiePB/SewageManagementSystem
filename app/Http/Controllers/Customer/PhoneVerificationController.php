<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\PhoneOtpService;
use App\Support\BruneiPhone;
use App\Support\PhoneVerificationSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class PhoneVerificationController extends Controller
{
    public function send(Request $request, PhoneOtpService $otp): JsonResponse
    {
        $request->validate([
            'phone' => [
                'required',
                'string',
                'max:30',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! BruneiPhone::isValid((string) $value)) {
                        $fail(__('Please enter a valid Brunei phone number (+673…).'));
                    }
                },
            ],
        ]);

        $phone = (string) $request->input('phone');

        $ipKey = 'otp-send-ip:'.sha1($request->ip());
        $phoneKey = 'otp-send-phone:'.sha1(BruneiPhone::normalize($phone));

        if (RateLimiter::tooManyAttempts($ipKey, 30)) {
            return response()->json(['message' => __('Too many requests. Try again later.')], 429);
        }

        if (RateLimiter::tooManyAttempts($phoneKey, 5)) {
            return response()->json(['message' => __('Too many codes sent to this number. Try again later.')], 429);
        }

        RateLimiter::hit($ipKey, 3600);
        RateLimiter::hit($phoneKey, 3600);

        try {
            $otp->send($phone);
        } catch (\InvalidArgumentException) {
            throw ValidationException::withMessages([
                'phone' => [__('Please enter a valid Brunei phone number (+673…).')],
            ]);
        } catch (\RuntimeException $e) {
            return match ($e->getMessage()) {
                'sms_not_configured' => response()->json(['message' => __('SMS is not configured.')], 503),
                'telesign_trial_destination' => response()->json([
                    'message' => __(
                        'Telesign trial error -10033: this number is not authorized yet. In the portal (Numbers and senders → test numbers) you must complete verification—Telesign texts or calls you a code; enter that code in the portal. Listing the number alone is not enough. Check that your .env Customer ID matches this portal and the app uses the exact same number.'
                    ),
                ], 422),
                'telesign_product_disabled' => response()->json([
                    'message' => __('SMS Verify is not enabled for this Telesign customer. Enable “SMS Verify” (or contact Telesign support) in your portal, then try again.'),
                ], 503),
                'sms_delivery_failed' => response()->json(['message' => __('Unable to send SMS. Try again later.')], 503),
                default => throw $e,
            };
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => __('Unable to send SMS. Try again later.')], 503);
        }

        return response()->json(['ok' => true]);
    }

    public function verify(Request $request, PhoneOtpService $otp): JsonResponse
    {
        $request->validate([
            'phone' => [
                'required',
                'string',
                'max:30',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! BruneiPhone::isValid((string) $value)) {
                        $fail(__('Please enter a valid Brunei phone number (+673…).'));
                    }
                },
            ],
            'code' => ['required', 'string', 'regex:/^[0-9]{6}$/'],
        ]);

        $ok = $otp->verify((string) $request->input('phone'), (string) $request->input('code'));

        if (! $ok) {
            return response()->json(['message' => __('Invalid or expired code.')], 422);
        }

        return response()->json(['ok' => true]);
    }

    public function clear(): JsonResponse
    {
        PhoneVerificationSession::clear();

        return response()->json(['ok' => true]);
    }
}
