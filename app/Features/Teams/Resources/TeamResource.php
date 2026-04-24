<?php

namespace App\Features\Teams\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sector_id' => $this->sector_id,
            'sector' => $this->whenLoaded('sector', function () {
                return ['id' => $this->sector->id, 'name' => $this->sector->name];
            }),
        ];
    }
}
