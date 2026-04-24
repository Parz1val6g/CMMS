<?php
namespace App\Features\Sectors\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class SectorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'head' => $this->whenLoaded('head', function () {
                return [
                    'id' => $this->head->id,
                    'name' => $this->head->first_name . ' ' . $this->head->last_name,
                ];
            }),
            // Return teams if requested
            'teams' => $this->whenLoaded('teams'),
        ];
    }
}