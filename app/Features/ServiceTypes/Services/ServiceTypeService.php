<?php

namespace App\Features\ServiceTypes\Services;

use App\Core\Services\TransactionHandler;
use App\Features\ServiceTypes\Models\ServiceType;

class ServiceTypeService
{
    public function __construct(
        private TransactionHandler $transactions
    ) {}

    public function create(array $data): ServiceType
    {
        return $this->transactions->execute(function () use ($data) {
            return ServiceType::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);
        });
    }

    public function update(ServiceType $serviceType, array $data): ServiceType
    {
        return $this->transactions->execute(function () use ($serviceType, $data) {
            $serviceType->update($data);
            return $serviceType;
        });
    }

    public function delete(ServiceType $serviceType): ?bool
    {
        return $this->transactions->execute(function () use ($serviceType) {
            return $serviceType->delete();
        });
    }
}
