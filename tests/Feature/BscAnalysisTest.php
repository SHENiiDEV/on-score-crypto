<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\ApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BscAnalysisTest extends TestCase
{
    use RefreshDatabase;

    private ApiClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $account = Account::create([
            'name' => 'BSC Casino Client',
            'slug' => 'bsc-casino-client',
            'credit_balance' => 1000,
            'status' => 'active',
        ]);

        $this->client = ApiClient::create([
            'account_id' => $account->id,
            'name' => 'BSC Live Key',
            'key_id' => 'ons_live_bsc_test_key_123',
            'secret_hash' => bcrypt('secret123'),
            'status' => 'active',
            'rate_limit_per_minute' => 60,
        ]);
    }

    public function test_bsc_analysis_endpoint_creates_snapshot_and_charges_credits(): void
    {
        $response = $this->postJson('/api/v1/analyses', [
            'network' => 'bsc',
            'address' => '0x88a14d17d5ea4a6d1a9386d79042b4eb48184f4f',
            'external_player_id' => 'bsc_player_1',
        ], [
            'X-API-Key' => 'ons_live_bsc_test_key_123',
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
