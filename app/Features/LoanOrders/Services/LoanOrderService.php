<?php

namespace App\Features\LoanOrders\Services;

use App\Core\Enums\EquipmentStatus;
use App\Core\Enums\LoanOrderStatus;
use App\Core\Enums\TaskStatus;
use App\Core\Helpers\InputSanitizer;
use App\Core\Services\TransactionHandler;
use App\Exceptions\EquipmentUnavailableException;
use App\Features\Equipments\Models\Equipment;
use App\Features\LoanOrders\Models\LoanOrder;
use App\Features\Locations\Models\Location;
use App\Features\Tasks\Models\Task;
use InvalidArgumentException;

class LoanOrderService
{
    public function __construct(
        private TransactionHandler $transactions,
        private AvailabilityService $availability
    ) {}

    public function create(array $data, string $managerId): LoanOrder
    {
        return $this->transactions->execute(function () use ($data, $managerId) {
            // Support both flat equipment_ids and nested equipments array
            $equipmentItems = $data['equipments'] ?? [];
            $equipmentIds   = $data['equipment_ids'] ?? array_column($equipmentItems, 'equipment_id');

            if (empty($equipmentIds)) {
                throw new InvalidArgumentException(__('messages.loan_orders.no_equipment'));
            }

            // Top-level scheduling fields apply to all equipments when no per-equipment array is provided
            $topStart    = $data['start_date'] ?? null;
            $topEnd      = $data['end_date'] ?? null;
            $topOperator = (bool) ($data['needs_operator'] ?? false);

            if (empty($equipmentItems) && ($topStart || $topEnd || array_key_exists('needs_operator', $data))) {
                $equipmentItems = array_map(fn($id) => [
                    'equipment_id'   => $id,
                    'start_date'     => $topStart,
                    'end_date'       => $topEnd,
                    'needs_operator' => $topOperator,
                ], $equipmentIds);
            }

            // Pessimistic lock — prevents concurrent double-booking
            $equipments = Equipment::whereIn('id', $equipmentIds)->lockForUpdate()->get();

            foreach ($equipments as $equipment) {
                if (!$equipment->isAvailableForLoan()) {
                    throw new EquipmentUnavailableException(
                        __('messages.loan_orders.equipment_unavailable', ['ref' => $equipment->reference])
                    );
                }
            }

            // Validate date-based availability when dates are provided
            if (!empty($equipmentItems)) {
                foreach ($equipmentItems as $item) {
                    if (!empty($item['start_date']) && !empty($item['end_date'])) {
                        if (!$this->availability->isAvailable($item['equipment_id'], $item['start_date'], $item['end_date'])) {
                            $eq = $equipments->firstWhere('id', $item['equipment_id']);
                            throw new EquipmentUnavailableException(
                                __('messages.loan_orders.equipment_unavailable', ['ref' => $eq?->reference ?? $item['equipment_id']])
                            );
                        }
                    }
                }
            }

            // Create Location if parish_id provided
            $locationId = null;
            if (!empty($data['parish_id'])) {
                $location = Location::create([
                    'parish_id'      => $data['parish_id'],
                    'postal_code'    => $data['postal_code'] ?? '',
                    'street_address' => InputSanitizer::sanitize($data['street'] ?? ''),
                    'landmark'       => isset($data['reference_point'])
                        ? InputSanitizer::sanitize($data['reference_point'])
                        : '',
                    'latitude'       => $data['latitude'] ?? null,
                    'longitude'      => $data['longitude'] ?? null,
                ]);
                $locationId = $location->id;
            }

            $loanOrder = LoanOrder::create([
                'client_id'            => $data['client_id'] ?? null,
                'entity_id'            => $data['entity_id'] ?? null,
                'manager_id'           => $managerId,
                'location_id'          => $locationId,
                'delivery_location_id' => $data['delivery_location_id'] ?? null,
                'status'               => LoanOrderStatus::PENDING->value,
                'description'          => $data['description'] ?? null,
            ]);

            foreach ($equipments as $equipment) {
                $equipment->markAsInUse();
            }

            // Sync with pivot dates if provided, otherwise plain sync
            if (!empty($equipmentItems)) {
                $syncData = [];
                foreach ($equipmentItems as $item) {
                    $syncData[$item['equipment_id']] = [
                        'start_date'     => $item['start_date'] ?? null,
                        'end_date'       => $item['end_date'] ?? null,
                        'needs_operator' => $item['needs_operator'] ?? false,
                    ];
                }
                $loanOrder->equipments()->sync($syncData);
            } else {
                $loanOrder->equipments()->sync($equipmentIds);
            }

            // Auto-create checkout task
            Task::create([
                'taskable_id'   => $loanOrder->id,
                'taskable_type' => LoanOrder::class,
                'manager_id'    => $managerId,
                'description'   => __('messages.task_names.equipment_loan'),
                'status'        => TaskStatus::PENDING->value,
            ]);

            return $loanOrder->load(['equipments', 'tasks', 'location']);
        });
    }

    public function update(LoanOrder $loanOrder, array $data): LoanOrder
    {
        return $this->transactions->execute(function () use ($loanOrder, $data) {
            $updatable = array_intersect_key($data, array_flip([
                'client_id', 'entity_id', 'manager_id', 'status', 'description',
            ]));

            // Update or create location when location fields are present
            if (array_key_exists('parish_id', $data) || array_key_exists('street', $data)
                || array_key_exists('reference_point', $data) || array_key_exists('postal_code', $data)
                || array_key_exists('latitude', $data) || array_key_exists('longitude', $data)) {
                $locationPayload = [
                    'parish_id'      => $data['parish_id'] ?? optional($loanOrder->location)->parish_id,
                    'postal_code'    => $data['postal_code'] ?? optional($loanOrder->location)->postal_code ?? '',
                    'street_address' => InputSanitizer::sanitize($data['street'] ?? optional($loanOrder->location)->street_address ?? ''),
                    'landmark'       => InputSanitizer::sanitize($data['reference_point'] ?? optional($loanOrder->location)->landmark ?? ''),
                    'latitude'       => $data['latitude'] ?? optional($loanOrder->location)->latitude,
                    'longitude'      => $data['longitude'] ?? optional($loanOrder->location)->longitude,
                ];

                if ($loanOrder->location_id) {
                    $loanOrder->location->update($locationPayload);
                } elseif (!empty($locationPayload['parish_id'])) {
                    $location = Location::create($locationPayload);
                    $updatable['location_id'] = $location->id;
                }
            }

            if (!empty($updatable)) {
                $loanOrder->update($updatable);
            }

            $equipmentItems = $data['equipments'] ?? [];
            $equipmentIds   = $data['equipment_ids'] ?? array_column($equipmentItems, 'equipment_id');

            $topStart    = $data['start_date'] ?? null;
            $topEnd      = $data['end_date'] ?? null;
            $topOperator = array_key_exists('needs_operator', $data) ? (bool) $data['needs_operator'] : null;

            if (!empty($equipmentIds) && empty($equipmentItems) && ($topStart || $topEnd || $topOperator !== null)) {
                $equipmentItems = array_map(fn($id) => [
                    'equipment_id'   => $id,
                    'start_date'     => $topStart,
                    'end_date'       => $topEnd,
                    'needs_operator' => $topOperator ?? false,
                ], $equipmentIds);
            }

            if (!empty($equipmentItems)) {
                $syncData = [];
                foreach ($equipmentItems as $item) {
                    $syncData[$item['equipment_id']] = [
                        'start_date'     => $item['start_date'] ?? null,
                        'end_date'       => $item['end_date'] ?? null,
                        'needs_operator' => $item['needs_operator'] ?? false,
                    ];
                }
                $loanOrder->equipments()->sync($syncData);
            } elseif (!empty($equipmentIds)) {
                $loanOrder->equipments()->sync($equipmentIds);
            }

            return $loanOrder->fresh(['equipments', 'tasks', 'location']);
        });
    }

    public function approve(LoanOrder $loanOrder, string $approvedByUserId): LoanOrder
    {
        return $this->transactions->execute(function () use ($loanOrder, $approvedByUserId) {
            // Pessimistic lock
            $loanOrder = LoanOrder::lockForUpdate()->findOrFail($loanOrder->id);

            if (!$loanOrder->status->isPending()) {
                throw new InvalidArgumentException(
                    __('messages.loan_orders.must_be_pending_to_approve')
                );
            }

            // Re-verify availability for each equipment with dates
            $loanOrder->loadMissing('equipments');
            foreach ($loanOrder->equipments as $equipment) {
                $startDate = $equipment->pivot->start_date;
                $endDate   = $equipment->pivot->end_date;
                if ($startDate && $endDate) {
                    if (!$this->availability->isAvailable($equipment->id, $startDate, $endDate, $loanOrder->id)) {
                        throw new EquipmentUnavailableException(
                            __('messages.loan_orders.equipment_unavailable', ['ref' => $equipment->reference])
                        );
                    }
                }
            }

            $loanOrder->update([
                'status'      => LoanOrderStatus::APPROVED->value,
                'approved_by' => $approvedByUserId,
                'approved_at' => now(),
            ]);

            return $loanOrder->fresh();
        });
    }

    public function checkout(LoanOrder $loanOrder): LoanOrder
    {
        return $this->transactions->execute(function () use ($loanOrder) {
            if ($loanOrder->status !== LoanOrderStatus::APPROVED) {
                throw new InvalidArgumentException(
                    __('messages.loan_orders.must_be_approved_to_checkout')
                );
            }

            $loanOrder->update([
                'status'         => LoanOrderStatus::CHECKED_OUT->value,
                'checked_out_at' => now(),
            ]);

            // Ensure equipment is marked IN_USE
            $loanOrder->loadMissing('equipments');
            foreach ($loanOrder->equipments as $equipment) {
                $equipment->markAsInUse();
            }

            return $loanOrder->fresh();
        });
    }

    public function complete(LoanOrder $loanOrder): LoanOrder
    {
        return $this->transactions->execute(function () use ($loanOrder) {
            if ($loanOrder->status !== LoanOrderStatus::CHECKED_OUT) {
                throw new InvalidArgumentException(__('messages.loan_orders.must_be_checked_out'));
            }

            $loanOrder->update([
                'status'      => LoanOrderStatus::RETURNED->value,
                'returned_at' => now(),
            ]);

            $this->releaseEquipment($loanOrder);

            return $loanOrder->fresh();
        });
    }

    public function cancel(LoanOrder $loanOrder, string $userId): LoanOrder
    {
        return $this->transactions->execute(function () use ($loanOrder, $userId) {
            // Idempotent — already cancelled
            if ($loanOrder->status === LoanOrderStatus::CANCELLED) {
                return $loanOrder;
            }

            // Guard: only PENDING is cancellable
            if (!$loanOrder->status->isPending()) {
                throw new InvalidArgumentException(
                    __('messages.loan_orders.cannot_cancel_status', ['status' => $loanOrder->status->value])
                );
            }

            $loanOrder->update([
                'status'       => LoanOrderStatus::CANCELLED->value,
                'cancelled_at' => now(),
                'cancelled_by' => $userId,
            ]);

            $this->releaseEquipment($loanOrder);

            return $loanOrder->fresh();
        });
    }

    public function initiateReturn(LoanOrder $loanOrder): Task
    {
        return $this->transactions->execute(function () use ($loanOrder) {
            // Guard: must be checked out
            if ($loanOrder->status !== LoanOrderStatus::CHECKED_OUT) {
                throw new InvalidArgumentException(
                    __('messages.loan_orders.must_be_checked_out')
                );
            }

            // Guard: no existing return task
            if ($loanOrder->tasks()->where('description', __('messages.task_names.equipment_return'))->exists()) {
                throw new InvalidArgumentException(
                    __('messages.loan_orders.return_task_exists')
                );
            }

            // Guard: checkout task must be completed
            $checkoutTask = $loanOrder->tasks()
                ->where('description', __('messages.task_names.equipment_loan'))
                ->first();

            if (!$checkoutTask || $checkoutTask->status !== TaskStatus::COMPLETED) {
                throw new InvalidArgumentException(
                    __('messages.loan_orders.checkout_must_complete')
                );
            }

            $task = Task::create([
                'taskable_id'   => $loanOrder->id,
                'taskable_type' => LoanOrder::class,
                'manager_id'    => $loanOrder->manager_id,
                'description'   => __('messages.task_names.equipment_return'),
                'status'        => TaskStatus::PENDING->value,
            ]);

            return $task->load('manager');
        });
    }

    public function delete(LoanOrder $loanOrder): void
    {
        $this->transactions->execute(function () use ($loanOrder) {
            if (!in_array($loanOrder->status, [
                LoanOrderStatus::PENDING,
                LoanOrderStatus::APPROVED,
                LoanOrderStatus::CANCELLED,
                LoanOrderStatus::RETURNED,
            ], true)) {
                throw new InvalidArgumentException(
                    __('messages.loan_orders.cannot_delete', ['status' => $loanOrder->status->value])
                );
            }

            $loanOrder->delete();
        });
    }

    /**
     * Release equipment back to ACTIVE if currently IN_USE.
     * Safe to call even when no equipment is attached.
     */
    private function releaseEquipment(LoanOrder $loanOrder): void
    {
        $loanOrder->loadMissing('equipments');
        foreach ($loanOrder->equipments as $equipment) {
            if ($equipment->status === EquipmentStatus::IN_USE) {
                $equipment->markAsActive();
            }
        }
    }
}
