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
            'name' => $this->whenLoaded('user', function () {
                return $this->user->first_name . ' ' . $this->user->last_name;
            }),
            'email' => $this->whenLoaded('user', function () {
                return $this->user->email;
            }),
            'team' => $this->whenLoaded('team', function () {
                return ['id' => $this->team->id, 'name' => $this->team->name];
            }),
        ];
    }
}
