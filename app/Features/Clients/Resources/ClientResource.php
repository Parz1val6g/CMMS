<?php

namespace App\Features\Clients\Resources;

use App\Features\Clients\Resources\ClientLocationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'nif'        => $this->nif,
            'first_name' => $this->whenLoaded('user', fn() => $this->user->first_name),
            'last_name'  => $this->whenLoaded('user', fn() => $this->user->last_name),
            'name'       => $this->whenLoaded('user', fn() => $this->user->first_name . ' ' . $this->user->last_name),
            'email'      => $this->whenLoaded('user', fn() => $this->user->email),
            'phone'      => $this->whenLoaded('user', fn() => $this->user->phone),
            'locations'  => $this->whenLoaded('clientLocations', fn() =>
                ClientLocationResource::collection($this->clientLocations)
            ),
        ];
    }
}
