<?php

namespace App\Features\Equipments\Services;

use App\Core\Services\TransactionHandler;
use App\Features\Equipments\Models\EquipmentRevision;

class EquipmentRevisionService
{
    public function __construct(
        private TransactionHandler $transactions
    ) {}

    public function create(array $data): EquipmentRevision
    {
        return $this->transactions->execute(function () use ($data) {
            return EquipmentRevision::create([
                'equipment_id'  => $data['equipment_id'],
                'status'        => $data['status'],
                'revision_date' => $data['revision_date'],
                'notes'         => $data['notes'] ?? null,
                'approved_by'   => $data['approved_by'] ?? null,
                'approved_at'   => $data['approved_at'] ?? null,
            ]);
        });
    }

    public function update(EquipmentRevision $equipmentRevision, array $data): EquipmentRevision
    {
        return $this->transactions->execute(function () use ($equipmentRevision, $data) {
            $equipmentRevision->update($data);
            return $equipmentRevision;
        });
    }

    public function delete(EquipmentRevision $equipmentRevision): ?bool
    {
        return $this->transactions->execute(function () use ($equipmentRevision) {
            return $equipmentRevision->delete();
        });
    }
}
