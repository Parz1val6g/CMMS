<?php

namespace App\Features\Sectors\Services;

use App\Core\Cache\RefCache;
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
            $sector = Sector::create([
                'name' => $data['name'],
                'head_id' => $data['head_id'] ?? null,
            ]);
            RefCache::flushSectors();
            return $sector;
        });
    }

    public function update(Sector $sector, array $data): Sector
    {
        return $this->transactions->execute(function () use ($sector, $data) {
            $sector->update($data);
            RefCache::flushSectors();
            return $sector;
        });
    }

    public function delete(Sector $sector): ?bool
    {
        return $this->transactions->execute(function () use ($sector) {
            $result = $sector->delete();
            RefCache::flushSectors();
            return $result;
        });
    }
}
