<?php

namespace App\Features\Workers\Services;

use App\Core\Services\TransactionHandler;
use App\Features\Workers\Models\Worker;

class WorkerService
{
    public function __construct(
        private TransactionHandler $transactions
    ) {}

    public function create(array $data): Worker
    {
        return $this->transactions->execute(function () use ($data) {
            return Worker::create([
                'user_id' => $data['user_id'],
                'team_id' => $data['team_id'],
            ]);
        });
    }

    public function update(Worker $worker, array $data): Worker
    {
        return $this->transactions->execute(function () use ($worker, $data) {
            $worker->update($data);
            return $worker;
        });
    }

    public function delete(Worker $worker): ?bool
    {
        return $this->transactions->execute(function () use ($worker) {
            return $worker->delete();
        });
    }
}
