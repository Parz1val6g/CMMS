<?php

namespace App\Features\ServiceOrders\Policies;

use App\Core\Enums\PermissionAction;
use App\Core\Enums\PermissionResource;
use App\Core\Policies\BasePolicy;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Shared\Models\User;

class ServiceOrderPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, PermissionAction::VIEW->value, PermissionResource::SERVICE_ORDERS->value);
    }

    public function view(User $user, ServiceOrder $serviceOrder): bool
    {
        return $this->isManagerScoped($user, $serviceOrder->manager);
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, PermissionAction::CREATE->value, PermissionResource::SERVICE_ORDERS->value);
    }

    public function update(User $user, ServiceOrder $serviceOrder): bool
    {
        return $this->isManagerScoped($user, $serviceOrder->manager);
    }

    public function cancel(User $user, ServiceOrder $serviceOrder): bool
    {
        return $this->isManagerScoped($user, $serviceOrder->manager);
    }

    public function complete(User $user, ServiceOrder $serviceOrder): bool
    {
        return $this->isManagerScoped($user, $serviceOrder->manager);
    }

    public function delete(User $user, ServiceOrder $serviceOrder): bool
    {
        return $this->isManagerScoped($user, $serviceOrder->manager);
    }

    public function restore(User $user, ServiceOrder $serviceOrder): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, ServiceOrder $serviceOrder): bool
    {
        return $this->isAdmin($user);
    }
}
