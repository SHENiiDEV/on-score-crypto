<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Analysis extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'account_id',
        'api_client_id',
        'batch_job_id',
        'wallet_id',
        'network',
        'address',
        'external_player_id',
        'deposit_tx_hash',
        'deposit_amount',
        'deposit_asset',
        'deposit_timestamp',
        'status',
        'cost_credits',
        'score_value',
        'segment',
        'confidence',
        'model_version',
        'error_code',
        'error_message',
        'completed_at',
    ];

    protected $casts = [
        'deposit_amount' => 'decimal:8',
        'deposit_timestamp' => 'datetime',
        'cost_credits' => 'integer',
        'score_value' => 'integer',
        'confidence' => 'float',
        'completed_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function apiClient(): BelongsTo
    {
        return $this->belongsTo(ApiClient::class);
    }

    public function batchJob(): BelongsTo
    {
        return $this->belongsTo(BatchJob::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function snapshot(): HasOne
    {
        return $this->hasOne(AnalysisSnapshot::class);
    }
}
