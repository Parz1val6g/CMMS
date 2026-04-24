<?php
namespace App\Features\MiniTasks\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class MiniTaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->created_at->toIso8601String(),

            'supervisor' => $this->whenLoaded('supervisor', function () {
                return [
                    'id' => $this->supervisor->id,
                    'name' => $this->supervisor->first_name . ' ' . $this->supervisor->last_name,
                ];
            }),

            // Return Workers if assigned
            'workers' => $this->whenLoaded('workers', function () {
                return $this->workers->map(function ($worker) {
                    return [
                        'id' => $worker->id,
                        'name' => $worker->user->first_name . ' ' . $worker->user->last_name,
                    ];
                });
            }),
            // Return Teams if assigned
            'teams' => $this->whenLoaded('teams', function () {
                return $this->teams->map(function ($team) {
                    return [
                        'id' => $team->id,
                        'name' => $team->name,
                    ];
                });
            }),
            // Return Planned Materials
            'materials' => $this->whenLoaded('materials', function () {
                return $this->materials->map(function ($material) {
                    return [
                        'id' => $material->id,
                        'name' => $material->name,
                        'planned_quantity' => $material->pivot->planned_quantity,
                        'unit' => $material->unit->abbreviation ?? '',
                    ];
                });
            }),
        ];
    }
}