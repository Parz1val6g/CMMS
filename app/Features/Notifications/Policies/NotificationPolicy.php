<?php
namespace App\Features\Notifications\Policies;

use App\Core\Policies\BasePolicy;
use App\Features\Notifications\Models\Notification;
use App\Shared\Models\User;

class NotificationPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view', 'notifications');
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
