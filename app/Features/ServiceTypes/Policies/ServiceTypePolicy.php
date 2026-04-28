<?php

namespace App\Features\ServiceTypes\Policies;

use App\Core\Policies\BasePolicy;
use App\Features\ServiceTypes\Models\ServiceType;
use App\Shared\Models\User;

class ServiceTypePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view', 'service_types');
    }

    public function view(User $user, ServiceType $serviceType): bool
    {
        return $this->hasPermission($user, 'view', 'service_types');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create', 'service_types');
    }

    public function update(User $user, ServiceType $serviceType): bool
    {
        return $this->hasPermission($user, 'update', 'service_types');
    }

    public function delete(User $user, ServiceType $serviceType): bool
    {
        return $this->hasPermission($user, 'delete', 'service_types');
    }

    public function restore(User $user, ServiceType $serviceType): bool
    {
        return $this->hasPermission($user, 'restore', 'service_types');
    }

    public function forceDelete(User $user, ServiceType $serviceType): bool
    {
        return $this->hasPermission($user, 'force_delete', 'service_types');
    }
}
