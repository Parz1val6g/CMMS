<?php
namespace App\Features\Notifications\Policies;

use App\Core\Policies\BasePolicy;
use App\Features\Notifications\Models\Notification;
use App\Shared\Models\User;

class NotificationPolicy extends BasePolicy
{
    /**
     * viewAny returns true because the controller always scopes to auth()->id().
     * Extending BasePolicy ensures the admin before() override still runs.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Notification $notification): bool
    {
        return $this->isAdmin($user) || $user->id === $notification->user_id;
    }

    public function update(User $user, Notification $notification): bool
    {
        return $this->isAdmin($user) || $user->id === $notification->user_id;
    }
}
