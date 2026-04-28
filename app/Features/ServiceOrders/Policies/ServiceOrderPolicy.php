<?php

namespace App\Features\ServiceOrders\Policies;

use App\Core\Policies\BasePolicy;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Shared\Models\User;

class ServiceOrderPolicy extends BasePolicy
{
    /* ── DEMO MODE: All gates return true for presentation ─────── */

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ServiceOrder $serviceOrder): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ServiceOrder $serviceOrder): bool
    {
        return true;
    }

    public function cancel(User $user, ServiceOrder $serviceOrder): bool
    {
        return true;
    }

    public function complete(User $user, ServiceOrder $serviceOrder): bool
    {
        return true;
    }

    public function delete(User $user, ServiceOrder $serviceOrder): bool
    {
        return true;
    }

    public function restore(User $user, ServiceOrder $serviceOrder): bool
    {
        return true;
    }

    public function forceDelete(User $user, ServiceOrder $serviceOrder): bool
    {
        return true;
    }
}
