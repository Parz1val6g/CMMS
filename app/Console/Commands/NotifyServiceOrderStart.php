<?php

namespace App\Console\Commands;

use App\Core\Enums\ServiceOrderStatus;
use App\Core\Services\TransactionHandler;
use App\Features\Notifications\Services\NotificationService;
use App\Features\ServiceOrders\Models\ServiceOrder;
use Illuminate\Console\Command;

class NotifyServiceOrderStart extends Command
{
    protected $signature = 'app:notify-service-order-start';

    protected $description = 'Notify managers of service orders whose execution date is today';

    public function __construct(
        private NotificationService $notificationService,
        private TransactionHandler $transactions,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $orders = ServiceOrder::query()
            ->whereDate('end_date', today())
            ->where('status', ServiceOrderStatus::PENDING->value)
            ->whereNull('start_notified_at')
            ->get();

        $notified = 0;

        foreach ($orders as $order) {
            if (!$order->manager_id) {
                continue;
            }

            $this->transactions->execute(function () use ($order) {
                $this->notificationService->create(
                    userId: $order->manager_id,
                    title: __('messages.services.notifications.service_order_start_title'),
                    message: __('messages.services.notifications.service_order_start_message', ['process' => $order->process]),
                    type: 'service_order_start_notification',
                );

                $order->update(['start_notified_at' => now()]);
            });

            $notified++;
        }

        $this->info(sprintf('Notified %d service order(s).', $notified));

        return self::SUCCESS;
    }
}
