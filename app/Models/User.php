<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /** Roles that belong to a merchant (B2B client) workspace. */
    public const MERCHANT_ROLES = ['merchant_owner', 'merchant_member', 'user'];

    protected $fillable = [
        'account_id',
        'name',
        'email',
        'password',
        'role',
        'status',
        'must_change_password',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'must_change_password' => 'boolean',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['superadmin', 'admin'], true);
    }

    public function isMerchant(): bool
    {
        return ! $this->isAdmin() && in_array($this->role, self::MERCHANT_ROLES, true);
    }

    public function isMerchantOwner(): bool
    {
        return $this->role === 'merchant_owner';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            'superadmin' => 'Super Admin',
            'admin' => 'Administrator',
            'merchant_owner' => 'Account Owner',
            'merchant_member' => 'Team Member',
            default => 'Member',
        };
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim((string) $this->name)) ?: [];
        $letters = array_map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)), array_slice($parts, 0, 2));

        return implode('', $letters) ?: mb_strtoupper(mb_substr((string) $this->email, 0, 2));
    }
}
