<?php

namespace App\Support;

final class AccountEmail
{
    public const ADMIN_SUFFIX = '@admin.brudms.jkr.bn';

    public const OPERATION_SUFFIX = '@operation.brudms.jkr.bn';

    public static function endsWith(string $email, string $suffix): bool
    {
        return str_ends_with(strtolower($email), strtolower($suffix));
    }
}
