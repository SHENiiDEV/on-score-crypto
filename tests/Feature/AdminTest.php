<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\ApiClient;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $regularUser;
    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('role', 'superadmin')->first();
        $this->regularUser = User::where('role', 'user')->first();
        $this->account = Account::first();
    }

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');
    }

    public function test_regular_user_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->regularUser)->get('/admin');
        $response->assertRedirect('/login');
    }

    public function test_admin_can_login_and_access_admin_dashboard(): void
    {
        $loginResp = $this->post('/login', [
            'email' => 'admin@onscore.io',
            'password' => 'password123',
        ]);

        $loginResp->assertRedirect('/admin');
        $this->assertAuthenticatedAs($this->admin);

        $dashResp = $this->actingAs($this->admin)->get('/admin');
        $dashResp->assertStatus(200)
            ->assertSee('On-Score Admin Dashboard')
            ->assertSee('Provision API Key');
    }

    public function test_admin_can_create_new_b2b_account(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/accounts', [
            'name' => 'Royal Vegas Gaming Group',
            'initial_credits' => 2000,
            'status' => 'active',
        ]);

        $response->assertRedirect('/admin');
        $this->assertDatabaseHas('accounts', [
            'name' => 'Royal Vegas Gaming Group',
            'credit_balance' => 2000,
        ]);
    }

    public function test_admin_can_generate_and_revoke_api_key(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/api-keys/generate', [
            'account_id' => $this->account->id,
            'name' => 'Casino Core Test Key',
            'rate_limit' => 500,
        ]);

        $response->assertRedirect('/admin');
        $response->assertSessionHas('new_api_key');

        $this->assertDatabaseHas('api_clients', [
            'account_id' => $this->account->id,
            'name' => 'Casino Core Test Key',
            'status' => 'active',
            'rate_limit_per_minute' => 500,
        ]);

        $newClient = ApiClient::where('name', 'Casino Core Test Key')->first();

        // Revoke key
        $revokeResp = $this->actingAs($this->admin)->post("/admin/api-keys/{$newClient->id}/revoke");
        $revokeResp->assertRedirect('/admin');

        $this->assertEquals('revoked', $newClient->fresh()->status);
    }

    public function test_admin_can_adjust_client_credits(): void
    {
        $initial = $this->account->credit_balance;

        $response = $this->actingAs($this->admin)->post('/admin/credits/adjust', [
            'account_id' => $this->account->id,
            'delta' => 750,
            'reason' => 'VIP Contract Bonus Package',
        ]);

        $response->assertRedirect('/admin');
        $this->assertEquals($initial + 750, $this->account->fresh()->credit_balance);

        $this->assertDatabaseHas('credit_ledger', [
            'account_id' => $this->account->id,
            'delta' => 750,
            'reason' => 'VIP Contract Bonus Package',
        ]);
    }

    public function test_admin_can_add_entity_address(): void
    {
        $entity = \App\Models\Entity::where('slug', 'bybit')->first();

        $response = $this->actingAs($this->admin)->post('/admin/entities/add-address', [
            'entity_id' => $entity->id,
            'network' => 'tron',
            'address' => 'TBybitNewHotWalletTRON998877112233',
            'label' => 'Bybit New Hot Wallet 99',
            'confidence' => 0.98,
        ]);

        $response->assertRedirect('/admin/entities');
        $this->assertDatabaseHas('entity_addresses', [
            'entity_id' => $entity->id,
            'address' => 'TBybitNewHotWalletTRON998877112233',
        ]);
    }
}
