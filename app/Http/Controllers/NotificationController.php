<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;

/**
 * NotificationController: Manage user notifications with AJAX endpoints
 */
class NotificationController extends Controller
{
    /**
     * Get all notifications for authenticated user
     */
    public function index(Request $request)
    {
        $notifications = NotificationService::getNotifications(
            perPage: $request->get('per_page', 15)
        );

        if ($request->expectsJson()) {
            return response()->json($notifications);
        }

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Get latest unread notifications (for dropdown)
     */
    public function latest(Request $request)
    {
        $unread = NotificationService::getUnreadNotifications(
            limit: $request->get('limit', 10)
        );

        return response()->json([
            'notifications' => $unread,
            'count' => NotificationService::getUnreadCount(),
        ]);
    }

    /**
     * Mark single notification as read
     */
    public function markAsRead($id)
    {
        NotificationService::markAsRead($id);

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $count = NotificationService::markAllAsRead();

        return response()->json([
            'success' => true,
            'marked' => $count,
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy($id)
    {
        NotificationService::delete($id);

        return response()->json(['success' => true]);
    }

    /**
     * Delete all notifications
     */
    public function destroyAll()
    {
        $count = NotificationService::deleteAll();

        return response()->json([
            'success' => true,
            'deleted' => $count,
        ]);
    }

    /**
     * Get unread count (for AJAX polling)
     */
    public function unreadCount()
    {
        return response()->json([
            'count' => NotificationService::getUnreadCount(),
        ]);
    }
}
