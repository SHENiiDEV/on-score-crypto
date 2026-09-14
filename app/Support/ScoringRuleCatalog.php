<?php

namespace App\Support;

/**
 * Single source of truth for the metrics, operators and tiers that a merchant
 * may use when composing custom scoring rules. Keys match ScoringEngine.
 */
class ScoringRuleCatalog
{
    public static function metrics(): array
    {
        return [
            'visible_balance_usd' => [
                'label' => 'Visible balance',
                'unit' => 'USD',
                'hint' => 'Non-custodial liquidity currently held by the wallet.',
            ],
            'turnover_365d_usd' => [
                'label' => 'Turnover — 365 days',
                'unit' => 'USD',
                'hint' => 'Total inbound + outbound volume over the last year.',
            ],
            'lifetime_turnover_usd' => [
                'label' => 'Lifetime turnover',
                'unit' => 'USD',
                'hint' => 'Total volume across the entire wallet history.',
            ],
            'gambling_turnover_usd' => [
                'label' => 'Gambling turnover',
                'unit' => 'USD',
                'hint' => 'Volume attributed to known casino and betting clusters.',
            ],
            'gambling_flow_365d_usd' => [
                'label' => 'Gambling flow 365d',
                'unit' => 'USD',
                'hint' => 'Volume attributed to known casino and betting clusters.',
            ],
            'transactions_count' => [
                'label' => 'Transactions count',
                'unit' => 'tx',
                'hint' => 'Total number of transactions observed on-chain.',
            ],
            'avg_tx_size_usd' => [
                'label' => 'Average transaction size',
                'unit' => 'USD',
                'hint' => 'Mean value per transaction.',
            ],
            'overall_score' => [
                'label' => 'Overall score',
                'unit' => '0–100',
                'hint' => 'The computed On-Score value before custom overrides.',
            ],
            'round_tx_percentage' => [
                'label' => 'Round deposit percentage',
                'unit' => '%',
                'hint' => 'Share of outgoing transactions with round amounts ($50, $100, $200, $500).',
            ],
            'is_fixed_amount_depositor' => [
                'label' => 'Fixed amount depositor flag',
                'unit' => '1 / 0',
                'hint' => 'Flag (1) if wallet repeatedly deposits identical/round denominations.',
            ],
            'martingale_detected' => [
                'label' => 'Martingale loss-chasing pattern',
                'unit' => '1 / 0',
                'hint' => 'Flag (1) if rapid doubling / escalating sequences were detected.',
            ],
            'night_activity_percentage' => [
                'label' => 'Night activity share',
                'unit' => '%',
                'hint' => 'Percentage of activity between 22:00 and 06:00 UTC.',
            ],
            'weekend_activity_percentage' => [
                'label' => 'Weekend activity share',
                'unit' => '%',
                'hint' => 'Percentage of activity on weekends (Fri night to Sun).',
            ],
            'whale_potential_score' => [
                'label' => 'Whale potential index',
                'unit' => '0–100',
                'hint' => 'Predictive LTV model rating probability of reaching high-roller volume.',
            ],
            'session_intensity' => [
                'label' => 'Session intensity (Max burst txs)',
                'unit' => 'tx/session',
                'hint' => 'Peak number of transactions clustered in a single rapid gaming session.',
            ],
            'fast_reload_count' => [
                'label' => 'Fast reload count',
                'unit' => 'reloads',
                'hint' => 'Occurrences of back-to-back deposits within < 15 minutes.',
            ],
        ];
    }

    public static function operators(): array
    {
        return [
            '>=' => 'is at least (≥)',
            '>' => 'is greater than (>)',
            '<=' => 'is at most (≤)',
            '<' => 'is less than (<)',
            '==' => 'equals (=)',
        ];
    }

    public static function tiers(): array
    {
        return [
            '' => 'Keep engine segment',
            'super_vip' => 'Super VIP',
            'potential_vip' => 'Potential VIP',
            'high_value' => 'High value',
            'good_player' => 'Good player',
            'regular' => 'Regular',
            'low_value' => 'Low value',
        ];
    }

    public static function metricLabel(string $key): string
    {
        return self::metrics()[$key]['label'] ?? ucfirst(str_replace('_', ' ', $key));
    }

    public static function tierLabel(?string $key): string
    {
        if (! $key) {
            return 'Keep engine segment';
        }

        return self::tiers()[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }

    /**
     * Normalise raw request input into the array shape ScoringEngine expects.
     */
    public static function sanitizeRules(array $rows): array
    {
        $allowedMetrics = array_keys(self::metrics());
        $allowedOperators = array_keys(self::operators());
        $allowedTiers = array_filter(array_keys(self::tiers()));

        $clean = [];

        foreach ($rows as $row) {
            $metric = strtolower(trim((string) ($row['metric'] ?? '')));
            $value = $row['value'] ?? null;

            if (! in_array($metric, $allowedMetrics, true) || $value === null || $value === '') {
                continue;
            }

            $operator = (string) ($row['operator'] ?? '>=');
            if (! in_array($operator, $allowedOperators, true)) {
                $operator = '>=';
            }

            $tag = strtoupper(trim((string) ($row['tag'] ?? '')));
            $tag = preg_replace('/[^A-Z0-9_\- ]/', '', $tag) ?: null;

            $tier = strtolower(trim((string) ($row['tier'] ?? '')));
            $tier = in_array($tier, $allowedTiers, true) ? $tier : null;

            if (! $tag && ! $tier) {
                continue;
            }

            $clean[] = [
                'metric' => $metric,
                'operator' => $operator,
                'value' => (float) $value,
                'tag' => $tag ? substr($tag, 0, 40) : null,
                'tier' => $tier,
            ];

            if (count($clean) >= 40) {
                break;
            }
        }

        return $clean;
    }
}
