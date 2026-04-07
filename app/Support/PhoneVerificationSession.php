<?php

namespace App\Support;

use Illuminate\Support\Carbon;

final class PhoneVerificationSession
{
    private const SESSION_PHONE_KEY = 'otp_verified_phone';

    private const SESSION_EXPIRES_KEY = 'otp_verified_expires_at';

    public static function markVerified(string $normalizedPhone): void
    {
        session([
            self::SESSION_PHONE_KEY => $normalizedPhone,
            self::SESSION_EXPIRES_KEY => now()->addMinutes((int) config('services.twilio.otp_session_minutes', 30)),
        ]);
    }

    public static function isVerifiedFor(string $normalizedPhone): bool
    {
        $verified = session(self::SESSION_PHONE_KEY);
        $expires = session(self::SESSION_EXPIRES_KEY);

        if (! is_string($verified) || $verified === '' || ! $expires) {
            return false;
        }

        if ($verified !== $normalizedPhone) {
            return false;
        }

        $expiresAt = $expires instanceof Carbon ? $expires : Carbon::parse($expires);

        return now()->lessThanOrEqualTo($expiresAt);
    }

    public static function clear(): void
    {
        session()->forget([self::SESSION_PHONE_KEY, self::SESSION_EXPIRES_KEY]);
    }
}
