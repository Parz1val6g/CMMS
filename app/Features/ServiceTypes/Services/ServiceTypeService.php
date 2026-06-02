<?php

namespace App\Features\ServiceTypes\Services;

use App\Core\Cache\RefCache;
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
            $serviceType = ServiceType::create([
                'sector_id'   => $data['sector_id'],
                'name'        => $data['name'],
                'description' => $data['description'] ?? null,
            ]);
            RefCache::flushServiceTypes();
            return $serviceType;
        });
    }

    public function update(ServiceType $serviceType, array $data): ServiceType
    {
        return $this->transactions->execute(function () use ($serviceType, $data) {
            $serviceType->update($data);
            RefCache::flushServiceTypes();
            return $serviceType;
        });
    }

    public function delete(ServiceType $serviceType): ?bool
    {
        return $this->transactions->execute(function () use ($serviceType) {
            $result = $serviceType->delete();
            RefCache::flushServiceTypes();
            return $result;
        });
    }
}
