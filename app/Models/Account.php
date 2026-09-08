<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'slug',
        'credit_balance',
        'status',
        'scoring_rules',
        'webhook_url',
        'webhook_secret',
        'settings',
    ];

    protected $casts = [
        'credit_balance' => 'integer',
        'settings' => 'array',
        'scoring_rules' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function apiClients(): HasMany
    {
        return $this->hasMany(ApiClient::class);
    }

    public function creditLedgerEntries(): HasMany
    {
        return $this->hasMany(CreditLedger::class);
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(Analysis::class);
    }

    public function batchJobs(): HasMany
    {
        return $this->hasMany(BatchJob::class);
    }

    public function hasSufficientCredits(int $amount): bool
    {
        return $this->credit_balance >= $amount;
    }
}
