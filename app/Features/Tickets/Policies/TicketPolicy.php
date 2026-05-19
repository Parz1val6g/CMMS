<?php

namespace App\Features\Tickets\Policies;

use App\Core\Enums\PermissionAction;
use App\Core\Enums\PermissionResource;
use App\Core\Policies\BasePolicy;
use App\Features\Tickets\Models\Ticket;
use App\Shared\Models\User;

class TicketPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, PermissionAction::VIEW->value, PermissionResource::TICKETS->value);
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ($this->hasRole($user, 'manager')) {
            return true;
        }

        if ($this->hasRole($user, 'ticket_manager')) {
            return $ticket->ticket_manager_id === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, PermissionAction::CREATE->value, PermissionResource::TICKETS->value);
    }

    public function update(User $user, Ticket $ticket): bool
    {
        if ($ticket->status->isTerminal()) {
            return false;
        }

        if ($this->isAdmin($user) || $this->hasRole($user, 'manager')) {
            return true;
        }

        if ($this->hasRole($user, 'ticket_manager')) {
            return $ticket->ticket_manager_id === $user->id;
        }

        return false;
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $this->update($user, $ticket);
    }

    public function convert(User $user, Ticket $ticket): bool
    {
        if ($ticket->status->isTerminal()) {
            return false;
        }

        return $this->isAdmin($user) || $this->hasRole($user, 'manager');
    }
}
