<?php

namespace App\Features\ServiceOrderCategories\Services;

use App\Core\Services\TransactionHandler;
use App\Features\ServiceOrderCategories\Models\ServiceOrderCategory;

class ServiceOrderCategoryService
{
    public function __construct(
        private TransactionHandler $transactions
    ) {}

    public function create(array $data): ServiceOrderCategory
    {
        return $this->transactions->execute(function () use ($data) {
            return ServiceOrderCategory::create([
                'name'        => $data['name'],
                'description' => $data['description'] ?? null,
            ]);
        });
    }

    public function update(ServiceOrderCategory $serviceOrderCategory, array $data): ServiceOrderCategory
    {
        return $this->transactions->execute(function () use ($serviceOrderCategory, $data) {
            $serviceOrderCategory->update($data);
            return $serviceOrderCategory;
        });
    }

    public function delete(ServiceOrderCategory $serviceOrderCategory): ?bool
    {
        return $this->transactions->execute(function () use ($serviceOrderCategory) {
            return $serviceOrderCategory->delete();
        });
    }
}
