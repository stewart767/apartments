<?php

namespace App\Http\Controllers\Api\V1\Notifications;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\NotificationResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends ApiController
{
    /**
     * List notifications for authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->paginate((int) $request->input('per_page', 15));

        return $this->paginatedResponse($notifications, NotificationResource::class, 'Notifications retrieved.');
    }

    /**
     * Get unread notification count.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = $request->user()->unreadNotifications()->count();
        return $this->successResponse(['unread_count' => $count], 'Unread notification count retrieved.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();
        return $this->successResponse(null, 'All notifications marked as read.');
    }

    /**
     * Mark single notification as read.
     */
    public function markRead(Request $request, $id): JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return $this->successResponse(null, 'Notification marked as read.');
    }
}
