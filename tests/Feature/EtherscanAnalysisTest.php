<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\ApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EtherscanAnalysisTest extends TestCase
{
    use RefreshDatabase;

    private ApiClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $account = Account::create([
            'name' => 'Ethereum VIP Casino Client',
            'slug' => 'ethereum-vip-casino-client',
            'credit_balance' => 1000,
            'status' => 'active',
        ]);

        $this->client = ApiClient::create([
            'account_id' => $account->id,
            'name' => 'ETH Live Key',
            'key_id' => 'ons_live_eth_test_key_123',
            'secret_hash' => bcrypt('secret123'),
            'status' => 'active',
            'rate_limit_per_minute' => 60,
        ]);
    }

    public function test_ethereum_analysis_endpoint_creates_snapshot_and_charges_credits(): void
    {
        $response = $this->postJson('/api/v1/analyses', [
            'network' => 'ethereum',
            'address' => '0xde0B295669a9FD93d5F28D9Ec85E40f4cb697BAe',
            'external_player_id' => 'vitalik_player',
        ], [
            'X-API-Key' => 'ons_live_eth_test_key_123',
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

        $this->assertEquals(999, $this->client->account->fresh()->credit_balance);
    }
}
