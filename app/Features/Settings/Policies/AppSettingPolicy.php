<?php

namespace App\Features\Settings\Policies;

use App\Core\Policies\BasePolicy;
use App\Shared\Models\AppSetting;
use App\Shared\Models\User;

class AppSettingPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(User $user, AppSetting $appSetting): bool
    {
        return $this->isAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, AppSetting $appSetting): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, AppSetting $appSetting): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user, AppSetting $appSetting): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, AppSetting $appSetting): bool
    {
        return $this->isAdmin($user);
    }
}
