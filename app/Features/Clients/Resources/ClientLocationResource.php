<?php

namespace App\Features\Clients\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientLocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'is_primary' => $this->is_primary,
            'location'   => $this->whenLoaded('location', fn () => [
                'id'             => $this->location->id,
                'parish_id'      => $this->location->parish_id,
                'postal_code'    => $this->location->postal_code,
                'street_address' => $this->location->street_address,
                'landmark'       => $this->location->landmark,
                'latitude'       => $this->location->latitude,
                'longitude'      => $this->location->longitude,
                'parish'         => $this->location->relationLoaded('parish') && $this->location->parish
                    ? [
                        'id'   => $this->location->parish->id,
                        'name' => $this->location->parish->name,
                    ]
                    : null,
            ]),
        ];
    }
}
