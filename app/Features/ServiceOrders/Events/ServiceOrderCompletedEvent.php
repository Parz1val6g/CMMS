<?php
namespace App\Features\ServiceOrders\Events;
use App\Features\ServiceOrders\Models\ServiceOrder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class ServiceOrderCompletedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public function __construct(
        public ServiceOrder $serviceOrder
    ) {
    }
}