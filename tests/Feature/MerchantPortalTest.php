<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\ApiClient;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MerchantPortalTest extends TestCase
{
    use RefreshDatabase;

    private User $merchant;
    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->merchant = User::where('email', 'crm@apexgaming.com')->firstOrFail();
        $this->account = $this->merchant->account;
    }

    public function test_guest_is_redirected_from_portal(): void
    {
        $this->get('/merchant')->assertRedirect('/login');
    }

    public function test_merchant_login_lands_on_portal(): void
    {
        $this->post('/login', [
            'email' => 'crm@apexgaming.com',
            'password' => 'password123',
        ])->assertRedirect(route('merchant.dashboard'));

        $this->assertAuthenticatedAs($this->merchant);
    }

    public function test_portal_shows_shared_balance(): void
    {
        $this->actingAs($this->merchant)->get('/merchant')
            ->assertStatus(200)
            ->assertSee('Account balance')
            ->assertSee(number_format($this->account->credit_balance));
    }

    public function test_merchant_can_generate_a_key(): void
    {
        $response = $this->actingAs($this->merchant)->post('/merchant/keys', [
            'name' => 'Staging integration',
        ]);

        $response->assertRedirect(route('merchant.keys'));
        $response->assertSessionHas('new_api_key');

        $this->assertDatabaseHas('api_clients', [
            'account_id' => $this->account->id,
            'name' => 'Staging integration',
            'status' => 'active',
            'created_by' => $this->merchant->email,
        ]);
    }

    public function test_merchant_cannot_exceed_key_limit(): void
    {
        $this->account->update(['max_api_keys' => 1]);

        // The seeder already provisioned one active key for this account.
        $this->actingAs($this->merchant)
            ->from(route('merchant.keys'))
            ->post('/merchant/keys', ['name' => 'Over the limit'])
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('api_clients', ['name' => 'Over the limit']);
    }

    public function test_merchant_key_rate_limit_is_capped_by_plan(): void
    {
        $this->account->update(['default_rate_limit' => 100, 'max_api_keys' => 10]);

        $this->actingAs($this->merchant)->post('/merchant/keys', [
            'name' => 'Greedy key',
            'rate_limit' => 9000,
        ]);

        $this->assertSame(100, ApiClient::where('name', 'Greedy key')->first()->rate_limit_per_minute);
    }

    public function test_merchant_can_revoke_own_key_but_not_foreign_keys(): void
    {
        $ownKey = ApiClient::where('account_id', $this->account->id)->first();

        $this->actingAs($this->merchant)->post("/merchant/keys/{$ownKey->id}/revoke")
            ->assertRedirect(route('merchant.keys'));
        $this->assertSame('revoked', $ownKey->fresh()->status);

        $otherAccount = Account::create([
            'name' => 'Other Operator',
            'slug' => 'other-operator',
            'credit_balance' => 100,
            'status' => 'active',
        ]);
        $foreign = ApiClient::generateCredentials($otherAccount, 'Foreign key')['client'];

        $this->actingAs($this->merchant)->post("/merchant/keys/{$foreign->id}/revoke")->assertNotFound();
        $this->assertSame('active', $foreign->fresh()->status);
    }

    public function test_merchant_can_save_scoring_rules(): void
    {
        $this->actingAs($this->merchant)->post('/merchant/scoring-rules', [
            'super_vip_threshold_usd' => 600000,
            'potential_vip_threshold_usd' => 120000,
            'high_value_threshold_usd' => 20000,
            'rules' => [
                ['metric' => 'visible_balance_usd', 'operator' => '>', 'value' => 250000, 'tag' => 'whale', 'tier' => ''],
                ['metric' => 'not_a_metric', 'operator' => '>=', 'value' => 1, 'tag' => 'nope', 'tier' => ''],
                ['metric' => 'overall_score', 'operator' => '>=', 'value' => 90, 'tag' => '', 'tier' => ''],
            ],
        ])->assertRedirect(route('merchant.scoring-rules'));

        $rules = $this->account->fresh()->scoring_rules;

        $this->assertEquals(600000, $rules['super_vip_threshold_usd']);
        // Unknown metric and tag-less/tier-less rules are dropped.
        $this->assertCount(1, $rules['custom_rules']);
        $this->assertSame('WHALE', $rules['custom_rules'][0]['tag']);
    }

    public function test_merchant_can_reset_scoring_rules(): void
    {
        $this->actingAs($this->merchant)->post('/merchant/scoring-rules/reset')
            ->assertRedirect(route('merchant.scoring-rules'));

        $this->assertNull($this->account->fresh()->scoring_rules);
    }

    public function test_merchant_cannot_see_another_accounts_analyses(): void
    {
        $otherAccount = Account::create([
            'name' => 'Rival Casino',
            'slug' => 'rival-casino',
            'credit_balance' => 500,
            'status' => 'active',
        ]);

        $wallet = \App\Models\Wallet::create([
            'network' => 'ethereum',
            'address' => '0xrival',
            'normalized_address' => '0xrival',
        ]);

        $foreign = \App\Models\Analysis::create([
            'account_id' => $otherAccount->id,
            'wallet_id' => $wallet->id,
            'network' => 'ethereum',
            'address' => '0xrival',
            'status' => 'completed',
            'cost_credits' => 3,
        ]);

        $this->actingAs($this->merchant)->get("/merchant/reports/{$foreign->id}")->assertNotFound();
    }

    public function test_suspended_account_cannot_sign_in(): void
    {
        $this->account->update(['status' => 'suspended']);

        $this->post('/login', [
            'email' => 'crm@apexgaming.com',
            'password' => 'password123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_merchant_can_change_password(): void
    {
        $this->actingAs($this->merchant)->post('/merchant/settings/password', [
            'current_password' => 'password123',
            'password' => 'a-much-longer-password',
            'password_confirmation' => 'a-much-longer-password',
        ])->assertRedirect(route('merchant.settings'));

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('a-much-longer-password', $this->merchant->fresh()->password));
        $this->assertFalse($this->merchant->fresh()->must_change_password);
    }
}
