<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ApiClient extends Model
{
    use HasFactory, HasUlids;

    public const DEFAULT_SCOPES = [
        'analyses:read',
        'analyses:write',
        'batches:read',
        'batches:write',
        'credits:read',
    ];

    protected $fillable = [
        'account_id',
        'name',
        'key_id',
        'secret_hash',
        'scopes',
        'rate_limit_per_minute',
        'status',
        'created_by',
        'revoked_at',
        'last_used_at',
        'last_used_ip',
    ];

    protected $casts = [
        'scopes' => 'array',
        'rate_limit_per_minute' => 'integer',
        'last_used_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    protected $hidden = [
        'secret_hash',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(Analysis::class);
    }

    public static function generateCredentials(
        Account $account,
        string $name,
        array $scopes = self::DEFAULT_SCOPES,
        ?string $createdBy = null,
        ?int $rateLimit = null,
    ): array {
        $keyId = 'ons_live_' . Str::random(24);
        $rawSecret = 'sec_' . Str::random(40);

        $client = static::create([
            'account_id' => $account->id,
            'name' => $name,
            'key_id' => $keyId,
            'secret_hash' => hash('sha256', $rawSecret),
            'scopes' => $scopes,
            'rate_limit_per_minute' => $rateLimit ?: ($account->default_rate_limit ?: 120),
            'status' => 'active',
            'created_by' => $createdBy,
        ]);

        return [
            'client' => $client,
            'key_id' => $keyId,
            'secret' => $rawSecret,
        ];
    }

    public function verifySecret(string $plainSecret): bool
    {
        return hash_equals($this->secret_hash, hash('sha256', $plainSecret));
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /** Short, safe preview of the key id for tables and logs. */
    public function maskedKeyId(): string
    {
        $key = (string) $this->key_id;

        if (strlen($key) <= 16) {
            return $key;
        }

        return substr($key, 0, 13) . '••••' . substr($key, -6);
    }
}
