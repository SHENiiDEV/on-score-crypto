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
        'max_api_keys',
        'default_rate_limit',
        'low_balance_threshold',
        'contact_email',
        'self_service_keys',
        'scoring_rules',
        'webhook_url',
        'webhook_secret',
        'settings',
    ];

    protected $casts = [
        'credit_balance' => 'integer',
        'max_api_keys' => 'integer',
        'default_rate_limit' => 'integer',
        'low_balance_threshold' => 'integer',
        'self_service_keys' => 'boolean',
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

    public function activeApiClients(): HasMany
    {
        return $this->hasMany(ApiClient::class)->where('status', 'active');
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

    /**
     * How many more self-service keys this merchant may still provision.
     */
    public function remainingKeySlots(): int
    {
        $limit = (int) ($this->max_api_keys ?: 0);

        if ($limit <= 0) {
            return 0;
        }

        return max(0, $limit - $this->apiClients()->where('status', 'active')->count());
    }

    public function canProvisionKey(): bool
    {
        return $this->status === 'active'
            && (bool) $this->self_service_keys
            && $this->remainingKeySlots() > 0;
    }

    public function isLowBalance(): bool
    {
        $threshold = (int) ($this->low_balance_threshold ?: 0);

        return $threshold > 0 && $this->credit_balance <= $threshold;
    }

    /**
     * Merged scoring configuration: platform defaults overridden by client rules.
     */
    public function effectiveScoringRules(): array
    {
        $rules = is_array($this->scoring_rules) ? $this->scoring_rules : [];

        return [
            'super_vip_threshold_usd' => (float) ($rules['super_vip_threshold_usd'] ?? 400000),
            'potential_vip_threshold_usd' => (float) ($rules['potential_vip_threshold_usd'] ?? 100000),
            'high_value_threshold_usd' => (float) ($rules['high_value_threshold_usd'] ?? 25000),
            'custom_rules' => array_values((array) ($rules['custom_rules'] ?? $rules['rules'] ?? [])),
        ];
    }
}
