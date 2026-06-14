<?php

namespace App\Features\Equipments\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EquipmentRevisionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'equipment_id'  => $this->equipment_id,
            'status'        => $this->status,
            'approved_by'   => $this->approved_by,
            'approved_at'   => $this->approved_at?->format('Y-m-d H:i'),
            'revision_date' => $this->revision_date?->format('Y-m-d H:i'),
            'notes'         => $this->notes,
            'created_at'    => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
