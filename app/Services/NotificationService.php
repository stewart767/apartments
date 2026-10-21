<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\HostelSystemNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send notification to user via database and push notification (FCM).
     */
    public function send(User $user, string $title, string $message, ?string $actionUrl = null, array $extraData = []): void
    {
        // 1. Send Laravel Database Notification
        $user->notify(new HostelSystemNotification($title, $message, $actionUrl));

        // 2. Send Push Notification if FCM token exists
        if (!empty($user->fcm_token)) {
            $this->sendPushNotification($user->fcm_token, $title, $message, $extraData);
        }
    }

    /**
     * Send FCM push notification.
     */
    public function sendPushNotification(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        $serverKey = config('services.fcm.server_key') ?? env('FCM_SERVER_KEY');
        if (!$serverKey) {
            // Log in dev mode
            Log::info("FCM Notification Simulated: To: {$fcmToken}, Title: {$title}, Body: {$body}");
            return true;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                ],
                'data' => array_merge($data, [
                    'title' => $title,
                    'message' => $body,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ]),
                'priority' => 'high',
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("FCM Send Error: " . $e->getMessage());
            return false;
        }
    }
}
