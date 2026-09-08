<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\ApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SolanaAnalysisTest extends TestCase
{
    use RefreshDatabase;

    private ApiClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $account = Account::create([
            'name' => 'Solana Casino Client',
            'slug' => 'solana-casino-client',
            'credit_balance' => 1000,
            'status' => 'active',
        ]);

        $this->client = ApiClient::create([
            'account_id' => $account->id,
            'name' => 'Solana Live Key',
            'key_id' => 'ons_live_sol_test_key_123',
            'secret_hash' => bcrypt('secret123'),
            'status' => 'active',
            'rate_limit_per_minute' => 60,
        ]);
    }

    public function test_solana_analysis_endpoint_creates_snapshot_and_charges_credits(): void
    {
        $response = $this->postJson('/api/v1/analyses', [
            'network' => 'solana',
            'address' => 'RollBitDepositVaultSolana1111111111111111111',
            'external_player_id' => 'sol_whale_1',
        ], [
            'X-API-Key' => 'ons_live_sol_test_key_123',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'analysis_id',
            'status',
            'wallet' => [
                'visible_balance_usd',
                'transactions_count',
            ],
            'score' => [
                'value',
                'segment',
            ],
        ]);

        // Solana costs 2 credits
        $this->assertEquals(998, $this->client->account->fresh()->credit_balance);
    }
}
