<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BatchJob extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'account_id',
        'total_wallets',
        'queued_count',
        'processing_count',
        'completed_count',
        'failed_count',
        'status',
    ];

    protected $casts = [
        'total_wallets' => 'integer',
        'queued_count' => 'integer',
        'processing_count' => 'integer',
        'completed_count' => 'integer',
        'failed_count' => 'integer',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(Analysis::class);
    }
}
