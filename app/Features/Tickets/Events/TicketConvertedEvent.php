<?php

namespace App\Features\Tickets\Events;

use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\Tickets\Models\Ticket;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketConvertedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public ServiceOrder $serviceOrder,
    ) {}
}
