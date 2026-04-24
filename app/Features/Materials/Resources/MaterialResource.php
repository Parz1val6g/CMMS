<?php

namespace App\Features\Materials\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'stock_quantity' => $this->stock_quantity,
            'unit' => $this->whenLoaded('unit', function () {
                return [
                    'id' => $this->unit->id, 
                    'name' => $this->unit->name, 
                    'abbreviation' => $this->unit->abbreviation
                ];
            }),
        ];
    }
}
