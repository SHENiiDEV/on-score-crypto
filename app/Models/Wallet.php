<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'network',
        'address',
        'normalized_address',
        'is_known_entity',
        'entity_id',
        'first_seen_at',
        'last_seen_at',
    ];

    protected $casts = [
        'is_known_entity' => 'boolean',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(Analysis::class);
    }

    public static function normalize(string $network, string $address): string
    {
        $net = strtolower(trim($network));
        $addr = trim($address);

        if (in_array($net, ['ethereum', 'eth', 'bsc', 'bnb', 'polygon', 'arbitrum', 'base'])) {
            return strtolower($addr);
        }

        // TRON, Solana, Bitcoin addresses are case-sensitive Base58/Base58Check/Bech32
        return $addr;
    }
}
