<?php

namespace App\Features\Workers\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'team_id' => $this->team_id,
            // For form pre-fill: separate first/last names and phone
            'first_name' => $this->whenLoaded('user', fn() => $this->user->first_name),
            'last_name' => $this->whenLoaded('user', fn() => $this->user->last_name),
            'phone' => $this->whenLoaded('user', fn() => $this->user->phone),
            // For display: combined name
            'name' => $this->whenLoaded('user', fn() => $this->user->first_name . ' ' . $this->user->last_name),
            'email' => $this->whenLoaded('user', fn() => $this->user->email),
            'team' => $this->whenLoaded('team', fn() => ['id' => $this->team->id, 'name' => $this->team->name]),
        ];
    }
}
