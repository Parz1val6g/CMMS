<?php

namespace App\Features\Tickets\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'description' => $this->description,
            'priority'    => [
                'value' => $this->priority->value,
                'label' => $this->priority->label(),
            ],
            'status'      => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'created_at'  => $this->created_at->toIso8601String(),

            'ticket_manager' => $this->whenLoaded('ticketManager', function () {
                return [
                    'id'   => $this->ticketManager->id,
                    'name' => $this->ticketManager->first_name . ' ' . $this->ticketManager->last_name,
                ];
            }),

            'client' => $this->whenLoaded('client', function () {
                return [
                    'id'   => $this->client->id,
                    'name' => $this->client->user->first_name . ' ' . $this->client->user->last_name,
                ];
            }),

            'service_type' => $this->whenLoaded('serviceType', function () {
                return [
                    'id'   => $this->serviceType->id,
                    'name' => $this->serviceType->name,
                ];
            }),

            'location' => $this->whenLoaded('location', function () {
                if (!$this->location) return null;
                return [
                    'id'              => $this->location->id,
                    'parish'          => $this->location->parish?->name,
                    'street_address'  => $this->location->street_address,
                    'reference_point' => $this->location->landmark,
                    'postal_code'     => $this->location->postal_code,
                ];
            }),

            'service_order' => $this->whenLoaded('serviceOrder', function () {
                if (!$this->serviceOrder) {
                    return null;
                }
                return [
                    'id'      => $this->serviceOrder->id,
                    'process' => $this->serviceOrder->process,
                ];
            }),
        ];
    }
}
