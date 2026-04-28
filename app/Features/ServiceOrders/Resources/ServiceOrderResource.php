<?php
namespace App\Features\ServiceOrders\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class ServiceOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'process' => $this->process,
            'priority' => $this->priority,
            'execution_date' => $this->execution_date ? $this->execution_date->format('Y-m-d') : null,
            'status' => $this->status,
            'created_at' => $this->created_at->toIso8601String(),
            'photo_url' => $this->photo_url,

            // Eager-loaded Relationships (Only included if they were loaded in the controller query!)
            'client' => $this->whenLoaded('client', function () {
                return [
                    'id' => $this->client->id,
                    'nif' => $this->client->nif,
                    'name' => $this->client->user->first_name . ' ' . $this->client->user->last_name,
                ];
            }),
            'manager' => $this->whenLoaded('manager', function () {
                return [
                    'id' => $this->manager->id,
                    'name' => $this->manager->first_name . ' ' . $this->manager->last_name,
                ];
            }),
            'location' => $this->whenLoaded('location'),
            'service_type' => $this->whenLoaded('serviceType'),

            // Nested Tasks (for detail views)
            // Assuming you'll create a TaskResource later, but we can return arrays for now
            'tasks' => $this->whenLoaded('tasks'),
        ];
    }
}