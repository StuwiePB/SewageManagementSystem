<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    public const AREA_ADMIN = 'admin';

    public const AREA_OPERATIONS = 'operations';

    public static function log(
        string $action,
        string $description,
        ?Model $subject = null,
        ?string $area = null,
        array $properties = [],
    ): AuditLog {
        $user = Auth::user();

        return AuditLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? $user?->staffname ?? 'System',
            'user_role' => $user ? self::resolveRole($user) : null,
            'area' => $area ?? self::detectArea(),
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject ? $subject->getMorphClass() : null,
            'subject_id' => $subject?->getKey(),
            'properties' => $properties ?: null,
            'ip_address' => Request::ip(),
            'created_at' => now(),
        ]);
    }

    public static function detectArea(): string
    {
        $path = Request::path();

        if (str_starts_with($path, 'operations') || str_starts_with($path, 'operator')) {
            return self::AREA_OPERATIONS;
        }

        return self::AREA_ADMIN;
    }

    public static function resolveRole(User $user): string
    {
        if ($user->isSuperAdmin()) {
            return User::ROLE_SUPER_ADMIN;
        }

        return $user->role ?? $user->getRoleNames()->first() ?? 'user';
    }
}
