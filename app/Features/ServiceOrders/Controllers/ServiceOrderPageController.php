<?php

namespace App\Features\ServiceOrders\Controllers;

use App\Core\Enums\Priority;
use App\Core\Enums\ServiceOrderStatus;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\ServiceOrders\Schemas\ServiceOrderFormSchema;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use App\Features\Tasks\Models\Task;
use App\Features\MiniTasks\Models\MiniTask;
use App\Features\WorkLogs\Models\WorkLog;

class ServiceOrderPageController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', ServiceOrder::class);

        $orders = ServiceOrder::with(['client.user', 'manager', 'location.parish', 'serviceType'])
            ->latest()
            ->paginate(15)
            ->through(fn($o) => [
                'id' => $o->id,
                'process' => $o->process,
                'description' => $o->description,
                'workflow_type' => $o->workflow_type,
                'equipment_id' => $o->equipment_id,
                'client_id' => $o->client_id,
                'location_id' => $o->location_id,
                'service_type_id' => $o->service_type_id,
                'manager_id' => $o->manager_id,
                'priority' => $o->priority,
                'status' => $o->status,
                'execution_date' => $o->execution_date?->format('Y-m-d'),
                'created_at' => $o->created_at->format('Y-m-d'),
                'photo_url' => $o->photo_url,
                'client' => $o->client ? [
                    'id' => $o->client->id,
                    'name' => trim(($o->client->user?->first_name ?? '') . ' ' . ($o->client->user?->last_name ?? '')) ?: 'N/A',
                ] : null,
                'manager' => $o->manager ? [
                    'id' => $o->manager->id,
                    'name' => trim(($o->manager->first_name ?? '') . ' ' . ($o->manager->last_name ?? '')) ?: 'N/A',
                ] : null,
                'location' => $o->location ? [
                    'id' => $o->location->id,
                    'parish' => $o->location->parish ? ['name' => $o->location->parish->name] : null,
                    'street' => $o->location->street_address,
                    'landmark' => $o->location->landmark,
                    'latitude' => $o->location->latitude,
                    'longitude' => $o->location->longitude,
                ] : null,
                'service_type' => $o->serviceType ? ['name' => $o->serviceType->name] : null,
            ]);

        $createSchema = ServiceOrderFormSchema::create();
        $updateSchema = ServiceOrderFormSchema::update();

        return Inertia::render('ServiceOrders/Pages/Index', [
            'service_orders' => $orders,
            'columns' => [
                ['key' => 'process', 'label' => 'Process', 'sortable' => true],
                ['key' => 'description', 'label' => 'Description'],
                ['key' => 'client.name', 'label' => 'Client'],
                ['key' => 'priority', 'label' => 'Priority', 'sortable' => true],
                ['key' => 'status', 'label' => 'Status', 'sortable' => true],
                ['key' => 'created_at', 'label' => 'Created', 'sortable' => true],
            ],
            'formSchema' => $updateSchema->toArray(),
            'createFormSchema' => $createSchema->toArray(),
            'routes' => [
                'index' => url('/api/service-orders'),
                'store' => url('/api/service-orders'),
                'update' => url('/api/service-orders/:id'),
                'destroy' => url('/api/service-orders/:id'),
                'show' => url('/api/service-orders/:id'),
            ],
            'filterSchema' => [
                ['key' => 'search', 'label' => 'Search', 'type' => 'text', 'placeholder' => 'Search process...'],
                [
                    'key' => 'status',
                    'label' => 'Status',
                    'type' => 'select',
                    'options' => ServiceOrderStatus::options()
                ],
                [
                    'key' => 'priority',
                    'label' => 'Priority',
                    'type' => 'select',
                    'options' => Priority::options()
                ],
            ],
        ]);
    }

    public function show(Request $request, $id)
    {
        $serviceOrder = ServiceOrder::find($id);

        if (!$serviceOrder) {
            abort(404);
        }

        Gate::authorize('view', $serviceOrder);

        // Determine which relationships to eager-load based on ?only= parameter
        $only = array_filter(explode(',', $request->query('only', '')));
        $onlySpecified = !empty($only);

        // Build base query with always-needed relationships
        $query = ServiceOrder::with([
            'client.user',
            'manager',
            'location.parish',
            'serviceType',
            'equipment.manager'
        ]);

        // Conditional lazy-loading based on ?only= parameter
        if (empty($only) || in_array('tasks', $only)) {
            $query->with([
                'tasks' => function ($q) {
                    $q->with([
                        'manager',
                        'sectors',
                        'miniTasks' => function ($mt) {
                            $mt->with([
                                'sectors',
                                'workLogs' => function ($wl) {
                                    $wl->with(['workers', 'materials', 'equipment', 'reviewer']);
                                }
                            ]);
                        }
                    ]);
                }
            ]);
        }

        if (empty($only) || in_array('equipment', $only)) {
            // Equipment is already loaded in base query, no additional loading needed
        }

        if (empty($only) || in_array('timeline', $only)) {
            // Timeline uses WorkLogs with all details
            // Already included in tasks.miniTasks.workLogs, but we can add direct access if needed
        }

        $so = $query->findOrFail($id);

        // Transform ServiceOrder with nested data
        $data = [
            'id' => $so->id,
            'process' => $so->process,
            'description' => $so->description,
            'workflow_type' => $so->workflow_type,
            'client_id' => $so->client_id,
            'location_id' => $so->location_id,
            'service_type_id' => $so->service_type_id,
            'equipment_id' => $so->equipment_id,
            'manager_id' => $so->manager_id,
            'priority' => $so->priority,
            'status' => $so->status,
            'execution_date' => $so->execution_date?->format('Y-m-d'),
            'created_at' => $so->created_at->format('Y-m-d'),
            'photo_url' => $so->photo_url,
            'client' => $so->client ? [
                'id' => $so->client->id,
                'name' => trim(($so->client->user?->first_name ?? '') . ' ' . ($so->client->user?->last_name ?? '')) ?: 'N/A',
            ] : null,
            'manager' => $so->manager ? [
                'id' => $so->manager->id,
                'name' => trim(($so->manager->first_name ?? '') . ' ' . ($so->manager->last_name ?? '')) ?: 'N/A',
            ] : null,
            'location' => $so->location ? [
                'id' => $so->location->id,
                'parish' => $so->location->parish ? ['name' => $so->location->parish->name] : null,
                'street' => $so->location->street_address,
                'landmark' => $so->location->landmark,
                'latitude' => $so->location->latitude,
                'longitude' => $so->location->longitude,
            ] : null,
            'service_type' => $so->serviceType ? ['name' => $so->serviceType->name] : null,
            'equipment' => $so->equipment ? [
                'id' => $so->equipment->id,
                'name' => $so->equipment->name,
                'serial_number' => $so->equipment->serial_number,
                'status' => $so->equipment->status,
                'is_loanable' => $so->equipment->is_loanable,
                'manager' => $so->equipment->manager ? [
                    'id' => $so->equipment->manager->id,
                    'name' => trim(($so->equipment->manager->first_name ?? '') . ' ' . ($so->equipment->manager->last_name ?? '')) ?: 'N/A',
                ] : null,
            ] : null,
        ];

        // Include tasks only if explicitly requested or on full load
        if (empty($only) || in_array('tasks', $only)) {
            $data['tasks'] = $so->tasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'name' => $task->name,
                    'description' => $task->description,
                    'status' => $task->status,
                    'manager' => $task->manager ? [
                        'id' => $task->manager->id,
                        'name' => trim(($task->manager->first_name ?? '') . ' ' . ($task->manager->last_name ?? '')) ?: 'N/A',
                    ] : null,
                    'sectors' => $task->sectors->map(fn($s) => [
                        'id' => $s->id,
                        'name' => $s->name,
                    ])->toArray(),
                    'mini_tasks' => $task->miniTasks->map(function ($mt) {
                        return [
                            'id' => $mt->id,
                            'name' => $mt->name,
                            'description' => $mt->description,
                            'status' => $mt->status,
                            'sectors' => $mt->sectors->map(fn($s) => [
                                'id' => $s->id,
                                'name' => $s->name,
                            ])->toArray(),
                            'work_logs' => $mt->workLogs->map(function ($wl) {
                                return [
                                    'id' => $wl->id,
                                    'started_at' => $wl->started_at?->format('Y-m-d H:i'),
                                    'completed_at' => $wl->completed_at?->format('Y-m-d H:i'),
                                    'duration_minutes' => $wl->duration_minutes,
                                    'description' => $wl->description,
                                    'status' => $wl->status,
                                    'reviewed_at' => $wl->reviewed_at?->format('Y-m-d H:i'),
                                    'workers' => $wl->workers->map(fn($w) => [
                                        'id' => $w->id,
                                        'name' => $w->name,
                                    ])->toArray(),
                                    'materials' => $wl->materials->map(fn($m) => [
                                        'id' => $m->id,
                                        'name' => $m->name,
                                        'quantity_used' => $m->pivot->quantity_used,
                                        'unit_price_at_use' => $m->pivot->unit_price_at_use,
                                    ])->toArray(),
                                    'equipment' => $wl->equipment->map(fn($e) => [
                                        'id' => $e->id,
                                        'name' => $e->name,
                                        'serial_number' => $e->serial_number,
                                    ])->toArray(),
                                    'reviewer' => $wl->reviewer ? [
                                        'id' => $wl->reviewer->id,
                                        'name' => trim(($wl->reviewer->first_name ?? '') . ' ' . ($wl->reviewer->last_name ?? '')) ?: 'N/A',
                                    ] : null,
                                ];
                            })->toArray(),
                        ];
                    })->toArray(),
                ];
            })->toArray();
        }

        $updateSchema = ServiceOrderFormSchema::update();

        return Inertia::render('ServiceOrders/Pages/Show', [
            'service_order' => $data,
            'formSchema' => $updateSchema->toArray(),
            'routes' => [
                'index' => url('/service-orders'),
                'update' => url('/api/service-orders/:id'),
            ],
        ]);
    }
}
