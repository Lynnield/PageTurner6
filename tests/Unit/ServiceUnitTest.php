<?php

namespace Tests\Unit;

use App\Services\AuditService;
use App\Services\NotificationService;
use App\Services\RateLimiterService;
use App\Models\User;
use Tests\TestCase;

class ServiceUnitTest extends TestCase
{
    /**
     * Test AuditService can log events
     */
    public function test_audit_service_logs_events(): void
    {
        $user = User::factory()->create();

        $audit = AuditService::log(
            'test_event',
            'TestModel',
            1,
            [],
            ['key' => 'value'],
            $user
        );

        $this->assertNotNull($audit);
        $this->assertEquals('test_event', $audit->event);
        $this->assertEquals('TestModel', $audit->auditable_type);
    }

    /**
     * Test NotificationService gets unread count
     */
    public function test_notification_service_unread_count(): void
    {
        $user = User::factory()->create();
        $count = NotificationService::getUnreadCount($user);

        $this->assertEquals(0, $count);
    }

    /**
     * Test RateLimiterService gets user limit
     */
    public function test_rate_limiter_service_user_limit(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $limit = RateLimiterService::getLimitForUser($admin);

        $this->assertEquals(1000, $limit['requests']);
    }

    /**
     * Test RateLimiterService standard user limit
     */
    public function test_rate_limiter_service_standard_limit(): void
    {
        $user = User::factory()->create(['api_tier' => 'standard']);
        $limit = RateLimiterService::getLimitForUser($user);

        $this->assertEquals(60, $limit['requests']);
    }
}
