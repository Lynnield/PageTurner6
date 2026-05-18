<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * NotificationService: Manage user notifications with filtering and marking
 */
class NotificationService
{
    /**
     * Get unread notification count for user
     */
    public static function getUnreadCount(?User $user = null): int
    {
        $user = $user ?? auth()->user();
        return $user?->unreadNotifications()->count() ?? 0;
    }

    /**
     * Get paginated notifications for user
     */
    public static function getNotifications(?User $user = null, int $perPage = 15)
    {
        $user = $user ?? auth()->user();
        
        return $user->notifications()
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get unread notifications only
     */
    public static function getUnreadNotifications(?User $user = null, int $limit = 10): Collection
    {
        $user = $user ?? auth()->user();
        
        return $user->unreadNotifications()
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Mark single notification as read
     */
    public static function markAsRead(string $notificationId): bool
    {
        $notification = auth()->user()?->notifications()
            ->find($notificationId);

        if ($notification) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    /**
     * Mark all notifications as read
     */
    public static function markAllAsRead(?User $user = null): int
    {
        $user = $user ?? auth()->user();
        
        return $user->unreadNotifications()
            ->update(['read_at' => now()]);
    }

    /**
     * Delete notification
     */
    public static function delete(string $notificationId): bool
    {
        return auth()->user()?->notifications()
            ->where('id', $notificationId)
            ->delete() > 0;
    }

    /**
     * Delete all notifications
     */
    public static function deleteAll(?User $user = null): int
    {
        $user = $user ?? auth()->user();
        
        return $user->notifications()->delete();
    }

    /**
     * Filter notifications by type
     */
    public static function filterByType(?User $user = null, string $type = ''): Collection
    {
        $user = $user ?? auth()->user();
        
        return $user->notifications()
            ->where('type', 'like', "%{$type}%")
            ->latest()
            ->get();
    }
}
