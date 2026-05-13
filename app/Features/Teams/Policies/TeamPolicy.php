<?php

namespace App\Features\Teams\Policies;

use App\Core\Policies\BasePolicy;
use App\Features\Teams\Models\Team;
use App\Shared\Models\User;

class TeamPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view', 'teams');
    }

    public function view(User $user, Team $team): bool
    {
        if (!$this->hasPermission($user, 'view', 'teams')) {
            return false;
        }

        // sector_manager can only view teams in their sectors
        if ($this->isSectorManager($user)) {
            return $team->sector->head_id === $user->id;
        }

        // supervisor can only view teams assigned to their mini-tasks
        if ($this->isSupervisor($user)) {
            return $team->miniTasks()
                ->where('supervisor_id', $user->id)
                ->exists();
        }

        // team_manager can only view their own team
        if ($this->isTeamManager($user)) {
            return $team->responsible_id === $user->id;
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create', 'teams');
    }

    public function update(User $user, Team $team): bool
    {
        if (!$this->hasPermission($user, 'update', 'teams')) {
            return false;
        }

        if ($this->isSectorManager($user)) {
            return $team->sector->head_id === $user->id;
        }

        if ($this->isTeamManager($user)) {
            return $team->responsible_id === $user->id;
        }

        return true;
    }

    public function delete(User $user, Team $team): bool
    {
        return $this->hasPermission($user, 'delete', 'teams');
    }

    public function restore(User $user, Team $team): bool
    {
        return $this->hasPermission($user, 'restore', 'teams');
    }

    public function forceDelete(User $user, Team $team): bool
    {
        return $this->hasPermission($user, 'force_delete', 'teams');
    }
}
