<?php

namespace App\Features\ServiceOrders\Presenters;

use App\Features\ServiceOrders\Models\ServiceOrder;
use Illuminate\Support\Collection;

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
            'equipment_id'  => $o->equipment_id,
            'client_id'     => $o->client_id,
            'location_id'   => $o->location_id,
            'service_type_id' => $o->service_type_id,
            'manager_id'    => $o->manager_id,
            'priority'      => $o->priority,
            'status'        => $o->status,
            'execution_date' => $o->execution_date?->format('Y-m-d'),
            'created_at'    => $o->created_at->format('Y-m-d'),
            'photo_url'     => $o->photo_url,
            'client'        => self::shapeClient($o),
            'manager'       => self::shapeUser($o->manager),
            'location'      => self::shapeLocation($o),
            'service_type'  => $o->serviceType ? ['name' => $o->serviceType->name] : null,
            'equipment'     => self::shapeEquipment($o),
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
            'equipment_id'  => $so->equipment_id,
            'manager_id'    => $so->manager_id,
            'priority'      => $so->priority,
            'status'        => $so->status,
            'execution_date' => $so->execution_date?->format('Y-m-d'),
            'created_at'    => $so->created_at->format('Y-m-d'),
            'photo_url'     => $so->photo_url,
            'client'        => self::shapeClient($so),
            'manager'       => self::shapeUser($so->manager),
            'location'      => self::shapeLocation($so),
            'service_type'  => $so->serviceType ? ['name' => $so->serviceType->name] : null,
            'equipment'     => self::shapeEquipmentDetail($so),
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

    private static function shapeEquipment(ServiceOrder $o): ?array
    {
        if (!$o->equipment) return null;
        return [
            'id'                => $o->equipment->id,
            'name'              => $o->equipment->name,
            'serial_number'     => $o->equipment->serial_number,
            'status'            => $o->equipment->status,
            'is_loanable'       => $o->equipment->is_loanable,
            'description'       => $o->equipment->description,
            'last_revision_date' => $o->equipment->last_revision_date?->format('Y-m-d'),
            'next_revision_date' => $o->equipment->next_revision_date?->format('Y-m-d'),
        ];
    }

    private static function shapeEquipmentDetail(ServiceOrder $o): ?array
    {
        $eq = self::shapeEquipment($o);
        if (!$eq || !$o->equipment->manager) return $eq;

        $eq['manager'] = self::shapeUser($o->equipment->manager);
        return $eq;
    }

    private static function shapeTasks(ServiceOrder $so): array
    {
        return $so->tasks->map(function ($task) {
            return [
                'id'          => $task->id,
                'name'        => $task->name,
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
                        'name'        => $mt->name,
                        'description' => $mt->description,
                        'status'      => $mt->status,
                        'sectors'     => $mt->sectors->map(fn($s) => [
                            'id'   => $s->id,
                            'name' => $s->name,
                        ])->toArray(),
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
