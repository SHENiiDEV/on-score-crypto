<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_landing_page_renders(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('OnScore');
        $response->assertSee('VIP Player Detection');
    }

    public function test_the_app_dashboard_renders(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = \App\Models\User::whereIn('role', ['superadmin', 'admin'])->first();

        $response = $this->actingAs($admin)->get('/app');

        $response->assertStatus(200);
        $response->assertSee('OnScore');
        $response->assertSee('Dashboard');
    }
}
