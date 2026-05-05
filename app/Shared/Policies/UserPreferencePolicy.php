<?php

namespace App\Shared\Policies;

use App\Core\Policies\BasePolicy;
use App\Shared\Models\User;
use App\Shared\Models\UserPreference;

class UserPreferencePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        // Gate passes; the controller scopes the query to auth()->id() so users
        // only ever receive their own preferences in the response.
        return true;
    }

    public function view(User $user, UserPreference $preference): bool
    {
        return $this->isOwner($user, $preference->user);
    }

    public function create(User $user): bool
    {
        // Any authenticated user can create their own preferences
        return true;
    }

    public function update(User $user, UserPreference $preference): bool
    {
        return $this->isOwner($user, $preference->user);
    }

    public function delete(User $user, UserPreference $preference): bool
    {
        return $this->isOwner($user, $preference->user);
    }
}
