<?php

namespace App\Features\Teams\Services;

use App\Core\Services\TransactionHandler;
use App\Features\Teams\Models\Team;

class TeamService
{
    public function __construct(
        private TransactionHandler $transactions
    ) {}

    public function create(array $data): Team
    {
        return $this->transactions->execute(function () use ($data) {
            return Team::create([
                'sector_id' => $data['sector_id'],
                'name' => $data['name'],
            ]);
        });
    }

    public function update(Team $team, array $data): Team
    {
        return $this->transactions->execute(function () use ($team, $data) {
            $team->update($data);
            return $team;
        });
    }

    public function delete(Team $team): ?bool
    {
        return $this->transactions->execute(function () use ($team) {
            return $team->delete();
        });
    }
}
