<?php

namespace App\Features\ServiceOrders\Policies;

use App\Core\Policies\BasePolicy;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Shared\Models\User;

class ServiceOrderPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view', 'service_orders');
    }

    public function view(User $user, ServiceOrder $serviceOrder): bool
    {
        return $this->hasPermission($user, 'view', 'service_orders') || $this->isOwner($user, $serviceOrder->manager) || $this->isOwner($user, $serviceOrder->client?->user);
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create', 'service_orders');
    }

    public function update(User $user, ServiceOrder $serviceOrder): bool
    {
        return $this->hasPermission($user, 'update', 'service_orders') || $this->isOwner($user, $serviceOrder->manager);
    }

    public function cancel(User $user, ServiceOrder $serviceOrder): bool
    {
        return $this->hasPermission($user, 'cancel', 'service_orders') || $this->isOwner($user, $serviceOrder->manager);
    }

    public function complete(User $user, ServiceOrder $serviceOrder): bool
    {
        return $this->hasPermission($user, 'complete', 'service_orders') || $this->isOwner($user, $serviceOrder->manager);
    }
}
