<?php

namespace App\Features\Locations\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'postal_code' => $this->postal_code,
            'street_address' => $this->street_address,
            'landmark' => $this->landmark,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'parish' => $this->whenLoaded('parish', function () {
                return [
                    'id' => $this->parish->id, 
                    'name' => $this->parish->name,
                    'municipality' => $this->whenLoaded('parish.municipality', function() {
                         return ['id' => $this->parish->municipality->id, 'name' => $this->parish->municipality->name];
                    })
                ];
            }),
        ];
    }
}
