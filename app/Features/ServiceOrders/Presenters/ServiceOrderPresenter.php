<?php

namespace App\Features\ServiceOrders\Presenters;

use App\Features\ServiceOrders\Models\ServiceOrder;

/**
 * Centralizes data shaping for Inertia views.
 * Extracts the inline `through(fn)`, `map(fn)` patterns from ServiceOrderPageController.
 */
class ServiceOrderPresenter
{
    /**
     * Shape a single ServiceOrder for list index view.
     */
    public static function forIndex(ServiceOrder $o): array
    {
        return [
            'id'            => $o->id,
            'process'       => $o->process,
            'description'   => $o->description,
            'workflow_type' => $o->workflow_type,
            'client_id'     => $o->client_id,
            'location_id'   => $o->location_id,
            'service_type_id' => $o->service_type_id,
            'manager_id'    => $o->manager_id,
            'priority'      => $o->priority,
            'status'        => $o->status,
            'start_date'    => $o->start_date?->format('Y-m-d'),
            'end_date'      => $o->end_date?->format('Y-m-d'),
            'created_at'    => $o->created_at->format('Y-m-d'),
            'photo_url'     => $o->photo_url,
            'sectors'       => $o->sectors->map(fn($s) => [
                'id'   => $s->id,
                'name' => $s->name,
            ])->toArray(),
            'client'        => self::shapeClient($o),
            'manager'       => self::shapeUser($o->manager),
            'location'      => self::shapeLocation($o),
            'service_type'  => $o->serviceType ? ['name' => $o->serviceType->name] : null,
            // Flatten location fields for edit form pre-fill
            'parish_id'       => $o->location?->parish_id,
            'street'          => $o->location?->street_address,
            'reference_point' => $o->location?->landmark,
            'postal_code'     => $o->location?->postal_code,
            'latitude'        => $o->location?->latitude,
            'longitude'       => $o->location?->longitude,
        ];
    }

    /**
     * Shape a single ServiceOrder with full nested data for the show/detail view.
     */
    public static function forDetail(ServiceOrder $so, array $only = []): array
    {
        $onlySpecified = !empty($only);

        $data = [
            'id'            => $so->id,
            'process'       => $so->process,
            'description'   => $so->description,
            'workflow_type' => $so->workflow_type,
            'client_id'     => $so->client_id,
            'location_id'   => $so->location_id,
            'service_type_id' => $so->service_type_id,
            'manager_id'    => $so->manager_id,
            'priority'      => $so->priority,
            'status'        => $so->status,
            'start_date'    => $so->start_date?->format('Y-m-d'),
            'end_date'      => $so->end_date?->format('Y-m-d'),
            'created_at'    => $so->created_at->format('Y-m-d'),
            'photo_url'     => $so->photo_url,
            'sectors'       => $so->sectors->map(fn($s) => [
                'id'   => $s->id,
                'name' => $s->name,
            ])->toArray(),
            'client'        => self::shapeClient($so),
            'manager'       => self::shapeUser($so->manager),
            'location'      => self::shapeLocation($so),
            'service_type'  => $so->serviceType ? ['name' => $so->serviceType->name] : null,
            // Flatten location fields for edit form pre-fill
            'parish_id'       => $so->location?->parish_id,
            'street'          => $so->location?->street_address,
            'reference_point' => $so->location?->landmark,
            'postal_code'     => $so->location?->postal_code,
            'latitude'        => $so->location?->latitude,
            'longitude'       => $so->location?->longitude,
        ];

        if (empty($only) || in_array('tasks', $only)) {
            $data['tasks'] = self::shapeTasks($so);
        }

        return $data;
    }

    // ── Private shape helpers ──────────────────────────────────────────

    private static function shapeClient(ServiceOrder $o): ?array
    {
        if (!$o->client) return null;
        return [
            'id'   => $o->client->id,
            'name' => trim(($o->client->user?->first_name ?? '') . ' ' . ($o->client->user?->last_name ?? '')) ?: 'N/A',
        ];
    }

    private static function shapeUser($user): ?array
    {
        if (!$user) return null;
        return [
            'id'   => $user->id,
            'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: 'N/A',
        ];
    }

    private static function shapeLocation(ServiceOrder $o): ?array
    {
        if (!$o->location) return null;
        return [
            'id'        => $o->location->id,
            'parish'    => $o->location->parish ? ['name' => $o->location->parish->name] : null,
            'street'    => $o->location->street_address,
            'landmark'  => $o->location->landmark,
            'latitude'  => $o->location->latitude,
            'longitude' => $o->location->longitude,
        ];
    }

    private static function shapeTasks(ServiceOrder $so): array
    {
        return $so->tasks->map(function ($task) {
            return [
                'id'          => $task->id,
                'reference'   => $task->reference,
                'description' => $task->description,
                'status'      => $task->status,
                'manager'     => self::shapeUser($task->manager),
                'sectors'     => $task->sectors->map(fn($s) => [
                    'id'   => $s->id,
                    'name' => $s->name,
                ])->toArray(),
                'mini_tasks'  => $task->miniTasks->map(function ($mt) {
                    return [
                        'id'          => $mt->id,
                        'reference'   => $mt->reference,
                        'description' => $mt->description,
                        'status'      => $mt->status,
                        'work_logs'   => $mt->workLogs->map(function ($wl) {
                            return [
                                'id'               => $wl->id,
                                'started_at'       => $wl->started_at?->format('Y-m-d H:i'),
                                'completed_at'     => $wl->completed_at?->format('Y-m-d H:i'),
                                'duration_minutes' => $wl->duration_minutes,
                                'description'      => $wl->description,
                                'status'           => $wl->status,
                                'reviewed_at'      => $wl->reviewed_at?->format('Y-m-d H:i'),
                                'workers'          => $wl->workers->map(fn($w) => [
                                    'id'   => $w->id,
                                    'name' => $w->name,
                                ])->toArray(),
                                'materials'        => $wl->materials->map(fn($m) => [
                                    'id'               => $m->id,
                                    'name'             => $m->name,
                                    'quantity_used'    => $m->pivot->quantity_used,
                                    'unit_price_at_use' => $m->pivot->unit_price_at_use,
                                ])->toArray(),
                                'equipment'        => $wl->equipment->map(fn($e) => [
                                    'id'            => $e->id,
                                    'name'          => $e->name,
                                    'serial_number' => $e->serial_number,
                                ])->toArray(),
                                'reviewer'         => $wl->reviewer ? self::shapeUser($wl->reviewer) : null,
                            ];
                        })->toArray(),
                    ];
                })->toArray(),
            ];
        })->toArray();
    }
}
