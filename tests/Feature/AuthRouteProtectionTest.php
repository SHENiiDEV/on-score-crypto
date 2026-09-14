<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRouteProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_app_dashboard(): void
    {
        $response = $this->get('/app');
        $response->assertRedirect('/login');
    }

    public function test_guests_cannot_access_reports(): void
    {
        $response = $this->get('/report/any-id');
        $response->assertRedirect('/login');
    }

    public function test_guests_cannot_access_merchant_portal(): void
    {
        $this->get('/merchant')->assertRedirect('/login');
        $this->get('/merchant/keys')->assertRedirect('/login');
        $this->get('/merchant/scoring-rules')->assertRedirect('/login');
    }

    public function test_admin_can_access_app_dashboard(): void
    {
        $account = Account::create([
            'name' => 'Demo Admin Account',
            'slug' => 'demo-admin-account',
            'credit_balance' => 500,
            'status' => 'active',
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'account_id' => $account->id,
        ]);

        $response = $this->actingAs($admin)->get('/app');
        $response->assertStatus(200);
    }
}
