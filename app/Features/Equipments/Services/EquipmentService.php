<?php

namespace App\Features\Equipments\Services;

use App\Core\Services\TransactionHandler;
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
                'status' => 'active',
                'is_loanable' => $data['is_loanable'] ?? true,
                'description' => $data['description'] ?? null,
                'revision_interval_days' => $data['revision_interval_days'] ?? 365,
            ]);
        });
    }

    public function update(Equipment $equipment, array $data): Equipment
    {
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
