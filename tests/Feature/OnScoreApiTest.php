<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\ApiClient;
use App\Models\Analysis;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnScoreApiTest extends TestCase
{
    use RefreshDatabase;

    private Account $account;
    private ApiClient $apiClient;
    private string $keyId;
    private string $secret;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->account = Account::where('slug', 'apex-gaming')->first();
        $this->apiClient = ApiClient::where('account_id', $this->account->id)->first();
        $this->keyId = $this->apiClient->key_id;
        $this->secret = 'ons_sec_demo_secret_apex_778899aabbcc';
    }

    public function test_api_authentication_required(): void
    {
        $response = $this->postJson('/api/v1/analyses', [
            'network' => 'tron',
            'address' => 'TVipWhalePlayerAddressTron7788991122',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('error.code', 'UNAUTHORIZED');
    }

    public function test_api_invalid_secret_returns_401(): void
    {
        $response = $this->withHeaders([
            'X-API-Key' => $this->keyId,
            'X-API-Secret' => 'wrong_secret_123',
        ])->postJson('/api/v1/analyses', [
            'network' => 'tron',
            'address' => 'TVipWhalePlayerAddressTron7788991122',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('error.code', 'INVALID_API_SECRET');
    }

    public function test_vip_whale_analysis_returns_potential_vip_score(): void
    {
        $initialBalance = $this->account->fresh()->credit_balance;

        $response = $this->withHeaders([
            'X-API-Key' => $this->keyId,
            'X-API-Secret' => $this->secret,
        ])->postJson('/api/v1/analyses', [
            'network' => 'tron',
            'address' => 'TVipWhalePlayerAddressTron7788991122',
            'external_player_id' => 'player_vip_991',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'completed')
            ->assertJsonPath('network', 'tron')
            ->assertJsonPath('score.segment', 'super_vip')
            ->assertJsonPath('gambling.status', 'detected');

        $this->assertGreaterThanOrEqual(85, $response->json('score.value'));
        $this->assertEquals($initialBalance - 3, $this->account->fresh()->credit_balance);

        // Verify Credit Ledger entry
        $this->assertDatabaseHas('credit_ledger', [
            'account_id' => $this->account->id,
            'type' => 'reservation',
            'delta' => -3,
        ]);
    }

    public function test_bybit_hotwallet_is_classified_as_cex_pool_and_not_vip(): void
    {
        $bybitAddress = 'TLyqzVGLV1srkB7dToTAnYg9auqwTmTX71';

        $response = $this->withHeaders([
            'X-API-Key' => $this->keyId,
            'X-API-Secret' => $this->secret,
        ])->postJson('/api/v1/analyses', [
            'network' => 'tron',
            'address' => $bybitAddress,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'completed')
            ->assertJsonPath('wallet.is_custodial_cex', true)
            ->assertJsonPath('wallet.visible_balance_usd', 0)
            ->assertJsonPath('score.segment', 'low_value')
            ->assertJsonPath('score.value', 0);

        $drivers = $response->json('score.key_drivers');
        $this->assertContains('target_is_custodial_cex_pool', $drivers);
    }

    public function test_insufficient_credits_returns_402(): void
    {
        $this->account->update(['credit_balance' => 0]);

        $response = $this->withHeaders([
            'X-API-Key' => $this->keyId,
            'X-API-Secret' => $this->secret,
        ])->postJson('/api/v1/analyses', [
            'network' => 'tron',
            'address' => 'TVipWhalePlayerAddressTron7788991122',
        ]);

        $response->assertStatus(402)
            ->assertJsonPath('error.code', 'INSUFFICIENT_CREDITS');
    }

    public function test_single_analysis_get_and_list(): void
    {
        $createResp = $this->withHeaders([
            'X-API-Key' => $this->keyId,
            'X-API-Secret' => $this->secret,
        ])->postJson('/api/v1/analyses', [
            'network' => 'tron',
            'address' => 'TVipWhalePlayerAddressTron7788991122',
        ]);

        $analysisId = $createResp->json('analysis_id');

        // GET /v1/analyses/{id}
        $getResp = $this->withHeaders([
            'X-API-Key' => $this->keyId,
            'X-API-Secret' => $this->secret,
        ])->getJson("/api/v1/analyses/{$analysisId}");

        $getResp->assertStatus(200)
            ->assertJsonPath('analysis_id', $analysisId)
            ->assertJsonPath('status', 'completed');

        // GET /v1/analyses (List)
        $listResp = $this->withHeaders([
            'X-API-Key' => $this->keyId,
            'X-API-Secret' => $this->secret,
        ])->getJson('/api/v1/analyses');

        $listResp->assertStatus(200)
            ->assertJsonStructure(['data', 'pagination']);
    }

    public function test_batch_analysis_execution(): void
    {
        $response = $this->withHeaders([
            'X-API-Key' => $this->keyId,
            'X-API-Secret' => $this->secret,
        ])->postJson('/api/v1/batches', [
            'wallets' => [
                ['network' => 'tron', 'address' => 'TVipWhalePlayerAddressTron7788991122', 'external_player_id' => 'p1'],
                ['network' => 'tron', 'address' => 'TRegularGamblerUserTrx9988112233', 'external_player_id' => 'p2'],
            ],
        ]);

        $response->assertStatus(202)
            ->assertJsonPath('total_wallets', 2)
            ->assertJsonPath('completed_count', 2);

        $jobId = $response->json('job_id');

        // Fetch batch results
        $resultsResp = $this->withHeaders([
            'X-API-Key' => $this->keyId,
            'X-API-Secret' => $this->secret,
        ])->getJson("/api/v1/batches/{$jobId}/results");

        $resultsResp->assertStatus(200)
            ->assertJsonPath('job_id', $jobId)
            ->assertJsonCount(2, 'results');
    }

    public function test_get_account_credits(): void
    {
        $response = $this->withHeaders([
            'X-API-Key' => $this->keyId,
            'X-API-Secret' => $this->secret,
        ])->getJson('/api/v1/account/credits');

        $response->assertStatus(200)
            ->assertJsonPath('account_id', $this->account->id)
            ->assertJsonPath('credit_balance', $this->account->credit_balance)
            ->assertJsonStructure([
                'account_id',
                'account_name',
                'credit_balance',
                'status',
                'ledger_recent',
            ]);
    }

    public function test_web_dashboard_and_report_flow(): void
    {
        $admin = \App\Models\User::whereIn('role', ['superadmin', 'admin'])->first();

        $dashResp = $this->actingAs($admin)->get('/app');
        $dashResp->assertStatus(200)
            ->assertSee('On-Score')
            ->assertSee('Apex Gaming Group');

        $postResp = $this->actingAs($admin)->post('/analyze', [
            'network' => 'tron',
            'address' => 'TVipWhalePlayerAddressTron7788991122',
            'external_player_id' => 'player_web_1',
        ]);

        $postResp->assertRedirect();
        $this->assertDatabaseHas('analyses', [
            'external_player_id' => 'player_web_1',
            'status' => 'completed',
        ]);

        $latestAnalysis = Analysis::latest()->first();
        $reportResp = $this->actingAs($admin)->get("/report/{$latestAnalysis->id}");
        $reportResp->assertStatus(200)
            ->assertSee('SUPER VIP')
            ->assertSee('Player On-Chain Intelligence Profile');
    }

    public function test_score_wallet_alias_and_chain_parameter_works_with_bearer_token(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->keyId}",
        ])->postJson('/api/v1/score/wallet', [
            'chain' => 'ETH',
            'address' => '0x28c6c06298d514db089934071355e5743bf21d60',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'completed')
            ->assertJsonPath('network', 'ethereum')
            ->assertJsonPath('address', '0x28c6c06298d514db089934071355e5743bf21d60');
    }
}
