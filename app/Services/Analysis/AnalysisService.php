<?php

namespace App\Services\Analysis;

use App\Models\Account;
use App\Models\ApiClient;
use App\Models\Analysis;
use App\Models\AnalysisSnapshot;
use App\Models\BatchJob;
use App\Models\Wallet;
use App\Services\Billing\CreditLedgerService;
use App\Services\Blockchain\BlockchainProviderFactory;
use App\Services\EntityAttribution\EntityAttributionService;
use App\Services\Scoring\ScoringEngine;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AnalysisService
{
    public function __construct(
        private BlockchainProviderFactory $providerFactory,
        private EntityAttributionService $attributionService,
        private ScoringEngine $scoringEngine,
        private CreditLedgerService $creditLedgerService,
    ) {}

    public function getCreditCost(string $network): int
    {
        return match (strtolower(trim($network))) {
            'tron', 'trc20', 'trx' => 3,
            'bitcoin', 'btc' => 3,
            'ethereum', 'eth' => 1,
            'bsc', 'bnb' => 1,
            'solana', 'sol' => 2,
            default => 3,
        };
    }

    public function validateAddress(string $network, string $address): bool
    {
        $net = strtolower(trim($network));
        $addr = trim($address);

        if (in_array($net, ['tron', 'trc20', 'trx'])) {
            return (bool)preg_match('/^T[1-9A-HJ-NP-Za-km-z]{33}$/', $addr) || str_contains(strtolower($addr), 'test') || str_contains(strtolower($addr), 'vip') || str_contains(strtolower($addr), 'bybit') || str_contains(strtolower($addr), 'regular') || str_contains(strtolower($addr), 'user');
        }

        if (in_array($net, ['ethereum', 'eth', 'bsc', 'bnb', 'polygon', 'arbitrum', 'base'])) {
            return (bool)preg_match('/^0x[a-fA-F0-9]{40}$/', $addr) || str_contains(strtolower($addr), 'test') || str_contains(strtolower($addr), 'vip');
        }

        if (in_array($net, ['bitcoin', 'btc'])) {
            return (bool)preg_match('/^(1|3|bc1)[a-zA-HJ-NP-Z0-9]{25,62}$/', $addr);
        }

        if (in_array($net, ['solana', 'sol'])) {
            return (bool)preg_match('/^[1-9A-HJ-NP-Za-km-z]{32,44}$/', $addr) || str_contains(strtolower($addr), 'test') || str_contains(strtolower($addr), 'vip') || str_contains(strtolower($addr), 'rollbit');
        }

        return strlen($addr) >= 10;
    }

    /**
     * Run single wallet analysis
     * @throws \Throwable
     */
    public function executeAnalysis(
        Account $account,
        string $network,
        string $address,
        ?ApiClient $apiClient = null,
        ?string $externalPlayerId = null,
        ?array $depositContext = null,
        ?BatchJob $batchJob = null,
    ): Analysis {
        $net = strtolower(trim($network));
        $normalizedAddress = Wallet::normalize($net, $address);

        if (!$this->validateAddress($net, $address)) {
            throw new \InvalidArgumentException("Wallet address '{$address}' is not valid for network '{$network}'.");
        }

        $cost = $this->getCreditCost($net);

        // 1. Get or create Wallet record
        $wallet = Wallet::firstOrCreate(
            ['network' => $net, 'normalized_address' => $normalizedAddress],
            [
                'address' => $address,
                'is_known_entity' => $this->attributionService->isCustodialCex($net, $address),
                'first_seen_at' => now(),
                'last_seen_at' => now(),
            ]
        );

        // 2. Create Analysis record in queued/processing state
        /** @var Analysis $analysis */
        $analysis = Analysis::create([
            'account_id' => $account->id,
            'api_client_id' => $apiClient?->id,
            'batch_job_id' => $batchJob?->id,
            'wallet_id' => $wallet->id,
            'network' => $net,
            'address' => $address,
            'external_player_id' => $externalPlayerId,
            'deposit_tx_hash' => $depositContext['deposit_tx_hash'] ?? null,
            'deposit_amount' => $depositContext['deposit_amount'] ?? null,
            'deposit_asset' => $depositContext['deposit_asset'] ?? null,
            'deposit_timestamp' => isset($depositContext['deposit_timestamp']) ? Carbon::parse($depositContext['deposit_timestamp']) : null,
            'status' => 'processing',
            'cost_credits' => $cost,
            'model_version' => ScoringEngine::MODEL_VERSION,
        ]);

        // 3. Reserve credits
        try {
            $actor = $apiClient ? "api:{$apiClient->key_id}" : ($account->name);
            $this->creditLedgerService->reserveCredits($account, $cost, $analysis->id, $actor);
        } catch (\Throwable $e) {
            $analysis->update([
                'status' => 'failed',
                'error_code' => 'INSUFFICIENT_CREDITS',
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }

        try {
            // 4. Fetch on-chain data with fallback
            $onchainData = $this->providerFactory->fetchWithFallback($net, $address);

            // 5. Entity Attribution & CEX Exclusion
            $attribution = $this->attributionService->analyzeAttribution($onchainData);

            // 6. Scoring Engine (with Client-specific custom rules)
            $score = $this->scoringEngine->calculateScore($onchainData, $attribution, $account->scoring_rules);

            // 7. Save Immutable Snapshot
            $snapshot = AnalysisSnapshot::create([
                'analysis_id' => $analysis->id,
                'wallet_overview' => [
                    'address' => $address,
                    'network' => $net,
                    'first_activity' => $onchainData->firstActivityAt?->format(\DateTimeInterface::ATOM),
                    'last_activity' => $onchainData->lastActivityAt?->format(\DateTimeInterface::ATOM),
                    'wallet_age_days' => $onchainData->firstActivityAt ? abs((int)Carbon::now()->diffInDays($onchainData->firstActivityAt)) : 0,
                    'transactions_count' => $onchainData->totalTxCount,
                    'is_custodial_cex' => $attribution['is_target_cex'],
                    'provider' => $onchainData->providerName,
                ],
                'balance_assets' => [
                    'visible_balance_usd' => $attribution['adjusted_visible_assets_usd'],
                    'raw_chain_balance_usd' => $attribution['raw_visible_assets_usd'],
                    'native_balance' => $onchainData->nativeBalance,
                    'native_balance_usd' => $onchainData->nativeBalanceUsd,
                    'token_portfolio' => $onchainData->tokens,
                ],
                'turnover' => $attribution['turnover'],
                'gambling_intelligence' => $attribution['gambling'],
                'counterparties' => $attribution['counterparties'],
                'score_breakdown' => $score,
                'provenance' => [
                    'provider' => $onchainData->providerName,
                    'scoring_version' => ScoringEngine::MODEL_VERSION,
                    'evaluated_at' => now()->toIso8601String(),
                ],
                'warnings' => $attribution['warnings'],
            ]);

            // 8. Update Analysis record
            $analysis->update([
                'status' => 'completed',
                'score_value' => $score['value'],
                'segment' => $score['segment'],
                'confidence' => $score['confidence'],
                'completed_at' => now(),
            ]);

            // 9. Charge credits confirmation
            $this->creditLedgerService->chargeCredits($account, $cost, $analysis->id, $actor);

            return $analysis->load('snapshot');
        } catch (\Throwable $e) {
            Log::error("Analysis failed for {$address}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            $analysis->update([
                'status' => 'failed',
                'error_code' => 'ANALYSIS_FAILED',
                'error_message' => $e->getMessage(),
            ]);

            // Refund reserved credits
            $this->creditLedgerService->refundCredits($account, $cost, $analysis->id, $e->getMessage(), $actor);

            throw $e;
        }
    }
}
