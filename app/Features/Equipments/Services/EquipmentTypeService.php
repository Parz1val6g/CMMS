<?php

namespace App\Features\Equipments\Services;

use App\Core\Services\TransactionHandler;
use App\Features\Equipments\Models\EquipmentType;

class EquipmentTypeService
{
    public function __construct(
        private TransactionHandler $transactions
    ) {}

    public function create(array $data): EquipmentType
    {
        return $this->transactions->execute(function () use ($data) {
            return EquipmentType::create([
                'name' => $data['name'],
                'category' => $data['category'] ?? null,
                'description' => $data['description'] ?? null,
                'active' => $data['active'] ?? true,
            ]);
        });
    }

    public function update(EquipmentType $equipmentType, array $data): EquipmentType
    {
        return $this->transactions->execute(function () use ($equipmentType, $data) {
            $equipmentType->update($data);
            return $equipmentType;
        });
    }

    public function delete(EquipmentType $equipmentType): ?bool
    {
        return $this->transactions->execute(function () use ($equipmentType) {
            return $equipmentType->delete();
        });
    }
}
