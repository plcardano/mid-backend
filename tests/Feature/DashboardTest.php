<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithJwtAuth;

class DashboardTest extends TestCase
{
    use RefreshDatabase, WithJwtAuth;

    public function test_dashboard_page_is_displayed()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAsWithJwt($user)
            ->get('/dashboard');

        $response->assertOk();
    }

    public function test_dashboard_cannot_be_accessed_by_guests()
    {
        $this->markTestSkipped();
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }
}
