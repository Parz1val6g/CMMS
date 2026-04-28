<?php

namespace App\Features\Tasks\Policies;

use App\Core\Policies\BasePolicy;
use App\Features\Tasks\Models\Task;
use App\Shared\Models\User;

class TaskPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view', 'tasks');
    }

    public function view(User $user, Task $task): bool
    {
        // Manager of the task or Sector Head assigned to the task can view it
        $isSectorHead = $task->sectors()->where('head_id', $user->id)->exists();
        
        return $this->hasPermission($user, 'view', 'tasks') || $this->isOwner($user, $task->manager) || $isSectorHead;
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create', 'tasks');
    }

    public function update(User $user, Task $task): bool
    {
        return $this->hasPermission($user, 'update', 'tasks') || $this->isOwner($user, $task->manager);
    }

    public function cancel(User $user, Task $task): bool
    {
        return $this->hasPermission($user, 'cancel', 'tasks') || $this->isOwner($user, $task->manager);
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->hasPermission($user, 'delete', 'tasks') || $this->isOwner($user, $task->manager);
    }

    public function restore(User $user, Task $task): bool
    {
        return $this->hasPermission($user, 'restore', 'tasks');
    }

    public function forceDelete(User $user, Task $task): bool
    {
        return $this->hasPermission($user, 'force_delete', 'tasks');
    }
}
