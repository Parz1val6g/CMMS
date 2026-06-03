<?php
namespace App\Features\ServiceOrders\Resources;
use App\Features\Clients\Resources\ClientLocationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class ServiceOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'process' => $this->process,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', fn() => [
                'id'   => $this->category->id,
                'name' => $this->category->name,
            ]),
            'start_date' => $this->start_date ? $this->start_date->format('Y-m-d') : null,
            'end_date' => $this->end_date ? $this->end_date->format('Y-m-d') : null,
            'status' => $this->status,
            'created_at' => $this->created_at->toIso8601String(),
            'photo_url' => $this->photo_url,

            'client_location_id' => $this->client_location_id,
            'client_location' => $this->whenLoaded('clientLocation', fn () =>
                new ClientLocationResource($this->clientLocation)
            ),

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
            'location' => $this->whenLoaded('location', fn() => $this->location),
            // Flatten location fields for form pre-fill
            'parish_id' => $this->whenLoaded('location', fn() => $this->location->parish_id),
            'street' => $this->whenLoaded('location', fn() => $this->location->street_address),
            'reference_point' => $this->whenLoaded('location', fn() => $this->location->landmark),
            'postal_code' => $this->whenLoaded('location', fn() => $this->location->postal_code),
            'latitude' => $this->whenLoaded('location', fn() => $this->location->latitude),
            'longitude' => $this->whenLoaded('location', fn() => $this->location->longitude),
            // Sectors with per-sector priority and service types
            'sectors' => $this->whenLoaded('sectors', function () {
                $serviceTypesBySector = $this->serviceTypesBySector();
                return $this->sectors->map(fn($s) => [
                    'id'           => $s->id,
                    'name'         => $s->name,
                    'service_types' => $serviceTypesBySector[$s->id] ?? [],
                ]);
            }),

            // Nested Tasks (for detail views)
            'tasks' => $this->whenLoaded('tasks', function () {
                return $this->tasks->map(fn($task) => [
                    'id'          => $task->id,
                    'reference'   => $task->reference,
                    'description' => $task->description,
                    'status'      => $task->status,
                    'manager'     => $task->relationLoaded('manager') && $task->manager ? [
                        'id'   => $task->manager->id,
                        'name' => $task->manager->first_name . ' ' . $task->manager->last_name,
                    ] : null,
                    'sectors'     => $task->relationLoaded('sectors') ? $task->sectors->map(fn($s) => [
                        'id'   => $s->id,
                        'name' => $s->name,
                    ]) : [],
                    'mini_tasks'  => $task->relationLoaded('miniTasks') ? $task->miniTasks->map(fn($mt) => [
                        'id'          => $mt->id,
                        'reference'   => $mt->reference,
                        'description' => $mt->description,
                        'status'      => $mt->status,
                        'work_logs'   => $mt->relationLoaded('workLogs') ? $mt->workLogs->map(fn($wl) => [
                            'id'               => $wl->id,
                            'reference'        => $wl->reference,
                            'started_at'       => $wl->started_at?->format('Y-m-d H:i'),
                            'completed_at'     => $wl->completed_at?->format('Y-m-d H:i'),
                            'duration_minutes' => $wl->duration_minutes,
                            'description'      => $wl->description,
                            'status'           => $wl->status,
                            'reviewed_at'      => $wl->reviewed_at?->format('Y-m-d H:i'),
                            'workers'          => $wl->relationLoaded('workers') ? $wl->workers->map(fn($w) => [
                                'id'   => $w->id,
                                'name' => $w->name,
                            ]) : [],
                            'materials'        => $wl->relationLoaded('materials') ? $wl->materials->map(fn($m) => [
                                'id'               => $m->id,
                                'name'             => $m->name,
                                'quantity_used'    => $m->pivot->quantity_used,
                                'unit_price_at_use' => $m->pivot->unit_price_at_use,
                            ]) : [],
                            'equipment'        => $wl->relationLoaded('equipment') ? $wl->equipment->map(fn($e) => [
                                'id'            => $e->id,
                                'name'          => $e->name,
                                'serial_number' => $e->serial_number,
                            ]) : [],
                            'reviewer'         => $wl->relationLoaded('reviewer') && $wl->reviewer ? [
                                'id'   => $wl->reviewer->id,
                                'name' => $wl->reviewer->first_name . ' ' . $wl->reviewer->last_name,
                            ] : null,
                        ]) : [],
                    ]) : [],
                ]);
            }),
        ];
    }
}