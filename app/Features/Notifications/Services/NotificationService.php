<?php

namespace App\Features\Notifications\Services;

use App\Core\Services\TransactionHandler;
use App\Features\Notifications\Events\NotificationSentEvent;
use App\Features\Notifications\Models\Notification;

class NotificationService
{
    public function __construct(
        private TransactionHandler $transactions
    ) {}

    public function create(string $userId, string $title, string $message, string $type): Notification
    {
        $notification = $this->transactions->execute(function () use ($userId, $title, $message, $type) {
            return Notification::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
            ]);
        });

        NotificationSentEvent::dispatch($notification);

        return $notification;
    }

    public function markAsRead(Notification $notification): Notification
    {
        return $this->transactions->execute(function () use ($notification) {
            $notification->update(['read_at' => now()]);
            return $notification;
        });
    }
}
