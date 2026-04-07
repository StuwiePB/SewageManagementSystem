<?php

namespace App\Support;

final class BruneiPhone
{
    public static function normalize(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', trim($phone)) ?? '';

        return '+'.$digits;
    }

    public static function isValid(string $phone): bool
    {
        $trimmed = trim($phone);
        if ($trimmed === '') {
            return false;
        }

        if (! preg_match('/^\+?[0-9\s\-()]+$/', $trimmed)) {
            return false;
        }

        $digits = preg_replace('/\D+/', '', $trimmed) ?? '';

        return str_starts_with($digits, '673') && strlen($digits) === 10;
    }
}
