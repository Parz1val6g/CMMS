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
            'reference' => $this->reference,
            'task_id' => $this->task_id,
            'description' => $this->description,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'status' => $this->status,
            'created_at' => $this->created_at->toIso8601String(),

            // Parent Task reference
            'task' => $this->whenLoaded('task', function () {
                return [
                    'id' => $this->task->id,
                    'reference' => $this->task->reference,
                    'description' => $this->task->description,
                ];
            }),

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
            // Return Planned Equipment
            'equipment' => $this->whenLoaded('equipment', fn() => $this->equipment->map(fn($e) => [
                'id'   => $e->id,
                'name' => $e->name,
            ])),
        ];
    }
}