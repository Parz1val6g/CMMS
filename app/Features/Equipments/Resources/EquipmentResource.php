<?php

namespace App\Features\Equipments\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EquipmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'brand' => $this->brand,
            'model' => $this->model,
            'serial_number' => $this->serial_number,
            'status' => $this->status,
            'is_loanable' => $this->is_loanable,
            'description' => $this->description,
            'cost_per_hour' => $this->cost_per_hour,
            'revision_interval_days' => $this->revision_interval_days,
            'last_revision_date' => $this->last_revision_date?->format('Y-m-d'),
            'next_revision_date' => $this->next_revision_date?->format('Y-m-d'),
            'manager' => $this->whenLoaded('manager', function () {
                return [
                    'id' => $this->manager->id,
                    'name' => $this->manager->first_name . ' ' . $this->manager->last_name,
                ];
            }),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
