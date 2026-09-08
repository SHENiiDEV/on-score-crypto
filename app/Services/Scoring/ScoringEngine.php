<?php

namespace App\Services\Scoring;

use App\Services\Blockchain\DTO\WalletOnchainData;
use Carbon\Carbon;

class ScoringEngine
{
    public const MODEL_VERSION = 'WPS-v1.0';

    public function calculateScore(WalletOnchainData $rawData, array $attributionData, ?array $customRules = null): array
    {
        // Guard: If the wallet itself is an exchange pool (Bybit, etc.), it is not a player
        if ($attributionData['is_target_cex']) {
            return [
                'value' => 0,
                'segment' => 'low_value',
                'subscores' => [
                    'financial_capacity' => 0,
                    'gambling_activity' => 0,
                    'activity_recency' => 0,
                    'transaction_profile' => 0,
                ],
                'key_drivers' => [
                    'target_is_custodial_cex_pool',
                    'individual_scoring_not_applicable',
                ],
                'custom_tags' => [],
                'confidence' => 0.99,
                'model_version' => self::MODEL_VERSION,
            ];
        }

        $visibleAssets = $attributionData['adjusted_visible_assets_usd'];
        $turnover365d = $attributionData['turnover']['365d_usd'];
        $lifetimeTurnover = $attributionData['turnover']['lifetime_usd'] ?? $turnover365d;
        $gamblingFlow365d = $attributionData['gambling']['total_flow_365d_usd'];
        $gamblingOutgoing365d = $attributionData['gambling']['outgoing_365d_usd'];
        $gamblingEntitiesCount = $attributionData['gambling']['entities_count'];
        $lastGamblingActivity = $attributionData['gambling']['last_gambling_activity'];
        $lastActivityAt = $rawData->lastActivityAt ?? Carbon::now()->subMonths(6);

        $keyDrivers = [];
        $customTags = [];

        // 1. Subscore: Financial Capacity (Weight: 35%)
        $finScore = 0;
        if ($visibleAssets >= 100000) {
            $finScore = 100;
            $keyDrivers[] = 'visible_assets_gt_100k';
        } elseif ($visibleAssets >= 25000) {
            $finScore = 85 + (int)(($visibleAssets - 25000) / 75000 * 15);
            $keyDrivers[] = 'visible_assets_gt_25k';
        } elseif ($visibleAssets >= 5000) {
            $finScore = 65 + (int)(($visibleAssets - 5000) / 20000 * 20);
            $keyDrivers[] = 'visible_assets_gt_5k';
        } elseif ($visibleAssets >= 1000) {
            $finScore = 40 + (int)(($visibleAssets - 1000) / 4000 * 25);
        } elseif ($visibleAssets >= 100) {
            $finScore = 20 + (int)(($visibleAssets - 100) / 900 * 20);
        } else {
            $finScore = min(19, (int)($visibleAssets / 10));
        }

        // Add points for total turnover liquidity
        if ($turnover365d > 500000) {
            $finScore = min(100, $finScore + 15);
            $keyDrivers[] = 'high_annual_turnover_gt_500k';
        }

        // 2. Subscore: Gambling Activity (Weight: 35%)
        $gmbScore = 0;
        if ($gamblingFlow365d >= 100000) {
            $gmbScore = 100;
            $keyDrivers[] = 'massive_gambling_volume_gt_100k';
        } elseif ($gamblingFlow365d >= 20000) {
            $gmbScore = 80 + (int)(($gamblingFlow365d - 20000) / 80000 * 20);
            $keyDrivers[] = 'high_gambling_volume_gt_20k';
        } elseif ($gamblingFlow365d >= 3000) {
            $gmbScore = 55 + (int)(($gamblingFlow365d - 3000) / 17000 * 25);
            $keyDrivers[] = 'regular_gambling_activity';
        } elseif ($gamblingFlow365d > 0) {
            $gmbScore = 25 + (int)($gamblingFlow365d / 3000 * 30);
            $keyDrivers[] = 'moderate_gambling_history';
        } else {
            $gmbScore = 0;
        }

        if ($gamblingEntitiesCount >= 3) {
            $gmbScore = min(100, $gmbScore + 10);
            $keyDrivers[] = 'multi_brand_gambler';
        }

        // 3. Subscore: Activity Recency (Weight: 15%)
        $now = Carbon::now();
        $daysSinceActive = $now->diffInDays($lastActivityAt);
        $recScore = 10;

        if ($lastGamblingActivity) {
            $lastGmbCarbon = Carbon::parse($lastGamblingActivity);
            $hoursSinceGambling = $now->diffInHours($lastGmbCarbon);
            if ($hoursSinceGambling <= 48) {
                $recScore = 100;
                $keyDrivers[] = 'gambling_activity_last_48h';
            } elseif ($hoursSinceGambling <= 168) { // 7 days
                $recScore = 85;
                $keyDrivers[] = 'gambling_activity_last_7d';
            } elseif ($hoursSinceGambling <= 720) { // 30 days
                $recScore = 65;
            } else {
                $recScore = 40;
            }
        } elseif ($daysSinceActive <= 7) {
            $recScore = 70;
        } elseif ($daysSinceActive <= 30) {
            $recScore = 50;
        }

        // 4. Subscore: Transaction Profile (Weight: 15%)
        $txCount = count($rawData->transactions);
        $avgTxSize = $txCount > 0 ? ($turnover365d / max(1, $txCount)) : 0;
        $txScore = 15;

        if ($avgTxSize >= 5000) {
            $txScore = 100;
            $keyDrivers[] = 'large_average_tx_size';
        } elseif ($avgTxSize >= 1000) {
            $txScore = 75 + (int)(($avgTxSize - 1000) / 4000 * 25);
        } elseif ($avgTxSize >= 200) {
            $txScore = 45 + (int)(($avgTxSize - 200) / 800 * 30);
        } else {
            $txScore = min(40, (int)($avgTxSize / 5));
        }

        // CEX Direct Funding Bonus Driver
        if ($attributionData['cex_funding']['detected']) {
            $keyDrivers[] = 'direct_cex_funded_wallet';
        }

        // Aggregate Player Score
        $totalScore = round(
            ($finScore * 0.35) +
            ($gmbScore * 0.35) +
            ($recScore * 0.15) +
            ($txScore * 0.15)
        );
        $totalScore = max(0, min(100, (int)$totalScore));

        // Base Segment & VIP Sub-classification
        $superVipThreshold = (float)($customRules['super_vip_threshold_usd'] ?? 400000);
        $potentialVipThreshold = (float)($customRules['potential_vip_threshold_usd'] ?? 100000);
        $highValueThreshold = (float)($customRules['high_value_threshold_usd'] ?? 25000);

        $segment = match (true) {
            $visibleAssets >= $superVipThreshold || ($totalScore >= 90 && $visibleAssets >= ($superVipThreshold * 0.375)) => 'super_vip',
            $totalScore >= 85 || $visibleAssets >= $potentialVipThreshold => 'potential_vip',
            $totalScore >= 70 || $visibleAssets >= $highValueThreshold => 'high_value',
            $totalScore >= 50 => 'good_player',
            $totalScore >= 30 => 'regular',
            default => 'low_value',
        };

        if ($visibleAssets >= $superVipThreshold) {
            $keyDrivers[] = "whale_liquidity_gt_{$superVipThreshold}";
            $keyDrivers[] = 'potential_shark';
        }

        // Evaluate Client Custom Rules (metric, operator, value, tag, optional tier)
        $clientRules = $customRules['rules'] ?? $customRules['custom_rules'] ?? [];
        if (is_array($clientRules)) {
            $metrics = [
                'visible_balance_usd' => (float)$visibleAssets,
                'visible_assets_usd' => (float)$visibleAssets,
                'turnover_365d_usd' => (float)$turnover365d,
                'lifetime_turnover_usd' => (float)$lifetimeTurnover,
                'gambling_turnover_usd' => (float)$gamblingFlow365d,
                'gambling_flow_365d_usd' => (float)$gamblingFlow365d,
                'transactions_count' => (float)$rawData->totalTxCount,
                'tx_count' => (float)$rawData->totalTxCount,
                'avg_tx_size_usd' => (float)$avgTxSize,
                'overall_score' => (float)$totalScore,
            ];

            foreach ($clientRules as $rule) {
                if (empty($rule['metric']) || !isset($rule['value'])) continue;

                $metricKey = strtolower(trim($rule['metric']));
                $currentVal = $metrics[$metricKey] ?? null;
                if ($currentVal === null) continue;

                $op = $rule['operator'] ?? '>=';
                $targetVal = (float)$rule['value'];

                $matched = match ($op) {
                    '>=' => $currentVal >= $targetVal,
                    '>' => $currentVal > $targetVal,
                    '<=' => $currentVal <= $targetVal,
                    '<' => $currentVal < $targetVal,
                    '==' => abs($currentVal - $targetVal) < 0.0001,
                    default => false,
                };

                if ($matched) {
                    if (!empty($rule['tag'])) {
                        $tag = strtoupper(trim($rule['tag']));
                        $customTags[] = $tag;
                        $keyDrivers[] = $tag;
                    }
                    if (!empty($rule['tier'])) {
                        $segment = strtolower(trim($rule['tier']));
                    }
                }
            }
        }

        // Overall Confidence calculation
        $confidence = 0.90;
        if ($rawData->totalTxCount > 50) $confidence += 0.05;
        if ($attributionData['gambling']['entities_count'] > 0) $confidence += 0.03;
        $confidence = min(0.99, $confidence);

        return [
            'value' => $totalScore,
            'segment' => $segment,
            'subscores' => [
                'financial_capacity' => $finScore,
                'gambling_activity' => $gmbScore,
                'activity_recency' => $recScore,
                'transaction_profile' => $txScore,
            ],
            'key_drivers' => array_values(array_unique($keyDrivers)),
            'custom_tags' => array_values(array_unique($customTags)),
            'confidence' => round($confidence, 2),
            'model_version' => self::MODEL_VERSION,
        ];
    }
}
