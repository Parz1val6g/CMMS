<?php

namespace App\Features\Tickets\Services;

use App\Core\Enums\TicketStatus;
use App\Core\Helpers\InputSanitizer;
use App\Core\Services\TransactionHandler;
use App\Features\Locations\Models\Location;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\ServiceOrders\Services\ServiceOrderService;
use App\Features\Tickets\Events\TicketConvertedEvent;
use App\Features\Tickets\Models\Ticket;
use InvalidArgumentException;

class TicketService
{
    public function __construct(
        private TransactionHandler $transactions,
        private ServiceOrderService $serviceOrderService,
    ) {}

    public function create(array $data, string $managerId): Ticket
    {
        return $this->transactions->execute(function () use ($data, $managerId) {
            $locationId = null;
            if (!empty($data['parish_id'])) {
                $location = Location::create([
                    'parish_id'      => $data['parish_id'],
                    'postal_code'    => $data['postal_code'] ?? '',
                    'street_address' => InputSanitizer::sanitize($data['street'] ?? ''),
                    'landmark'       => InputSanitizer::sanitize($data['reference_point'] ?? ''),
                    'latitude'       => $data['latitude'] ?? null,
                    'longitude'      => $data['longitude'] ?? null,
                ]);
                $locationId = $location->id;
            }

            return Ticket::create(array_merge($data, [
                'status'            => TicketStatus::OPEN->value,
                'ticket_manager_id' => $managerId,
                'location_id'       => $locationId,
            ]));
        });
    }

    public function update(Ticket $ticket, array $data): Ticket
    {
        if ($ticket->status->isTerminal()) {
            throw new InvalidArgumentException(__('messages.services.ticket.cannot_update_terminal'));
        }

        return $this->transactions->execute(function () use ($ticket, $data) {
            $ticket->update($data);
            return $ticket;
        });
    }

    public function cancel(Ticket $ticket): Ticket
    {
        if ($ticket->status->isTerminal()) {
            throw new InvalidArgumentException(__('messages.services.ticket.already_terminal'));
        }

        return $this->transactions->execute(function () use ($ticket) {
            $ticket->update(['status' => TicketStatus::CANCELLED->value]);
            return $ticket;
        });
    }

    public function convertToServiceOrder(Ticket $ticket, array $soData): ServiceOrder
    {
        if ($ticket->status->isTerminal()) {
            throw new InvalidArgumentException(__('messages.services.ticket.already_terminal'));
        }

        return $this->transactions->execute(function () use ($ticket, $soData) {
            $managerId = $soData['manager_id'] ?? $ticket->ticket_manager_id;

            $serviceOrder = $this->serviceOrderService->create($soData, $managerId);

            $ticket->update([
                'service_order_id' => $serviceOrder->id,
                'status'           => TicketStatus::CONVERTED->value,
            ]);

            TicketConvertedEvent::dispatch($ticket->fresh(), $serviceOrder);

            return $serviceOrder;
        });
    }
}
