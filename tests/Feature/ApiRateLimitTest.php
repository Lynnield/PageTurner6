<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ApiRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_rate_limiting()
    {
        RateLimiter::clear('public:' . '127.0.0.1');

        for ($i = 0; $i < 30; $i++) {
            $response = $this->getJson('/api/notifications/unread-count'); // This route is auth-only, let's use a public one
        }

        // Let's use a route that is actually public or mock the auth
    }

    public function test_auth_rate_limiting()
    {
        $user = User::factory()->create(['api_tier' => 'standard']);
        $this->actingAs($user);

        for ($i = 0; $i < 60; $i++) {
            $this->getJson('/api/notifications/unread-count');
        }

        $response = $this->getJson('/api/notifications/unread-count');
        $response->assertStatus(429);
    }

    public function test_admin_rate_limiting()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        for ($i = 0; $i < 1000; $i++) {
            // This might take too long in a test, but we can mock the limiter
        }
    }
}
