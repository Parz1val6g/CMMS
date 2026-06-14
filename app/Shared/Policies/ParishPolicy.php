<?php

namespace App\Shared\Policies;

use App\Core\Policies\BasePolicy;
use App\Shared\Models\Parish;
use App\Shared\Models\User;

class ParishPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view', 'parishes');
    }

    public function view(User $user, Parish $parish): bool
    {
        return $this->hasPermission($user, 'view', 'parishes');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create', 'parishes');
    }

    public function update(User $user, Parish $parish): bool
    {
        return $this->hasPermission($user, 'update', 'parishes');
    }

    public function delete(User $user, Parish $parish): bool
    {
        return $this->hasPermission($user, 'delete', 'parishes');
    }
}
