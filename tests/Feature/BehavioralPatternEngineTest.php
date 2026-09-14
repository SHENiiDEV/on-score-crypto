<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use App\Services\Behavioral\BehavioralPatternEngine;
use App\Services\Blockchain\DTO\TransactionDTO;
use App\Services\Blockchain\DTO\WalletOnchainData;
use App\Services\Scoring\ScoringEngine;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BehavioralPatternEngineTest extends TestCase
{
    use RefreshDatabase;

    private BehavioralPatternEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new BehavioralPatternEngine();
    }

    public function test_round_amount_detection_and_fixed_depositor_tag(): void
    {
        $now = Carbon::now();
        $txs = [
            new TransactionDTO('0x1', 'tron', 'TWallet1', 'TCasino1', 100.0, 'USDT', 100.0, $now->copy()->subHours(10), 'out'),
            new TransactionDTO('0x2', 'tron', 'TWallet1', 'TCasino2', 200.0, 'USDT', 200.0, $now->copy()->subHours(8), 'out'),
            new TransactionDTO('0x3', 'tron', 'TWallet1', 'TCasino1', 100.0, 'USDT', 100.0, $now->copy()->subHours(6), 'out'),
            new TransactionDTO('0x4', 'tron', 'TWallet1', 'TCasino3', 500.0, 'USDT', 500.0, $now->copy()->subHours(4), 'out'),
            new TransactionDTO('0x5', 'tron', 'TWallet1', 'TUser1', 34.58, 'USDT', 34.58, $now->copy()->subHours(2), 'out'),
        ];

        $data = new WalletOnchainData(
            address: 'TWallet1',
            network: 'tron',
            nativeBalance: 1000.0,
            nativeBalanceUsd: 150.0,
            visibleTotalBalanceUsd: 150.0,
            tokens: [],
            transactions: $txs,
            firstActivityAt: $now->copy()->subDays(30),
            lastActivityAt: $now,
            totalTxCount: 5,
            providerName: 'TronGrid'
        );

        $result = $this->engine->analyze($data, [
            'turnover' => ['turnover_365d_usd' => 934.58],
            'gambling' => ['total_gambling_volume_usd' => 900.0, 'transactions_count' => 4],
            'adjusted_visible_assets_usd' => 150.0,
        ]);

        $this->assertEquals(80.0, $result['round_deposits']['round_percentage']);
        $this->assertTrue($result['round_deposits']['is_fixed_amount_depositor']);
        $this->assertContains('RECURRENT_ROUND_DEPOSITOR', $result['behavioral_tags']);
        $this->assertNotEmpty($result['round_deposits']['top_recurring_amounts']);
    }

    public function test_martingale_loss_chasing_sequence_detection(): void
    {
        $base = Carbon::now()->subHours(5);
        $txs = [
            new TransactionDTO('0x10', 'ethereum', '0xWallet', '0xCasino', 50.0, 'USDT', 50.0, $base->copy()->addMinutes(5), 'out'),
            new TransactionDTO('0x11', 'ethereum', '0xWallet', '0xCasino', 100.0, 'USDT', 100.0, $base->copy()->addMinutes(25), 'out'),
            new TransactionDTO('0x12', 'ethereum', '0xWallet', '0xCasino', 200.0, 'USDT', 200.0, $base->copy()->addMinutes(45), 'out'),
            new TransactionDTO('0x13', 'ethereum', '0xWallet', '0xCasino', 450.0, 'USDT', 450.0, $base->copy()->addMinutes(70), 'out'),
        ];

        $data = new WalletOnchainData(
            address: '0xWallet',
            network: 'ethereum',
            nativeBalance: 2.5,
            nativeBalanceUsd: 6500.0,
            visibleTotalBalanceUsd: 6500.0,
            tokens: [],
            transactions: $txs,
            firstActivityAt: $base->copy()->subDays(10),
            lastActivityAt: $base->copy()->addMinutes(70),
            totalTxCount: 4,
            providerName: 'Etherscan'
        );

        $result = $this->engine->analyze($data);

        $this->assertTrue($result['martingale_chasing']['detected']);
        $this->assertSame(4, $result['martingale_chasing']['max_sequence_length']);
        $this->assertSame('CRITICAL', $result['martingale_chasing']['tilt_risk_level']);
        $this->assertContains('MARTINGALE_CHASING_PATTERN', $result['behavioral_tags']);
    }

    public function test_session_and_night_activity_profiling(): void
    {
        // Generate night transactions (23:00, 01:00, 02:00, 03:30 UTC)
        $txs = [
            new TransactionDTO('0x20', 'bsc', '0xPlayer', '0xDest', 100.0, 'USDT', 100.0, Carbon::parse('2026-09-14 23:15:00 UTC'), 'out'),
            new TransactionDTO('0x21', 'bsc', '0xPlayer', '0xDest', 100.0, 'USDT', 100.0, Carbon::parse('2026-09-15 01:05:00 UTC'), 'out'),
            new TransactionDTO('0x22', 'bsc', '0xPlayer', '0xDest', 100.0, 'USDT', 100.0, Carbon::parse('2026-09-15 01:25:00 UTC'), 'out'),
            new TransactionDTO('0x23', 'bsc', '0xPlayer', '0xDest', 100.0, 'USDT', 100.0, Carbon::parse('2026-09-15 03:30:00 UTC'), 'out'),
        ];

        $data = new WalletOnchainData(
            address: '0xPlayer',
            network: 'bsc',
            nativeBalance: 1.0,
            nativeBalanceUsd: 500.0,
            visibleTotalBalanceUsd: 500.0,
            tokens: [],
            transactions: $txs,
            firstActivityAt: Carbon::parse('2026-09-14 23:15:00 UTC'),
            lastActivityAt: Carbon::parse('2026-09-15 03:30:00 UTC'),
            totalTxCount: 4,
            providerName: 'BscScan'
        );

        $result = $this->engine->analyze($data);

        $this->assertEquals(100.0, $result['session_activity']['night_activity_percentage']);
        $this->assertContains('NIGHT_OWL_GAMBLER', $result['behavioral_tags']);
        $this->assertGreaterThan(0, $result['session_activity']['hourly_distribution'][23]);
        $this->assertGreaterThan(0, $result['session_activity']['hourly_distribution'][1]);
    }

    public function test_scoring_engine_triggers_custom_rule_on_behavioral_metrics(): void
    {
        $scoringEngine = new ScoringEngine();
        $now = Carbon::now();

        $txs = [
            new TransactionDTO('0x30', 'tron', 'TPlayer', 'TTarget', 500.0, 'USDT', 500.0, $now->copy()->subHours(2), 'out'),
            new TransactionDTO('0x31', 'tron', 'TPlayer', 'TTarget', 1000.0, 'USDT', 1000.0, $now->copy()->subHours(1), 'out'),
            new TransactionDTO('0x32', 'tron', 'TPlayer', 'TTarget', 500.0, 'USDT', 500.0, $now, 'out'),
        ];

        $rawData = new WalletOnchainData(
            address: 'TPlayer',
            network: 'tron',
            nativeBalance: 1000.0,
            nativeBalanceUsd: 150.0,
            visibleTotalBalanceUsd: 150.0,
            tokens: [],
            transactions: $txs,
            firstActivityAt: $now->copy()->subDays(10),
            lastActivityAt: $now,
            totalTxCount: 3,
            providerName: 'TronGrid'
        );

        $attribution = [
            'is_target_cex' => false,
            'adjusted_visible_assets_usd' => 10000.0,
            'turnover' => ['365d_usd' => 2000.0, 'lifetime_usd' => 2000.0],
            'gambling' => ['total_flow_365d_usd' => 2000.0, 'outgoing_365d_usd' => 2000.0, 'entities_count' => 1, 'last_gambling_activity' => $now->toIso8601String()],
            'cex_funding' => ['detected' => false],
        ];

        $behavioral = $this->engine->analyze($rawData, $attribution);

        $customRules = [
            'rules' => [
                [
                    'metric' => 'round_tx_percentage',
                    'operator' => '>=',
                    'value' => 70,
                    'tag' => 'VIP_ROUND_STAKER',
                    'tier' => 'super_vip',
                ],
            ],
        ];

        $score = $scoringEngine->calculateScore($rawData, $attribution, $customRules, $behavioral);

        $this->assertSame('super_vip', $score['segment']);
        $this->assertContains('VIP_ROUND_STAKER', $score['custom_tags']);
        $this->assertContains('recurrent_round_depositor', $score['key_drivers']);
    }
}
