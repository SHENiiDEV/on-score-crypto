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
    private User $merchantUser;
    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('role', 'superadmin')->first();
        $this->merchantUser = User::where('role', 'merchant_owner')->first();
        $this->account = Account::first();
    }

    public function test_guest_cannot_access_admin_console(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_merchant_user_cannot_access_admin_console(): void
    {
        $this->actingAs($this->merchantUser)->get('/admin')->assertRedirect('/login');
    }

    public function test_admin_can_login_and_open_console(): void
    {
        $this->post('/login', [
            'email' => 'admin@onscore.io',
            'password' => 'password123',
        ])->assertRedirect('/admin');

        $this->assertAuthenticatedAs($this->admin);

        $this->actingAs($this->admin)->get('/admin')
            ->assertStatus(200)
            ->assertSee('Command center');
    }

    public function test_admin_can_onboard_client_with_portal_access(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/clients', [
            'name' => 'Royal Vegas Gaming Group',
            'initial_credits' => 2000,
            'status' => 'active',
            'max_api_keys' => 8,
            'default_rate_limit' => 240,
            'owner_name' => 'Royal Vegas CRM',
            'owner_email' => 'crm@royalvegas.test',
        ]);

        $account = Account::where('name', 'Royal Vegas Gaming Group')->first();

        $this->assertNotNull($account);
        $response->assertRedirect(route('admin.clients.show', $account->id));
        $response->assertSessionHas('new_merchant_access');

        $this->assertSame(2000, $account->credit_balance);
        $this->assertSame(8, $account->max_api_keys);

        $this->assertDatabaseHas('users', [
            'email' => 'crm@royalvegas.test',
            'account_id' => $account->id,
            'role' => 'merchant_owner',
            'must_change_password' => true,
        ]);
    }

    public function test_admin_can_generate_and_revoke_api_key(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('admin.keys'))
            ->post('/admin/api-keys/generate', [
                'account_id' => $this->account->id,
                'name' => 'Casino Core Test Key',
                'rate_limit' => 500,
            ]);

        $response->assertRedirect(route('admin.keys'));
        $response->assertSessionHas('new_api_key');

        $this->assertDatabaseHas('api_clients', [
            'account_id' => $this->account->id,
            'name' => 'Casino Core Test Key',
            'status' => 'active',
            'rate_limit_per_minute' => 500,
        ]);

        $newClient = ApiClient::where('name', 'Casino Core Test Key')->first();

        $this->actingAs($this->admin)
            ->from(route('admin.keys'))
            ->post("/admin/api-keys/{$newClient->id}/revoke")
            ->assertRedirect(route('admin.keys'));

        $this->assertEquals('revoked', $newClient->fresh()->status);
    }

    public function test_admin_can_adjust_client_credits(): void
    {
        $initial = $this->account->credit_balance;

        $this->actingAs($this->admin)
            ->from(route('admin.ledger'))
            ->post('/admin/credits/adjust', [
                'account_id' => $this->account->id,
                'delta' => 750,
                'reason' => 'VIP Contract Bonus Package',
            ])
            ->assertRedirect(route('admin.ledger'));

        $this->assertEquals($initial + 750, $this->account->fresh()->credit_balance);

        $this->assertDatabaseHas('credit_ledger', [
            'account_id' => $this->account->id,
            'delta' => 750,
        ]);
    }

    public function test_admin_can_update_client_scoring_rules(): void
    {
        $this->actingAs($this->admin)->post("/admin/clients/{$this->account->id}/scoring-rules", [
            'super_vip_threshold_usd' => 500000,
            'potential_vip_threshold_usd' => 150000,
            'high_value_threshold_usd' => 30000,
            'rules' => [
                ['metric' => 'gambling_turnover_usd', 'operator' => '>=', 'value' => 75000, 'tag' => 'whale_host', 'tier' => 'super_vip'],
            ],
        ])->assertRedirect(route('admin.clients.show', $this->account->id));

        $rules = $this->account->fresh()->scoring_rules;

        $this->assertEquals(500000, $rules['super_vip_threshold_usd']);
        $this->assertCount(1, $rules['custom_rules']);
        $this->assertSame('WHALE_HOST', $rules['custom_rules'][0]['tag']);
        $this->assertSame('super_vip', $rules['custom_rules'][0]['tier']);
    }

    public function test_scoring_rule_thresholds_must_decrease(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.clients.show', $this->account->id))
            ->post("/admin/clients/{$this->account->id}/scoring-rules", [
                'super_vip_threshold_usd' => 1000,
                'potential_vip_threshold_usd' => 50000,
                'high_value_threshold_usd' => 90000,
            ])
            ->assertSessionHas('error');
    }

    public function test_admin_can_impersonate_a_client(): void
    {
        $this->actingAs($this->admin)
            ->post("/admin/clients/{$this->account->id}/impersonate")
            ->assertRedirect(route('merchant.dashboard'));

        $this->actingAs($this->admin)->get('/merchant')->assertStatus(200);
    }

    public function test_admin_can_add_entity_address(): void
    {
        $entity = \App\Models\Entity::where('slug', 'bybit')->first();

        $this->actingAs($this->admin)->post('/admin/entities/add-address', [
            'entity_id' => $entity->id,
            'network' => 'tron',
            'address' => 'TBybitNewHotWalletTRON998877112233',
            'label' => 'Bybit New Hot Wallet 99',
            'confidence' => 0.98,
        ])->assertRedirect('/admin/entities');

        $this->assertDatabaseHas('entity_addresses', [
            'entity_id' => $entity->id,
            'address' => 'TBybitNewHotWalletTRON998877112233',
        ]);
    }
}
