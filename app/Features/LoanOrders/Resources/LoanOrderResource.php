<?php

namespace App\Features\LoanOrders\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $firstPivot = $this->relationLoaded('equipments') ? $this->equipments->first()?->pivot : null;

        return [
            'id'             => $this->id,
            'reference'      => $this->reference,
            'status'         => $this->status,

            'start_date'     => $firstPivot?->start_date,
            'end_date'       => $firstPivot?->end_date,
            'needs_operator' => (bool) ($firstPivot?->needs_operator ?? false),

            'entity'         => $this->whenLoaded('entity', fn() => $this->entity ? [
                'id'          => $this->entity->id,
                'name'        => $this->entity->name,
                'nif'         => $this->entity->nif,
                'entity_type' => $this->entity->entity_type,
            ] : null),

            'manager'        => $this->whenLoaded('manager', fn() => [
                'id'   => $this->manager->id,
                'name' => $this->manager->name,
            ]),

            'approved_by'    => $this->whenLoaded('approvedBy', fn() => [
                'id'   => $this->approvedBy->id,
                'name' => $this->approvedBy->name,
            ]),

            'cancelled_by'   => $this->whenLoaded('cancelledBy', fn() => [
                'id'   => $this->cancelledBy->id,
                'name' => $this->cancelledBy->name,
            ]),

            'location'       => $this->whenLoaded('location', fn() => [
                'id'             => $this->location->id,
                'parish_id'      => $this->location->parish_id,
                'parish'         => $this->whenLoaded('location.parish', fn() => [
                    'id'   => $this->location->parish->id,
                    'name' => $this->location->parish->name,
                ]),
                'street'         => $this->location->street_address,
                'reference_point'=> $this->location->landmark,
                'postal_code'    => $this->location->postal_code,
                'latitude'       => $this->location->latitude,
                'longitude'      => $this->location->longitude,
            ]),

            'equipments'     => $this->whenLoaded('equipments', fn() =>
                $this->equipments->map(fn($eq) => [
                    'id'             => $eq->id,
                    'reference'      => $eq->reference,
                    'name'           => $eq->name,
                    'brand'          => $eq->brand,
                    'model'          => $eq->model,
                    'serial_number'  => $eq->serial_number,
                    'status'         => $eq->status,
                    'is_loanable'    => (bool) $eq->is_loanable,
                    'start_date'     => $eq->pivot?->start_date,
                    'end_date'       => $eq->pivot?->end_date,
                    'needs_operator' => (bool) ($eq->pivot?->needs_operator ?? false),
                ])
            ),

            'tasks'          => $this->whenLoaded('tasks', fn() =>
                $this->tasks->map(fn($task) => [
                    'id'          => $task->id,
                    'reference'   => $task->reference,
                    'description' => $task->description,
                    'status'      => $task->status,
                    'manager'     => $this->whenLoaded('tasks.manager', fn() => $task->relationLoaded('manager') && $task->manager ? [
                        'id'   => $task->manager->id,
                        'name' => $task->manager->name,
                    ] : null),
                ])
            ),

            'description'     => $this->description,
            'notes_checkout'  => $this->notes_checkout,
            'notes_return'    => $this->notes_return,
            'notes_cancel'    => $this->notes_cancel,
            'approved_at'     => $this->approved_at,
            'checked_out_at'  => $this->checked_out_at,
            'returned_at'     => $this->returned_at,
            'cancelled_at'    => $this->cancelled_at,
            'deleted_at'      => $this->deleted_at,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}
