<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entity extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'subtype',
        'is_custodial_cex',
        'status',
    ];

    protected $casts = [
        'is_custodial_cex' => 'boolean',
    ];

    public function addresses(): HasMany
    {
        return $this->hasMany(EntityAddress::class);
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }
}
