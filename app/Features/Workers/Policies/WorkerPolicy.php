<?php

namespace App\Features\Workers\Policies;

use App\Core\Policies\BasePolicy;
use App\Features\Workers\Models\Worker;
use App\Shared\Models\User;

class WorkerPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view', 'workers');
    }

    public function view(User $user, Worker $worker): bool
    {
        return $this->hasPermission($user, 'view', 'workers');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create', 'workers');
    }

    public function update(User $user, Worker $worker): bool
    {
        return $this->hasPermission($user, 'update', 'workers');
    }

    public function delete(User $user, Worker $worker): bool
    {
        return $this->hasPermission($user, 'delete', 'workers');
    }

    public function restore(User $user, Worker $worker): bool
    {
        return $this->hasPermission($user, 'restore', 'workers');
    }

    public function forceDelete(User $user, Worker $worker): bool
    {
        return $this->hasPermission($user, 'force_delete', 'workers');
    }
}
