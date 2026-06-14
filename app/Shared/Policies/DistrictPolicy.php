<?php

namespace App\Shared\Policies;

use App\Core\Policies\BasePolicy;
use App\Shared\Models\District;
use App\Shared\Models\User;

class DistrictPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view', 'districts');
    }

    public function view(User $user, District $district): bool
    {
        return $this->hasPermission($user, 'view', 'districts');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create', 'districts');
    }

    public function update(User $user, District $district): bool
    {
        return $this->hasPermission($user, 'update', 'districts');
    }

    public function delete(User $user, District $district): bool
    {
        return $this->hasPermission($user, 'delete', 'districts');
    }
}
