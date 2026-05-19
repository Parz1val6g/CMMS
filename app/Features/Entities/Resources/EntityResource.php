<?php

namespace App\Features\Entities\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EntityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'nif'         => $this->nif,
            'entity_type' => $this->entity_type->value,
            'phone'       => $this->phone,
            'location'    => $this->whenLoaded('location', fn() => [
                'id'   => $this->location->id,
                'name' => $this->location->name,
            ]),
            'user'        => $this->whenLoaded('user', fn() => [
                'id'   => $this->user->id,
                'name' => $this->user->first_name . ' ' . $this->user->last_name,
            ]),
            'loan_orders_count' => $this->when(
                isset($this->loan_orders_count),
                $this->loan_orders_count
            ),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
