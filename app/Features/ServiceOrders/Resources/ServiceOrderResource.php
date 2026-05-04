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
            'description' => $this->description,
            'priority' => $this->priority,
            'execution_date' => $this->execution_date ? $this->execution_date->format('Y-m-d') : null,
            'status' => $this->status,
            'workflow_type' => $this->workflow_type,
            'equipment_id' => $this->equipment_id,
            'created_at' => $this->created_at->toIso8601String(),
            'photo_url' => $this->photo_url,

            // Eager-loaded Relationships (Only included if they were loaded in the controller query!)
            'client' => $this->whenLoaded('client', function () {
                $data = [
                    'id' => $this->client->id,
                    'name' => $this->client->user->first_name . ' ' . $this->client->user->last_name,
                ];
                if (auth()->user()?->can('viewNif', $this->client)) {
                    $data['nif'] = $this->client->nif;
                }
                return $data;
            }),
            'manager' => $this->whenLoaded('manager', function () {
                return [
                    'id' => $this->manager->id,
                    'name' => $this->manager->first_name . ' ' . $this->manager->last_name,
                ];
            }),
            'location' => $this->whenLoaded('location'),
            'service_type' => $this->whenLoaded('serviceType'),

            // Primary loaned equipment (for loan workflow)
            'equipment' => $this->whenLoaded('equipment', function () {
                return [
                    'id' => $this->equipment->id,
                    'name' => $this->equipment->name,
                    'serial_number' => $this->equipment->serial_number,
                    'status' => $this->equipment->status,
                    'is_loanable' => $this->equipment->is_loanable,
                    'description' => $this->equipment->description,
                    'last_revision_date' => $this->equipment->last_revision_date?->format('Y-m-d'),
                    'next_revision_date' => $this->equipment->next_revision_date?->format('Y-m-d'),
                ];
            }),

            // Nested Tasks (for detail views)
            'tasks' => $this->whenLoaded('tasks'),
        ];
    }
}