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
                'serial_number' => $data['serial_number'] ?? null,
                'manager_id' => $managerId,
                'status' => EquipmentStatus::ACTIVE->value,
                'is_loanable' => $data['is_loanable'] ?? true,
                'description' => $data['description'] ?? null,
                'equipment_type_id' => $data['equipment_type_id'] ?? null,
                'license_plate' => $data['license_plate'] ?? null,
                'cost_per_hour' => $data['cost_per_hour'] ?? null,
                'internal_reference' => $data['internal_reference'] ?? null,
                'manufacturing_year' => $data['manufacturing_year'] ?? null,
                'inspection_date' => $data['inspection_date'] ?? null,
                'counting_type_id' => $data['counting_type_id'] ?? null,
                'revision_interval' => $data['revision_interval'] ?? 365,
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
