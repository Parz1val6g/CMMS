<?php
namespace App\Features\WorkLogs\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class WorkLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'description' => $this->description,
            'started_at' => $this->started_at ? $this->started_at->toIso8601String() : null,
            'completed_at' => $this->completed_at ? $this->completed_at->toIso8601String() : null,
            'duration_minutes' => $this->duration_minutes, // Handled automatically by your MySQL DB!
            'created_at' => $this->created_at->toIso8601String(),
            'status' => $this->status,

            // Parent MiniTask reference
            'mini_task' => $this->whenLoaded('miniTask', function () {
                return [
                    'id' => $this->miniTask->id,
                    'reference' => $this->miniTask->reference,
                ];
            }),

            // Nested Workers
            'workers' => $this->whenLoaded('workers', function () {
                return $this->workers->map(function ($worker) {
                    return [
                        'id' => $worker->id,
                        'name' => $worker->user->first_name . ' ' . $worker->user->last_name,
                    ];
                });
            }),
            // Nested Materials used
            'materials' => $this->whenLoaded('materials', function () {
                return $this->materials->map(function ($material) {
                    return [
                        'id' => $material->id,
                        'name' => $material->name,
                        'quantity_used' => $material->pivot->quantity_used,
                        'unit_price_at_use' => $material->pivot->unit_price_at_use,
                    ];
                });
            }),
            // Equipment used
            'equipment' => $this->whenLoaded('equipment', function () {
                return $this->equipment->map(function ($eq) {
                    return [
                        'id'            => $eq->id,
                        'name'          => $eq->name,
                        'brand'         => $eq->brand,
                        'model'         => $eq->model,
                        'serial_number' => $eq->serial_number,
                    ];
                });
            }),
        ];
    }
}