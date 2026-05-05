<?php

namespace App\Features\Clients\Policies;

use App\Core\Enums\PermissionAction;
use App\Core\Enums\PermissionResource;
use App\Core\Policies\BasePolicy;
use App\Features\Clients\Models\Client;
use App\Shared\Models\User;

class ClientPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, PermissionAction::VIEW->value, PermissionResource::CLIENTS->value);
    }

    public function view(User $user, Client $client): bool
    {
        return $this->hasPermission($user, PermissionAction::VIEW->value, PermissionResource::CLIENTS->value);
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, PermissionAction::CREATE->value, PermissionResource::CLIENTS->value);
    }

    public function update(User $user, Client $client): bool
    {
        return $this->hasPermission($user, PermissionAction::UPDATE->value, PermissionResource::CLIENTS->value);
    }

    public function delete(User $user, Client $client): bool
    {
        return $this->hasPermission($user, PermissionAction::DELETE->value, PermissionResource::CLIENTS->value);
    }

    public function restore(User $user, Client $client): bool
    {
        return $this->hasPermission($user, PermissionAction::RESTORE->value, PermissionResource::CLIENTS->value);
    }

    public function forceDelete(User $user, Client $client): bool
    {
        return $this->hasPermission($user, PermissionAction::FORCE_DELETE->value, PermissionResource::CLIENTS->value);
    }
}
