<?php

namespace App\Features\Teams\Services;

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
            $user = User::findOrFail($data['responsible_id']);

            if (Worker::where('user_id', $user->id)->exists()) {
                throw ValidationException::withMessages([
                    'responsible_id' => __('validation.custom.responsible_already_worker'),
                ]);
            }

            $team = Team::create([
                'sector_id' => $data['sector_id'],
                'name' => $data['name'],
                'responsible_id' => $data['responsible_id'],
            ]);

            $this->ensureRole($user, 'team_manager');
            $this->ensureRole($user, 'worker');

            Worker::create([
                'user_id' => $user->id,
                'team_id' => $team->id,
            ]);

            return $team;
        });
    }

    public function update(Team $team, array $data): Team
    {
        return $this->transactions->execute(function () use ($team, $data) {
            if (isset($data['responsible_id']) && $data['responsible_id'] !== $team->responsible_id) {
                $user = User::findOrFail($data['responsible_id']);

                $this->ensureRole($user, 'team_manager');
                $this->ensureRole($user, 'worker');

                if (!Worker::where('user_id', $user->id)->where('team_id', $team->id)->exists()) {
                    Worker::create([
                        'user_id' => $user->id,
                        'team_id' => $team->id,
                    ]);
                }
            }

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

    private function ensureRole(User $user, string $roleName): void
    {
        if ($user->roles()->where('name', $roleName)->exists()) {
            return;
        }

        $role = Role::where('name', $roleName)->firstOrFail();
        $user->roles()->attach($role);
    }
}
