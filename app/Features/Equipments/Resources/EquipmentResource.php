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
            'equipment_type_id' => $this->equipment_type_id,
            'license_plate' => $this->license_plate,
            'internal_reference' => $this->internal_reference,
            'manufacturing_year' => $this->manufacturing_year,
            'inspection_date' => $this->inspection_date?->format('Y-m-d'),
            'counting_type_id' => $this->counting_type_id,
            'status' => $this->status,
            'is_loanable' => $this->is_loanable,
            'description' => $this->description,
            'cost_per_hour' => $this->cost_per_hour,
            'revision_interval' => $this->revision_interval,
            'last_revision_date' => $this->last_revision_date?->format('Y-m-d'),
            'next_revision_date' => $this->next_revision_date?->format('Y-m-d'),
            'manager' => $this->whenLoaded('manager', function () {
                return [
                    'id' => $this->manager->id,
                    'name' => $this->manager->first_name . ' ' . $this->manager->last_name,
                ];
            }),
            'equipment_type' => $this->whenLoaded('equipmentType', function () {
                return ['id' => $this->equipmentType->id, 'name' => $this->equipmentType->name];
            }),
            'counting_type' => $this->whenLoaded('countingType', function () {
                return ['id' => $this->countingType->id, 'name' => $this->countingType->name];
            }),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
