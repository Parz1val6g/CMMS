<?php

namespace App\Features\Clients\Policies;

use App\Core\Policies\BasePolicy;
use App\Features\Clients\Models\Client;
use App\Shared\Models\User;

class ClientPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view', 'clients');
    }

    public function view(User $user, Client $client): bool
    {
        return $this->hasPermission($user, 'view', 'clients');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create', 'clients');
    }

    public function update(User $user, Client $client): bool
    {
        return $this->hasPermission($user, 'update', 'clients');
    }

    public function delete(User $user, Client $client): bool
    {
        return $this->hasPermission($user, 'delete', 'clients');
    }

    public function restore(User $user, Client $client): bool
    {
        return $this->hasPermission($user, 'restore', 'clients');
    }

    public function forceDelete(User $user, Client $client): bool
    {
        return $this->hasPermission($user, 'force_delete', 'clients');
    }
}
