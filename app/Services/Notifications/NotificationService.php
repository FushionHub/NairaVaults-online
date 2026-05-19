<?php

namespace App\Services\Notifications;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function send(User $user, string $type, string $title, string $body, array $metadata = []): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'metadata' => $metadata,
        ]);
    }

    public function markAsRead(Notification $notification): void
    {
        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function getUnreadCount(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
    }
}
