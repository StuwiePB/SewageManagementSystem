<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class DmsFormDateTime
{
    public static function parse(?string $value): ?CarbonInterface
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    public static function toStorage(?string $value): ?string
    {
        $parsed = self::parse($value);

        return $parsed?->format('Y-m-d H:i:s');
    }
}
