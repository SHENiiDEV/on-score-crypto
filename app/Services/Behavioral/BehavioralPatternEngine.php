<?php

namespace App\Services\Behavioral;

use App\Services\Blockchain\DTO\TransactionDTO;
use App\Services\Blockchain\DTO\WalletOnchainData;

class BehavioralPatternEngine
{
    /**
     * Common round USD betting/deposit denominations
     */
    private const COMMON_ROUND_AMOUNTS = [
        10, 20, 25, 50, 75, 100, 150, 200, 250, 300, 400, 500, 600, 750, 800, 1000,
        1500, 2000, 2500, 3000, 4000, 5000, 7500, 10000, 15000, 20000, 25000, 50000, 100000
    ];

    /**
     * Analyze all transactions of a wallet to detect casino behavioral patterns.
     */
    public function analyze(WalletOnchainData $rawData, array $attributionData = []): array
    {
        $txs = $rawData->transactions;

        if (empty($txs)) {
            return $this->emptyResult();
        }

        // 1. Sort transactions chronologically
        usort($txs, fn (TransactionDTO $a, TransactionDTO $b) => $a->timestamp <=> $b->timestamp);

        // 2. Round & Fixed Amount Analysis
        $roundAnalysis = $this->analyzeRoundAmounts($txs);

        // 3. Martingale / Tilt Sequence Detection
        $martingaleAnalysis = $this->detectMartingaleSequences($txs);

        // 4. Session & Temporal Profiling (24h heatmap, burst sessions, night/weekend index)
        $temporalAnalysis = $this->analyzeTemporalSessions($txs);

        // 5. Velocity & Cashout Cycles
        $velocityAnalysis = $this->analyzeVelocityCycles($txs);

        // 6. Unlabeled Casino & Sweeper Proxy Heuristics
        $unlabeledCasino = $this->evaluateUnlabeledCasinoHeuristics($txs, $attributionData);

        // 7. Predictive Traits & Behavioral Tags
        $tags = $this->deriveBehavioralTags(
            $roundAnalysis,
            $martingaleAnalysis,
            $temporalAnalysis,
            $velocityAnalysis,
            $unlabeledCasino,
            $rawData,
            $attributionData
        );

        $whalePotential = $this->calculateWhalePotential(
            $rawData,
            $attributionData,
            $roundAnalysis,
            $temporalAnalysis
        );

        $churnRisk = $this->calculateChurnRisk($rawData, $txs);

        return [
            'round_deposits' => $roundAnalysis,
            'martingale_chasing' => $martingaleAnalysis,
            'session_activity' => $temporalAnalysis,
            'velocity_cycles' => $velocityAnalysis,
            'unlabeled_casino_heuristics' => $unlabeledCasino,
            'behavioral_tags' => $tags,
            'whale_potential_score' => $whalePotential,
            'churn_risk' => $churnRisk,
        ];
    }

    /**
     * Analyze round deposit amounts and calculate Shannon entropy of bet/deposit sizing.
     */
    private function analyzeRoundAmounts(array $txs): array
    {
        $outTxs = array_filter($txs, fn (TransactionDTO $tx) => $tx->direction === 'out' && $tx->usdValue >= 5.0);
        
        // If out transactions are sparse, inspect all meaningful transactions
        if (count($outTxs) < 3) {
            $analyzedTxs = array_filter($txs, fn (TransactionDTO $tx) => $tx->usdValue >= 5.0);
        } else {
            $analyzedTxs = $outTxs;
        }

        $totalCount = count($analyzedTxs);
        if ($totalCount === 0) {
            return [
                'total_analyzed' => 0,
                'round_count' => 0,
                'round_percentage' => 0.0,
                'is_fixed_amount_depositor' => false,
                'top_recurring_amounts' => [],
                'amount_entropy' => 0.0,
            ];
        }

        $roundCount = 0;
        $clusters = [];

        foreach ($analyzedTxs as $tx) {
            $val = $tx->usdValue;
            $nativeAmt = $tx->amount;

            $isRound = $this->isRoundNumber($val, $nativeAmt);
            if ($isRound) {
                $roundCount++;
            }

            // Cluster amounts (round to nearest integer if >= 10, or 1 decimal)
            $clusterKey = $val >= 10 ? (string) round($val) : (string) round($val, 1);
            if (!isset($clusters[$clusterKey])) {
                $clusters[$clusterKey] = [
                    'amount_usd' => (float) $clusterKey,
                    'count' => 0,
                    'total_volume_usd' => 0.0,
                ];
            }
            $clusters[$clusterKey]['count']++;
            $clusters[$clusterKey]['total_volume_usd'] += $val;
        }

        $roundPercentage = round(($roundCount / $totalCount) * 100, 1);

        // Sort clusters by frequency descending
        usort($clusters, fn ($a, $b) => $b['count'] <=> $a['count']);
        $topClusters = array_slice($clusters, 0, 5);

        // Calculate normalized Shannon Entropy: H = -sum(p * log2(p)) / log2(N)
        $entropy = 0.0;
        $numClusters = count($clusters);
        if ($numClusters > 1) {
            foreach ($clusters as $c) {
                $p = $c['count'] / $totalCount;
                if ($p > 0) {
                    $entropy -= $p * log($p, 2);
                }
            }
            $maxEntropy = log($numClusters, 2);
            $normalizedEntropy = $maxEntropy > 0 ? round($entropy / $maxEntropy, 2) : 0.0;
        } else {
            $normalizedEntropy = 0.0;
        }

        $isFixed = ($roundPercentage >= 50.0 && $roundCount >= 3) || 
                   (!empty($topClusters) && ($topClusters[0]['count'] / $totalCount) >= 0.40 && $totalCount >= 4);

        return [
            'total_analyzed' => $totalCount,
            'round_count' => $roundCount,
            'round_percentage' => $roundPercentage,
            'is_fixed_amount_depositor' => $isFixed,
            'top_recurring_amounts' => $topClusters,
            'amount_entropy' => $normalizedEntropy,
        ];
    }

    /**
     * Check if a USD amount or native token amount is a typical round gambling number.
     */
    private function isRoundNumber(float $usd, float $nativeAmount): bool
    {
        if ($usd <= 0) return false;

        // Check common round USD amounts with small tolerance for token price fluctuations
        foreach (self::COMMON_ROUND_AMOUNTS as $round) {
            if (abs($usd - $round) <= max(0.5, $round * 0.025)) {
                return true;
            }
        }

        // Multiples of 50, 100, 500, 1000
        if ($usd >= 50 && fmod($usd, 50) < 0.5) return true;
        if ($usd >= 100 && fmod($usd, 100) < 1.0) return true;
        if ($usd >= 500 && fmod($usd, 500) < 5.0) return true;

        // Check native round amounts (e.g. 100 TRX, 500 TRX, 1000 TRX, 1.0 ETH, 5 SOL)
        if ($nativeAmount >= 10 && fmod($nativeAmount, 10) < 0.01) return true;
        if ($nativeAmount >= 1 && fmod($nativeAmount, 1) < 0.001) return true;

        return false;
    }

    /**
     * Detect Martingale / Loss-Chasing patterns (rapid doubling or escalating sequences within 3 hours).
     */
    private function detectMartingaleSequences(array $txs): array
    {
        $outTxs = array_values(array_filter($txs, fn (TransactionDTO $tx) => $tx->direction === 'out' && $tx->usdValue >= 10.0));
        $count = count($outTxs);

        if ($count < 3) {
            return [
                'detected' => false,
                'sequence_count' => 0,
                'max_sequence_length' => 0,
                'max_multiplier' => 1.0,
                'tilt_risk_level' => 'LOW',
                'sequences' => [],
            ];
        }

        $sequences = [];
        $currentChain = [$outTxs[0]];

        for ($i = 1; $i < $count; $i++) {
            $prev = end($currentChain);
            $curr = $outTxs[$i];

            $timeDiffSeconds = $curr->timestamp->getTimestamp() - $prev->timestamp->getTimestamp();
            $ratio = $prev->usdValue > 0 ? ($curr->usdValue / $prev->usdValue) : 1.0;

            // Chasing loss condition:
            // 1. Time difference <= 3 hours (10800s)
            // 2. Amount increases by multiplier between 1.6x and 2.6x (Martingale doubling) or aggressive jump >= 2.0x
            if ($timeDiffSeconds <= 10800 && $ratio >= 1.6 && $ratio <= 3.5) {
                $currentChain[] = $curr;
            } else {
                if (count($currentChain) >= 3) {
                    $sequences[] = $this->formatSequence($currentChain);
                }
                $currentChain = [$curr];
            }
        }

        if (count($currentChain) >= 3) {
            $sequences[] = $this->formatSequence($currentChain);
        }

        $detected = count($sequences) > 0;
        $maxLen = 0;
        $maxMul = 1.0;

        foreach ($sequences as $seq) {
            if ($seq['steps_count'] > $maxLen) $maxLen = $seq['steps_count'];
            if ($seq['multiplier'] > $maxMul) $maxMul = $seq['multiplier'];
        }

        $riskLevel = match (true) {
            $maxLen >= 4 || $maxMul >= 7.0 => 'CRITICAL',
            $maxLen >= 3 || $maxMul >= 3.5 => 'HIGH',
            $detected => 'MEDIUM',
            default => 'LOW',
        };

        return [
            'detected' => $detected,
            'sequence_count' => count($sequences),
            'max_sequence_length' => $maxLen,
            'max_multiplier' => round($maxMul, 2),
            'tilt_risk_level' => $riskLevel,
            'sequences' => $sequences,
        ];
    }

    private function formatSequence(array $chain): array
    {
        $first = $chain[0];
        $last = end($chain);
        $totalVol = array_sum(array_map(fn (TransactionDTO $t) => $t->usdValue, $chain));
        $multiplier = $first->usdValue > 0 ? ($last->usdValue / $first->usdValue) : 1.0;

        return [
            'steps_count' => count($chain),
            'start_time' => $first->timestamp->format(\DateTimeInterface::ATOM),
            'end_time' => $last->timestamp->format(\DateTimeInterface::ATOM),
            'duration_minutes' => round(($last->timestamp->getTimestamp() - $first->timestamp->getTimestamp()) / 60, 1),
            'start_amount_usd' => round($first->usdValue, 2),
            'peak_amount_usd' => round($last->usdValue, 2),
            'total_sequence_volume_usd' => round($totalVol, 2),
            'multiplier' => round($multiplier, 2),
            'steps' => array_map(fn (TransactionDTO $t) => [
                'tx_hash' => $t->txHash,
                'usd_value' => round($t->usdValue, 2),
                'timestamp' => $t->timestamp->format(\DateTimeInterface::ATOM),
            ], $chain),
        ];
    }

    /**
     * Session and temporal profiling (24h histogram, night index, burst gaming sessions).
     */
    private function analyzeTemporalSessions(array $txs): array
    {
        $hourly = array_fill(0, 24, 0);
        $weekday = array_fill(0, 7, 0); // 0 = Sunday, 1 = Monday ... 6 = Saturday

        $nightCount = 0;
        $weekendCount = 0;
        $totalCount = count($txs);

        // Session Clustering: gap <= 45 minutes (2700s) belongs to same burst session
        $sessions = [];
        $currentSession = [];

        foreach ($txs as $tx) {
            $dt = $tx->timestamp;
            $hour = (int) $dt->format('G'); // 0-23
            $dayOfWeek = (int) $dt->format('w'); // 0 (Sun) to 6 (Sat)

            $hourly[$hour]++;
            $weekday[$dayOfWeek]++;

            // Night hours: 22:00 to 06:00
            if ($hour >= 22 || $hour < 6) {
                $nightCount++;
            }

            // Weekend: Friday 18:00+ through Sunday 23:59
            if ($dayOfWeek === 0 || $dayOfWeek === 6 || ($dayOfWeek === 5 && $hour >= 18)) {
                $weekendCount++;
            }

            // Session clustering
            if (empty($currentSession)) {
                $currentSession[] = $tx;
            } else {
                $lastTx = end($currentSession);
                $diff = $tx->timestamp->getTimestamp() - $lastTx->timestamp->getTimestamp();
                if ($diff <= 2700) { // 45 min
                    $currentSession[] = $tx;
                } else {
                    $sessions[] = $currentSession;
                    $currentSession = [$tx];
                }
            }
        }

        if (!empty($currentSession)) {
            $sessions[] = $currentSession;
        }

        $sessionLengths = array_map(fn ($s) => count($s), $sessions);
        $maxSessionTx = !empty($sessionLengths) ? max($sessionLengths) : 0;
        $avgSessionTx = !empty($sessionLengths) ? round(array_sum($sessionLengths) / max(1, count($sessions)), 1) : 0.0;

        $nightPercentage = $totalCount > 0 ? round(($nightCount / $totalCount) * 100, 1) : 0.0;
        $weekendPercentage = $totalCount > 0 ? round(($weekendCount / $totalCount) * 100, 1) : 0.0;

        $isBurst = $maxSessionTx >= 4;

        return [
            'hourly_distribution' => $hourly,
            'weekday_distribution' => $weekday,
            'night_activity_percentage' => $nightPercentage,
            'weekend_activity_percentage' => $weekendPercentage,
            'total_sessions_count' => count($sessions),
            'max_session_tx_count' => $maxSessionTx,
            'avg_session_tx_count' => $avgSessionTx,
            'is_burst_session_player' => $isBurst,
            'peak_activity_hour' => array_search(max($hourly), $hourly, true) ?: 0,
        ];
    }

    /**
     * Deposit to cashout velocity and rapid reload frequency.
     */
    private function analyzeVelocityCycles(array $txs): array
    {
        $fastReloadCount = 0;
        $cashoutCycles = [];

        $count = count($txs);
        for ($i = 0; $i < $count - 1; $i++) {
            $curr = $txs[$i];
            $next = $txs[$i + 1];

            $timeDiff = $next->timestamp->getTimestamp() - $curr->timestamp->getTimestamp();

            // Fast reload: 2 outgoing txs within <= 15 minutes (900 seconds)
            if ($curr->direction === 'out' && $next->direction === 'out' && $timeDiff <= 900) {
                $fastReloadCount++;
            }

            // Rapid cashout cycle: OUT (Deposit) -> IN (Withdrawal / Win) within 6 hours
            if ($curr->direction === 'out' && $next->direction === 'in' && $timeDiff <= 21600) {
                $cashoutCycles[] = [
                    'deposit_usd' => round($curr->usdValue, 2),
                    'payout_usd' => round($next->usdValue, 2),
                    'duration_hours' => round($timeDiff / 3600, 2),
                ];
            }
        }

        $avgCycleHours = !empty($cashoutCycles)
            ? round(array_sum(array_column($cashoutCycles, 'duration_hours')) / count($cashoutCycles), 2)
            : null;

        return [
            'fast_reload_count' => $fastReloadCount,
            'cashout_cycles_count' => count($cashoutCycles),
            'avg_cashout_cycle_hours' => $avgCycleHours,
            'recent_cycles' => array_slice($cashoutCycles, 0, 5),
        ];
    }

    /**
     * Heuristic detection of unlabeled casino deposit proxies and sweeper contracts.
     */
    private function evaluateUnlabeledCasinoHeuristics(array $txs, array $attributionData): array
    {
        $unlabeledCandidates = [];
        $knownGamblingTxCount = $attributionData['gambling']['transactions_count'] ?? 0;

        // Group outgoing transactions by destination address
        $outDestinations = [];
        foreach ($txs as $tx) {
            if ($tx->direction === 'out' && $tx->usdValue >= 20.0) {
                $dest = strtolower($tx->toAddress);
                if (!isset($outDestinations[$dest])) {
                    $outDestinations[$dest] = [
                        'address' => $tx->toAddress,
                        'tx_count' => 0,
                        'total_usd' => 0.0,
                        'is_contract' => $tx->isContractCall,
                        'round_tx_count' => 0,
                    ];
                }
                $outDestinations[$dest]['tx_count']++;
                $outDestinations[$dest]['total_usd'] += $tx->usdValue;
                if ($this->isRoundNumber($tx->usdValue, $tx->amount)) {
                    $outDestinations[$dest]['round_tx_count']++;
                }
            }
        }

        $potentialCasinoProxies = 0;
        foreach ($outDestinations as $dest => $info) {
            // If a destination receives recurring round amounts and multiple deposits
            if ($info['tx_count'] >= 2 && ($info['round_tx_count'] / $info['tx_count']) >= 0.70) {
                $potentialCasinoProxies++;
                $unlabeledCandidates[] = [
                    'destination_address' => $info['address'],
                    'deposits_count' => $info['tx_count'],
                    'total_volume_usd' => round($info['total_usd'], 2),
                    'confidence' => 0.85,
                    'heuristic_reason' => 'Recurring round deposit proxy pattern',
                ];
            }
        }

        $score = match (true) {
            $knownGamblingTxCount > 0 => 95,
            $potentialCasinoProxies >= 2 => 80,
            $potentialCasinoProxies === 1 => 60,
            default => 15,
        };

        return [
            'unlabeled_casino_score' => $score,
            'detected_proxy_count' => $potentialCasinoProxies,
            'candidates' => $unlabeledCandidates,
        ];
    }

    /**
     * Derive explanatory behavioral tags for reporting and engine classification.
     */
    private function deriveBehavioralTags(
        array $round,
        array $martingale,
        array $temporal,
        array $velocity,
        array $unlabeled,
        WalletOnchainData $rawData,
        array $attributionData
    ): array {
        $tags = [];

        if ($round['is_fixed_amount_depositor']) {
            $tags[] = 'RECURRENT_ROUND_DEPOSITOR';
        }

        if ($round['round_percentage'] >= 75.0 && $round['total_analyzed'] >= 4) {
            $tags[] = 'FIXED_STAKE_SPECIALIST';
        }

        if ($martingale['detected']) {
            $tags[] = 'MARTINGALE_CHASING_PATTERN';
        }

        if ($temporal['is_burst_session_player']) {
            $tags[] = 'HIGH_SESSION_INTENSITY';
        }

        if ($temporal['night_activity_percentage'] >= 50.0 && count($rawData->transactions) >= 4) {
            $tags[] = 'NIGHT_OWL_GAMBLER';
        }

        if ($temporal['weekend_activity_percentage'] >= 55.0 && count($rawData->transactions) >= 4) {
            $tags[] = 'WEEKEND_WARRIOR';
        }

        if ($velocity['fast_reload_count'] >= 2) {
            $tags[] = 'RAPID_RELOADER';
        }

        if (($attributionData['turnover']['turnover_365d_usd'] ?? 0) >= 100000 || (!empty($round['top_recurring_amounts']) && ($round['top_recurring_amounts'][0]['amount_usd'] ?? 0) >= 1000)) {
            $tags[] = 'HIGH_ROLLER_POTENTIAL';
        }

        return array_values(array_unique($tags));
    }

    /**
     * Compute a predictive 0-100 score on whether the player will become a high-value Whale.
     */
    private function calculateWhalePotential(
        WalletOnchainData $rawData,
        array $attributionData,
        array $round,
        array $temporal
    ): int {
        $score = 10;

        $turnover = (float) ($attributionData['turnover']['turnover_365d_usd'] ?? 0);
        $balance = (float) ($attributionData['adjusted_visible_assets_usd'] ?? $rawData->nativeBalanceUsd);
        $gamblingFlow = (float) ($attributionData['gambling']['total_gambling_volume_usd'] ?? 0);

        if ($balance >= 100000) $score += 35;
        elseif ($balance >= 25000) $score += 20;
        elseif ($balance >= 5000) $score += 10;

        if ($turnover >= 250000) $score += 35;
        elseif ($turnover >= 50000) $score += 20;
        elseif ($turnover >= 10000) $score += 10;

        if ($gamblingFlow >= 50000) $score += 20;
        elseif ($gamblingFlow >= 10000) $score += 10;

        if (!empty($round['top_recurring_amounts']) && ($round['top_recurring_amounts'][0]['amount_usd'] >= 500)) {
            $score += 10;
        }

        if ($temporal['is_burst_session_player']) {
            $score += 5;
        }

        return max(0, min(100, $score));
    }

    /**
     * Compute churn risk based on dormancy and activity drop.
     */
    private function calculateChurnRisk(WalletOnchainData $rawData, array $txs): string
    {
        if (empty($txs)) {
            return 'HIGH';
        }

        $lastTx = end($txs);
        $now = new \DateTimeImmutable();
        $daysInactive = (int) $now->diff($lastTx->timestamp)->days;

        return match (true) {
            $daysInactive > 90 => 'HIGH',
            $daysInactive > 30 => 'MEDIUM',
            default => 'LOW',
        };
    }

    private function emptyResult(): array
    {
        return [
            'round_deposits' => [
                'total_analyzed' => 0,
                'round_count' => 0,
                'round_percentage' => 0.0,
                'is_fixed_amount_depositor' => false,
                'top_recurring_amounts' => [],
                'amount_entropy' => 0.0,
            ],
            'martingale_chasing' => [
                'detected' => false,
                'sequence_count' => 0,
                'max_sequence_length' => 0,
                'max_multiplier' => 1.0,
                'tilt_risk_level' => 'LOW',
                'sequences' => [],
            ],
            'session_activity' => [
                'hourly_distribution' => array_fill(0, 24, 0),
                'weekday_distribution' => array_fill(0, 7, 0),
                'night_activity_percentage' => 0.0,
                'weekend_activity_percentage' => 0.0,
                'total_sessions_count' => 0,
                'max_session_tx_count' => 0,
                'avg_session_tx_count' => 0.0,
                'is_burst_session_player' => false,
                'peak_activity_hour' => 0,
            ],
            'velocity_cycles' => [
                'fast_reload_count' => 0,
                'cashout_cycles_count' => 0,
                'avg_cashout_cycle_hours' => null,
                'recent_cycles' => [],
            ],
            'unlabeled_casino_heuristics' => [
                'unlabeled_casino_score' => 0,
                'detected_proxy_count' => 0,
                'candidates' => [],
            ],
            'behavioral_tags' => [],
            'whale_potential_score' => 0,
            'churn_risk' => 'HIGH',
        ];
    }
}
