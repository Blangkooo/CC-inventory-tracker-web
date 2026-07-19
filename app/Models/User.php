<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

#[Fillable(['name', 'email', 'password', 'pin', 'role', 'branch_id'])]
#[Hidden(['password', 'pin', 'remember_token'])]
class User extends Authenticatable implements JWTSubject
{
    public const ROLE_SUPER_ADMIN = 'super_admin';

    public const ROLE_MANAGER = 'manager';

    public const ROLE_STAFF = 'staff';

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            'pin' => 'hashed',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function profile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(WorkerProfile::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function shiftLogs(): HasMany
    {
        return $this->hasMany(ShiftLog::class);
    }

    /**
     * Not named notifications() to avoid conflicting with Notifiable::notifications().
     */
    public function alertNotifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    /**
     * Legacy alias — teammates' Blade views still call isOwner(); "owner"
     * became the super_admin role in the 3-tier RBAC migration.
     */
    public function isOwner(): bool
    {
        return $this->isSuperAdmin();
    }

    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /**
     * Super admin or manager — the two "admin panel" (email/password) roles,
     * as opposed to staff who authenticate via pin+branch.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_SUPER_ADMIN, self::ROLE_MANAGER);
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * @return array<string, mixed>
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
