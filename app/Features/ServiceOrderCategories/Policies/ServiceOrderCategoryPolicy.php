<?php

namespace App\Features\ServiceOrderCategories\Policies;

use App\Core\Policies\BasePolicy;
use App\Features\ServiceOrderCategories\Models\ServiceOrderCategory;
use App\Shared\Models\User;

class ServiceOrderCategoryPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view', 'service_order_categories');
    }

    public function view(User $user, ServiceOrderCategory $serviceOrderCategory): bool
    {
        return $this->hasPermission($user, 'view', 'service_order_categories');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create', 'service_order_categories');
    }

    public function update(User $user, ServiceOrderCategory $serviceOrderCategory): bool
    {
        return $this->hasPermission($user, 'update', 'service_order_categories');
    }

    public function delete(User $user, ServiceOrderCategory $serviceOrderCategory): bool
    {
        return $this->hasPermission($user, 'delete', 'service_order_categories');
    }

    public function restore(User $user, ServiceOrderCategory $serviceOrderCategory): bool
    {
        return $this->hasPermission($user, 'restore', 'service_order_categories');
    }

    public function forceDelete(User $user, ServiceOrderCategory $serviceOrderCategory): bool
    {
        return $this->hasPermission($user, 'force_delete', 'service_order_categories');
    }
}
