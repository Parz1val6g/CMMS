<?php
namespace App\Features\Notifications\Listeners;

use App\Features\Notifications\Services\NotificationService;
use App\Features\ServiceOrders\Events\ServiceOrderCreatedEvent;

class SendServiceOrderCreatedNotification
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function handle(ServiceOrderCreatedEvent $event): void
    {
        $so = $event->serviceOrder;
        $manager = $so->manager;

        if (!$manager) {
            return;
        }

        $this->notificationService->create(
            userId: $manager->id,
            title: __('messages.services.notifications.new_order_title'),
            message: __('messages.services.notifications.new_order_message', ['process' => $so->process]),
            type: 'service_order_created',
        );
    }
}
