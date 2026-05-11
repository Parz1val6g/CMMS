<?php

namespace App\Features\Equipments\Services;

use App\Core\Enums\EquipmentStatus;
use App\Core\Services\TransactionHandler;
use App\Exceptions\InvalidStateTransitionException;
use App\Features\Equipments\Models\Equipment;

class EquipmentService
{
    public function __construct(
        private TransactionHandler $transactions
    ) {}

    public function create(array $data, string $managerId): Equipment
    {
        return $this->transactions->execute(function () use ($data, $managerId) {
            return Equipment::create([
                'name' => $data['name'],
                'brand' => $data['brand'] ?? null,
                'model' => $data['model'] ?? null,
                'serial_number' => $data['serial_number'],
                'manager_id' => $managerId,
                'status' => EquipmentStatus::ACTIVE->value,
                'is_loanable' => $data['is_loanable'] ?? true,
                'description' => $data['description'] ?? null,
                'revision_interval_days' => $data['revision_interval_days'] ?? 365,
            ]);
        });
    }

    /**
     * Update equipment with state transition validation.
     * Throws InvalidStateTransitionException if the status change is not allowed.
     */
    public function update(Equipment $equipment, array $data): Equipment
    {
        // Guard: validate state transition before entering transaction
        if (isset($data['status'])) {
            $target = EquipmentStatus::tryFrom($data['status']);
            if (!$target) {
                throw new InvalidStateTransitionException(
                    __('messages.services.equipment.invalid_status', ['value' => $data['status']])
                );
            }
            if (!$equipment->canTransitionTo($target)) {
                throw new InvalidStateTransitionException(
                    __('messages.services.equipment.cannot_transition', ['from' => $equipment->status->value, 'to' => $target->value])
                );
            }
        }

        return $this->transactions->execute(function () use ($equipment, $data) {
            $equipment->update($data);
            return $equipment;
        });
    }

    public function delete(Equipment $equipment): ?bool
    {
        return $this->transactions->execute(function () use ($equipment) {
            return $equipment->delete();
        });
    }
}
