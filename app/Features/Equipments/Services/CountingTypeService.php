<?php

namespace App\Features\Equipments\Services;

use App\Core\Services\TransactionHandler;
use App\Features\Equipments\Models\CountingType;

class CountingTypeService
{
    public function __construct(
        private TransactionHandler $transactions
    ) {}

    public function create(array $data): CountingType
    {
        return $this->transactions->execute(function () use ($data) {
            return CountingType::create([
                'name' => $data['name'],
                'value' => $data['value'] ?? null,
                'active' => $data['active'] ?? true,
            ]);
        });
    }

    public function update(CountingType $countingType, array $data): CountingType
    {
        return $this->transactions->execute(function () use ($countingType, $data) {
            $countingType->update($data);
            return $countingType;
        });
    }

    public function delete(CountingType $countingType): ?bool
    {
        return $this->transactions->execute(function () use ($countingType) {
            return $countingType->delete();
        });
    }
}
