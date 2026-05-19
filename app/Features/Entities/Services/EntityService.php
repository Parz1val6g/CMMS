<?php

namespace App\Features\Entities\Services;

use App\Core\Services\TransactionHandler;
use App\Features\Entities\Models\Entity;

class EntityService
{
    public function __construct(
        private TransactionHandler $transactions
    ) {}

    public function create(array $data): Entity
    {
        return $this->transactions->execute(function () use ($data) {
            return Entity::create([
                'user_id'     => $data['user_id'],
                'entity_type' => $data['entity_type'],
                'nif'         => $data['nif'] ?? null,
                'name'        => $data['name'],
                'phone'       => $data['phone'] ?? null,
                'location_id' => $data['location_id'] ?? null,
            ]);
        });
    }

    public function update(Entity $entity, array $data): Entity
    {
        return $this->transactions->execute(function () use ($entity, $data) {
            $allowed = ['entity_type', 'nif', 'name', 'phone', 'location_id'];
            $updateData = array_intersect_key($data, array_flip($allowed));

            if (!empty($updateData)) {
                $entity->update($updateData);
            }

            return $entity->fresh();
        });
    }

    public function delete(Entity $entity): void
    {
        $this->transactions->execute(function () use ($entity) {
            $entity->delete();
        });
    }
}
