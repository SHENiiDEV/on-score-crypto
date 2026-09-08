<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\ApiClient;
use App\Models\User;
use App\Services\Blockchain\DTO\WalletOnchainData;
use App\Services\Scoring\ScoringEngine;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomScoringRulesTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::create([
            'name' => 'Custom Casino Operator',
            'slug' => 'custom-casino-operator',
            'credit_balance' => 5000,
            'status' => 'active',
        ]);

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'account_id' => $this->account->id,
        ]);
    }

    public function test_admin_can_update_custom_scoring_rules_for_account(): void
    {
        $response = $this->actingAs($this->adminUser)->post("/admin/accounts/{$this->account->id}/scoring-rules", [
            'super_vip_threshold_usd' => 200000,
            'potential_vip_threshold_usd' => 50000,
            'metrics' => ['visible_balance_usd', 'gambling_flow_365d_usd'],
            'operators' => ['>=', '>='],
            'values' => [200000, 30000],
            'tags' => ['SUPER_SHARK', 'HIGH_ROLLER_VIP'],
            'tiers' => ['super_vip', 'high_value'],
        ]);

        $response->assertRedirect('/admin');
        
        $fresh = $this->account->fresh();
        $this->assertEquals(200000, $fresh->scoring_rules['super_vip_threshold_usd']);
        $this->assertCount(2, $fresh->scoring_rules['custom_rules']);
        $this->assertEquals('SUPER_SHARK', $fresh->scoring_rules['custom_rules'][0]['tag']);
    }

    public function test_scoring_engine_applies_custom_client_thresholds_and_tags(): void
    {
        $customRules = [
            'super_vip_threshold_usd' => 200000,
            'custom_rules' => [
                [
                    'metric' => 'visible_balance_usd',
                    'operator' => '>=',
                    'value' => 200000,
                    'tag' => 'SUPER_SHARK',
                    'tier' => 'super_vip',
                ],
            ],
        ];

        $scoringEngine = new ScoringEngine();

        $rawData = new WalletOnchainData(
            address: '0x123custom',
            network: 'ethereum',
            nativeBalance: 100.0,
            nativeBalanceUsd: 250000.0,
            visibleTotalBalanceUsd: 250000.0,
            tokens: [],
            transactions: [],
            firstActivityAt: Carbon::now()->subMonths(6),
            lastActivityAt: Carbon::now(),
            totalTxCount: 20,
            providerName: 'mock',
            providerConfidence: 0.95
        );

        $attributionData = [
            'is_target_cex' => false,
            'adjusted_visible_assets_usd' => 250000.0,
            'raw_visible_assets_usd' => 250000.0,
            'turnover' => [
                '365d_usd' => 300000.0,
                'lifetime_usd' => 300000.0,
            ],
            'gambling' => [
                'total_flow_365d_usd' => 5000.0,
                'outgoing_365d_usd' => 5000.0,
                'entities_count' => 1,
                'last_gambling_activity' => null,
            ],
            'cex_funding' => [
                'detected' => false,
            ],
            'counterparties' => [],
            'warnings' => [],
        ];

        $result = $scoringEngine->calculateScore($rawData, $attributionData, $customRules);

        $this->assertEquals('super_vip', $result['segment']);
        $this->assertContains('SUPER_SHARK', $result['custom_tags']);
        $this->assertContains('SUPER_SHARK', $result['key_drivers']);
    }
}
