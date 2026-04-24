<?php

namespace App\Features\Clients\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nif' => $this->nif,
            'name' => $this->whenLoaded('user', function () {
                return $this->user->first_name . ' ' . $this->user->last_name;
            }),
            'email' => $this->whenLoaded('user', function () {
                return $this->user->email;
            }),
            'phone' => $this->whenLoaded('user', function () {
                return $this->user->phone;
            }),
        ];
    }
}
