<?php

namespace App\Features\Sectors\Services;

use App\Core\Services\TransactionHandler;
use App\Features\Sectors\Models\Sector;

class SectorService
{
    public function __construct(
        private TransactionHandler $transactions
    ) {}

    public function create(array $data): Sector
    {
        return $this->transactions->execute(function () use ($data) {
            return Sector::create([
                'name' => $data['name'],
                'head_id' => $data['head_id'] ?? null,
            ]);
        });
    }

    public function update(Sector $sector, array $data): Sector
    {
        return $this->transactions->execute(function () use ($sector, $data) {
            $sector->update($data);
            return $sector;
        });
    }

    public function delete(Sector $sector): ?bool
    {
        return $this->transactions->execute(function () use ($sector) {
            return $sector->delete();
        });
    }
}
