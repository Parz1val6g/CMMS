<?php

namespace App\Features\Tasks\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_order_id' => $this->service_order_id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->created_at->toIso8601String(),
            'manager' => $this->whenLoaded('manager', function () {
                return ['id' => $this->manager->id, 'name' => $this->manager->first_name . ' ' . $this->manager->last_name];
            }),
            'sectors' => $this->whenLoaded('sectors', function () {
                return $this->sectors->map(function ($sector) {
                    return ['id' => $sector->id, 'name' => $sector->name];
                });
            }),
            'mini_tasks' => $this->whenLoaded('miniTasks'),
        ];
    }
}
