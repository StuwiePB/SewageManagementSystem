<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles {
        HasRoles::hasRole as spatieHasRole;
    }

    use Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'staffname',
        'name',
        'email',
        'phone',
        'password',
        'role',
        'profile_photo_path',
        'preference_appearance',
        'preference_language',
        'preference_anonymous',
        'crew_id',
        'is_active',
    ];

    public const ROLE_SUPER_ADMIN = 'super_admin';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_OPERATOR = 'operator';

    public const ROLE_CUSTOMER = 'customer';

    /** Danny admin / operations (crew leader for workers portal) */
    public const ROLE_CREW_LEADER = 'crew_leader';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get URL-safe slug from name (e.g. "Qasiemul Hisyam" -> "qasiemulhisyam")
     */
    public function profileSlug(): string
    {
        return Str::slug($this->name ?? 'user', '');
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(self::ROLE_SUPER_ADMIN);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN) || $this->isSuperAdmin();
    }

    public function isOperator(): bool
    {
        return $this->hasRole(self::ROLE_OPERATOR);
    }

    public function isCustomer(): bool
    {
        return $this->hasRole(self::ROLE_CUSTOMER);
    }

    /**
     * Check role - fallback to users.role column when Spatie has no role assigned.
     */
    public function hasRole($roles, $guard = null): bool
    {
        if ($this->spatieHasRole($roles, $guard)) {
            return true;
        }
        $roles = \Illuminate\Support\Arr::wrap($roles);
        $userRole = $this->role ?? null;
        $userRole = match ($userRole) {
            'operation' => self::ROLE_OPERATOR,
            default => $userRole,
        };

        return $userRole && in_array($userRole, $roles, true);
    }

    public function crew(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Crew::class);
    }

    public function reports(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function supportMessages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SupportMessage::class);
    }

}
