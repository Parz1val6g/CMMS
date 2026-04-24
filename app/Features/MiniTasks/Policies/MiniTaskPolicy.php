<?php

namespace App\Features\MiniTasks\Policies;

use App\Core\Policies\BasePolicy;
use App\Features\MiniTasks\Models\MiniTask;
use App\Shared\Models\User;

class MiniTaskPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return clone $this->hasPermission($user, 'view', 'mini_tasks'); // Just true for roles that have it
    }

    public function view(User $user, MiniTask $miniTask): bool
    {
        // A worker assigned to the minitask can view it
        $isAssignedWorker = $miniTask->workers()->where('user_id', $user->id)->exists();
        // Or if their team is assigned
        $isAssignedTeam = $miniTask->teams()->whereHas('workers', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->exists();

        return $this->hasPermission($user, 'view', 'mini_tasks') 
            || $this->isOwner($user, $miniTask->supervisor) 
            || $isAssignedWorker 
            || $isAssignedTeam;
    }

    public function create(User $user): bool
    {
        // Sector Heads usually do this
        return $this->hasPermission($user, 'create', 'mini_tasks');
    }

    public function update(User $user, MiniTask $miniTask): bool
    {
        return $this->hasPermission($user, 'update', 'mini_tasks') || $this->isOwner($user, $miniTask->supervisor);
    }

    public function complete(User $user, MiniTask $miniTask): bool
    {
        return $this->hasPermission($user, 'complete', 'mini_tasks') || $this->isOwner($user, $miniTask->supervisor);
    }
}
