<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiClient extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'account_id',
        'name',
        'key_id',
        'secret_hash',
        'scopes',
        'rate_limit_per_minute',
        'status',
        'last_used_at',
    ];

    protected $casts = [
        'scopes' => 'array',
        'rate_limit_per_minute' => 'integer',
        'last_used_at' => 'datetime',
    ];

    protected $hidden = [
        'secret_hash',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public static function generateCredentials(Account $account, string $name, array $scopes = ['analyses:read', 'analyses:write']): array
    {
        $keyId = 'ons_live_' . Str::random(24);
        $rawSecret = 'sec_' . Str::random(40);

        $client = static::create([
            'account_id' => $account->id,
            'name' => $name,
            'key_id' => $keyId,
            'secret_hash' => hash('sha256', $rawSecret),
            'scopes' => $scopes,
            'status' => 'active',
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
}
