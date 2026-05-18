<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderPlacedNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationSystemTest extends TestCase
{
    /**
     * Test notification bell shows unread count
     */
    public function test_notification_bell_displays_unread_count(): void
    {
        $user = User::factory()->create();
        $notification = $user->notify(new OrderPlacedNotification(Order::factory()->create()));

        $this->actingAs($user)
            ->get('/api/notifications/unread-count')
            ->assertJson(['count' => 1]);
    }

    /**
     * Test notifications can be marked as read
     */
    public function test_notification_can_be_marked_as_read(): void
    {
        $user = User::factory()->create();
        $user->notify(new OrderPlacedNotification(Order::factory()->create()));

        $notificationId = $user->unreadNotifications()->first()->id;

        $this->actingAs($user)
            ->post("/api/notifications/{$notificationId}/read")
            ->assertJson(['success' => true]);

        $this->assertEquals(0, $user->unreadNotifications()->count());
    }

    /**
     * Test all notifications can be marked as read
     */
    public function test_all_notifications_can_be_marked_as_read(): void
    {
        $user = User::factory()->create();
        $user->notify(new OrderPlacedNotification(Order::factory()->create()));
        $user->notify(new OrderPlacedNotification(Order::factory()->create()));

        $this->actingAs($user)
            ->post('/api/notifications/read-all')
            ->assertJson(['success' => true, 'marked' => 2]);

        $this->assertEquals(0, $user->unreadNotifications()->count());
    }

    /**
     * Test notification can be deleted
     */
    public function test_notification_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $user->notify(new OrderPlacedNotification(Order::factory()->create()));

        $notificationId = $user->notifications()->first()->id;

        $this->actingAs($user)
            ->delete("/api/notifications/{$notificationId}")
            ->assertJson(['success' => true]);

        $this->assertEquals(0, $user->notifications()->count());
    }

    /**
     * Test notification index shows all notifications with pagination
     */
    public function test_notification_index_with_pagination(): void
    {
        $user = User::factory()->create();
        for ($i = 0; $i < 20; $i++) {
            $user->notify(new OrderPlacedNotification(Order::factory()->create()));
        }

        $response = $this->actingAs($user)
            ->get('/notifications')
            ->assertStatus(200);

        // Verify pagination exists
        $this->assertStringContainsString('pagination', $response->getContent());
    }
}
