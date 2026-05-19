<?php

namespace App\Features\LoanOrders\Presenters;

use App\Features\LoanOrders\Models\LoanOrder;

class LoanOrderPresenter
{
    public static function forIndex(LoanOrder $o): array
    {
        return [
            'id'              => $o->id,
            'reference'       => $o->reference,
            'status'          => $o->status,
            'description'     => $o->description,
            'created_at'      => $o->created_at->format('Y-m-d'),
            'checked_out_at'  => $o->checked_out_at?->format('Y-m-d'),
            'returned_at'     => $o->returned_at?->format('Y-m-d'),
            'cancelled_at'    => $o->cancelled_at?->format('Y-m-d'),
            'manager'         => self::shapeUser($o->manager),
            'entity'          => self::shapeEntity($o),
            'equipments'      => self::shapeEquipments($o),
            // Flatten location fields for edit form pre-fill
            'parish_id'       => $o->location?->parish_id,
            'street'          => $o->location?->street_address,
            'reference_point' => $o->location?->landmark,
            'postal_code'     => $o->location?->postal_code,
            'latitude'        => $o->location?->latitude,
            'longitude'       => $o->location?->longitude,
        ];
    }

    public static function forDetail(LoanOrder $lo, array $only = []): array
    {
        $onlySpecified = !empty($only);

        $data = [
            'id'              => $lo->id,
            'reference'       => $lo->reference,
            'status'          => $lo->status,
            'description'     => $lo->description,
            'created_at'      => $lo->created_at->format('Y-m-d'),
            'checked_out_at'  => $lo->checked_out_at?->format('Y-m-d'),
            'returned_at'     => $lo->returned_at?->format('Y-m-d'),
            'cancelled_at'    => $lo->cancelled_at?->format('Y-m-d'),
            'notes_cancel'    => $lo->notes_cancel,
            'manager'         => self::shapeUser($lo->manager),
            'entity'          => self::shapeEntity($lo),
            'cancelled_by'    => $lo->cancelledBy ? self::shapeUser($lo->cancelledBy) : null,
            'location'        => self::shapeLocation($lo),
            'equipments'      => self::shapeEquipmentsDetail($lo),
        ];

        if (empty($only) || in_array('tasks', $only)) {
            $data['tasks'] = self::shapeTasks($lo);
        }

        return $data;
    }

    // ── Private shape helpers ──────────────────────────────────────────

    private static function shapeUser($user): ?array
    {
        if (!$user) return null;
        return [
            'id'   => $user->id,
            'name' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: 'N/A',
        ];
    }

    private static function shapeEntity(LoanOrder $o): ?array
    {
        if (!$o->entity) return null;
        return [
            'id'   => $o->entity->id,
            'name' => $o->entity->name,
        ];
    }

    private static function shapeLocation(LoanOrder $o): ?array
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

    private static function shapeEquipments(LoanOrder $o): array
    {
        if (!$o->relationLoaded('equipments') || $o->equipments->isEmpty()) return [];
        return $o->equipments->map(fn($eq) => [
            'equipment_id'   => $eq->id,
            'id'             => $eq->id,
            'name'           => $eq->name,
            'serial_number'  => $eq->serial_number,
            'status'         => $eq->status,
            'is_loanable'    => $eq->is_loanable,
            'description'    => $eq->description,
            'start_date'     => $eq->pivot?->start_date,
            'end_date'       => $eq->pivot?->end_date,
            'needs_operator' => (bool) ($eq->pivot?->needs_operator ?? false),
        ])->toArray();
    }

    private static function shapeEquipmentsDetail(LoanOrder $o): array
    {
        if (!$o->relationLoaded('equipments') || $o->equipments->isEmpty()) return [];
        return $o->equipments->map(fn($eq) => [
            'id'                => $eq->id,
            'name'              => $eq->name,
            'serial_number'     => $eq->serial_number,
            'status'            => $eq->status,
            'is_loanable'       => $eq->is_loanable,
            'description'       => $eq->description,
            'last_revision_date' => $eq->last_revision_date?->format('Y-m-d'),
            'next_revision_date' => $eq->next_revision_date?->format('Y-m-d'),
            'manager'           => $eq->manager ? self::shapeUser($eq->manager) : null,
            'start_date'        => $eq->pivot?->start_date,
            'end_date'          => $eq->pivot?->end_date,
            'needs_operator'    => $eq->pivot?->needs_operator ?? false,
        ])->toArray();
    }

    private static function shapeTasks(LoanOrder $lo): array
    {
        if (!$lo->relationLoaded('tasks') || $lo->tasks->isEmpty()) return [];
        return $lo->tasks->map(fn($task) => [
            'id'          => $task->id,
            'reference'   => $task->reference,
            'description' => $task->description,
            'status'      => $task->status,
            'manager'     => self::shapeUser($task->manager),
        ])->toArray();
    }
}
