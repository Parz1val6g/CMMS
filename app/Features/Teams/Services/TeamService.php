<?php

namespace App\Features\Teams\Services;

use App\Core\Cache\RefCache;
use App\Core\Services\TransactionHandler;
use App\Features\Teams\Models\Team;
use App\Features\Workers\Models\Worker;
use App\Shared\Models\Role;
use App\Shared\Models\User;
use Illuminate\Validation\ValidationException;

class TeamService
{
    public function __construct(
        private TransactionHandler $transactions
    ) {}

    public function create(array $data): Team
    {
        return $this->transactions->execute(function () use ($data) {
            RefCache::flushTeams();
            $responsibleId = $data['responsible_id'] ?? null;

            if ($responsibleId) {
                $alreadyWorker = Worker::where('user_id', $responsibleId)->exists();
                if ($alreadyWorker) {
                    throw ValidationException::withMessages([
                        'responsible_id' => [__('validation.custom.responsible_already_worker')],
                    ]);
                }
            }

            $team = Team::create([
                'sector_id'      => $data['sector_id'],
                'responsible_id' => $responsibleId,
                'name'           => $data['name'],
            ]);

            if ($responsibleId) {
                $this->assignWorkerAndRoles($responsibleId, $team->id);
            }

            return $team;
        });
    }

    public function update(Team $team, array $data): Team
    {
        return $this->transactions->execute(function () use ($team, $data) {
            RefCache::flushTeams();
            $newResponsibleId = $data['responsible_id'] ?? $team->responsible_id;

            if ($newResponsibleId && $newResponsibleId !== $team->responsible_id) {
                $this->assignWorkerAndRoles($newResponsibleId, $team->id);
            }

            $team->update($data);
            return $team;
        });
    }

    public function delete(Team $team): ?bool
    {
        return $this->transactions->execute(function () use ($team) {
            $result = $team->delete();
            RefCache::flushTeams();
            return $result;
        });
    }

    private function assignWorkerAndRoles(string|int $userId, string|int $teamId): void
    {
        $workerExists = Worker::where('user_id', $userId)
            ->where('team_id', $teamId)
            ->exists();

        if (!$workerExists) {
            Worker::create([
                'user_id' => $userId,
                'team_id' => $teamId,
            ]);
        }

        $user = User::find($userId);
        if ($user) {
            foreach (['team_manager', 'worker'] as $roleName) {
                $role = Role::where('name', $roleName)->first();
                if ($role && !$user->roles()->where('name', $roleName)->exists()) {
                    $user->roles()->attach($role->id);
                }
            }
        }
    }
}
