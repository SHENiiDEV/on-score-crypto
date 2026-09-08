<?php

namespace App\Services\EntityAttribution;

use App\Models\Entity;
use App\Models\EntityAddress;
use App\Models\Wallet;
use App\Services\Blockchain\DTO\TransactionDTO;
use App\Services\Blockchain\DTO\WalletOnchainData;

class EntityAttributionService
{
    /**
     * Cache of normalized entity addresses for fast matching
     * @var array<string, array>
     */
    private array $knownAddressesCache = [];

    public function __construct()
    {
        $this->loadKnownAddresses();
    }

    private function loadKnownAddresses(): void
    {
        $addresses = EntityAddress::with('entity')
            ->where('status', 'active')
            ->get();

        foreach ($addresses as $item) {
            $key = strtolower($item->network) . ':' . $item->normalized_address;
            $this->knownAddressesCache[$key] = [
                'entity_id' => $item->entity_id,
                'entity_name' => $item->entity->name,
                'entity_slug' => $item->entity->slug,
                'category' => $item->entity->category,
                'subtype' => $item->entity->subtype,
                'is_custodial_cex' => (bool)$item->entity->is_custodial_cex,
                'label' => $item->label,
                'confidence' => (float)$item->confidence,
                'source' => $item->source,
            ];
        }
    }

    public function matchAddress(string $network, string $address): ?array
    {
        $normalized = Wallet::normalize($network, $address);
        $key = strtolower($network) . ':' . $normalized;

        return $this->knownAddressesCache[$key] ?? null;
    }

    public function isCustodialCex(string $network, string $address): bool
    {
        $matched = $this->matchAddress($network, $address);
        return $matched ? $matched['is_custodial_cex'] : false;
    }

    /**
     * Process wallet data: classify counterparties, filter out CEX collective funds,
     * calculate gambling and turnover metrics with explainable provenance.
     */
    public function analyzeAttribution(WalletOnchainData $rawData): array
    {
        $network = $rawData->network;
        $targetAddress = $rawData->address;

        $targetAttribution = $this->matchAddress($network, $targetAddress);
        $isTargetCex = $targetAttribution && $targetAttribution['is_custodial_cex'];

        $counterparties = [];
        $gamblingTransactions = [];
        $cexFundingTransactions = [];
        $totalTurnover30d = 0.0;
        $totalTurnover90d = 0.0;
        $totalTurnover365d = 0.0;
        $totalTurnoverLifetime = 0.0;

        $inflow365d = 0.0;
        $outflow365d = 0.0;

        $gamblingOutgoing365d = 0.0;
        $gamblingIncoming365d = 0.0;
        $gamblingEntitiesMap = [];
        $firstGamblingTx = null;
        $lastGamblingTx = null;

        $now = new \DateTimeImmutable();

        foreach ($rawData->transactions as $tx) {
            $otherAddress = ($tx->direction === 'in') ? $tx->fromAddress : $tx->toAddress;
            $matched = $this->matchAddress($network, $otherAddress);

            $usdVal = $tx->usdValue;
            $daysDiff = $now->diff($tx->timestamp)->days;

            // Turnover calculation
            if ($daysDiff <= 30) $totalTurnover30d += $usdVal;
            if ($daysDiff <= 90) $totalTurnover90d += $usdVal;
            if ($daysDiff <= 365) {
                $totalTurnover365d += $usdVal;
                if ($tx->direction === 'in') {
                    $inflow365d += $usdVal;
                } else {
                    $outflow365d += $usdVal;
                }
            }
            $totalTurnoverLifetime += $usdVal;

            if ($matched) {
                $entityName = $matched['entity_name'];
                if (!isset($counterparties[$entityName])) {
                    $counterparties[$entityName] = [
                        'entity_id' => $matched['entity_id'],
                        'name' => $entityName,
                        'category' => $matched['category'],
                        'subtype' => $matched['subtype'],
                        'confidence' => $matched['confidence'],
                        'tx_count' => 0,
                        'total_volume_usd' => 0.0,
                    ];
                }
                $counterparties[$entityName]['tx_count']++;
                $counterparties[$entityName]['total_volume_usd'] += $usdVal;

                // Handle CEX Inflows (Direct withdrawals to personal wallet)
                if ($matched['category'] === 'cex' && $tx->direction === 'in') {
                    $cexFundingTransactions[] = [
                        'tx_hash' => $tx->txHash,
                        'cex' => $entityName,
                        'amount_usd' => $usdVal,
                        'timestamp' => $tx->timestamp->format(\DateTimeInterface::ATOM),
                    ];
                }

                // Handle Gambling Transactions
                if ($matched['category'] === 'gambling') {
                    $gamblingEntitiesMap[$entityName] = true;
                    $gamblingTransactions[] = [
                        'tx_hash' => $tx->txHash,
                        'entity' => $entityName,
                        'amount_usd' => $usdVal,
                        'direction' => $tx->direction,
                        'timestamp' => $tx->timestamp->format(\DateTimeInterface::ATOM),
                        'confidence' => $matched['confidence'],
                    ];

                    if ($daysDiff <= 365) {
                        if ($tx->direction === 'out') {
                            $gamblingOutgoing365d += $usdVal;
                        } else {
                            $gamblingIncoming365d += $usdVal;
                        }
                    }

                    if ($firstGamblingTx === null || $tx->timestamp < $firstGamblingTx) {
                        $firstGamblingTx = $tx->timestamp;
                    }
                    if ($lastGamblingTx === null || $tx->timestamp > $lastGamblingTx) {
                        $lastGamblingTx = $tx->timestamp;
                    }
                }
            }
        }

        // Adjust visible assets if the target itself is an exchange pool
        $adjustedVisibleAssetsUsd = $rawData->visibleTotalBalanceUsd;
        $warnings = [];

        if ($isTargetCex) {
            $adjustedVisibleAssetsUsd = 0.0; // Exclude exchange collective pool balance
            $warnings[] = [
                'code' => 'TARGET_IS_CUSTODIAL_CEX_POOL',
                'message' => "The analyzed address is a known custodial exchange pool ({$targetAttribution['entity_name']}). Collective pool balance is not attributed to a single player.",
            ];
        }

        $gamblingDetected = count($gamblingEntitiesMap) > 0 ? 'detected' : 'not_detected';
        $totalGamblingFlow365d = $gamblingOutgoing365d + $gamblingIncoming365d;

        return [
            'is_target_cex' => $isTargetCex,
            'target_entity' => $targetAttribution,
            'adjusted_visible_assets_usd' => round($adjustedVisibleAssetsUsd, 2),
            'raw_visible_assets_usd' => round($rawData->visibleTotalBalanceUsd, 2),
            'warnings' => $warnings,
            'turnover' => [
                '30d_usd' => round($totalTurnover30d, 2),
                '90d_usd' => round($totalTurnover90d, 2),
                '365d_usd' => round($totalTurnover365d, 2),
                'lifetime_usd' => round($totalTurnoverLifetime, 2),
                'inflow_365d_usd' => round($inflow365d, 2),
                'outflow_365d_usd' => round($outflow365d, 2),
                'transaction_count' => count($rawData->transactions),
            ],
            'gambling' => [
                'status' => $gamblingDetected,
                'entities_count' => count($gamblingEntitiesMap),
                'entities_list' => array_keys($gamblingEntitiesMap),
                'outgoing_365d_usd' => round($gamblingOutgoing365d, 2),
                'incoming_365d_usd' => round($gamblingIncoming365d, 2),
                'total_flow_365d_usd' => round($totalGamblingFlow365d, 2),
                'first_gambling_activity' => $firstGamblingTx?->format(\DateTimeInterface::ATOM),
                'last_gambling_activity' => $lastGamblingTx?->format(\DateTimeInterface::ATOM),
                'gambling_share_of_turnover' => $totalTurnover365d > 0 ? round(($totalGamblingFlow365d / $totalTurnover365d), 4) : 0,
                'transactions_sample' => array_slice($gamblingTransactions, 0, 10),
            ],
            'counterparties' => array_values($counterparties),
            'cex_funding' => [
                'detected' => count($cexFundingTransactions) > 0,
                'transfers_count' => count($cexFundingTransactions),
                'sample' => array_slice($cexFundingTransactions, 0, 5),
            ],
        ];
    }
}
