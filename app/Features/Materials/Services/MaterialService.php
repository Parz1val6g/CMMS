<?php

namespace App\Features\Materials\Services;

use App\Core\Services\TransactionHandler;
use App\Features\Materials\Models\Material;

class MaterialService
{
    public function __construct(
        private TransactionHandler $transactions
    ) {}

    public function create(array $data): Material
    {
        return $this->transactions->execute(function () use ($data) {
            return Material::create([
                'name' => $data['name'],
                'unit_id' => $data['unit_id'],
                'stock_quantity' => $data['stock_quantity'] ?? 0,
            ]);
        });
    }

    public function update(Material $material, array $data): Material
    {
        return $this->transactions->execute(function () use ($material, $data) {
            $material->update($data);
            return $material;
        });
    }

    public function delete(Material $material): ?bool
    {
        return $this->transactions->execute(function () use ($material) {
            return $material->delete();
        });
    }
}
