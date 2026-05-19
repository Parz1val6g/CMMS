<?php

namespace App\Features\LoanOrders\Policies;

use App\Core\Enums\LoanOrderStatus;
use App\Core\Enums\PermissionAction;
use App\Core\Enums\PermissionResource;
use App\Core\Policies\BasePolicy;
use App\Features\LoanOrders\Models\LoanOrder;
use App\Shared\Models\User;

class LoanOrderPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission(
            $user,
            PermissionAction::VIEW->value,
            PermissionResource::LOAN_ORDERS->value
        );
    }

    public function view(User $user, LoanOrder $loanOrder): bool
    {
        if ($this->isAdmin($user)) return true;
        if ($this->isManagerScoped($user, $loanOrder->manager)) {
            return true;
        }
        return $loanOrder->entity?->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $this->hasPermission(
            $user,
            PermissionAction::CREATE->value,
            PermissionResource::LOAN_ORDERS->value
        );
    }

    public function update(User $user, LoanOrder $loanOrder): bool
    {
        if ($this->isAdmin($user)) return true;
        return $this->isManagerScoped($user, $loanOrder->manager);
    }

    public function approve(User $user, LoanOrder $loanOrder): bool
    {
        if ($this->isAdmin($user)) return true;
        return $this->isManagerScoped($user, $loanOrder->manager)
            && $loanOrder->status === LoanOrderStatus::PENDING;
    }

    public function checkout(User $user, LoanOrder $loanOrder): bool
    {
        if ($this->isAdmin($user)) return true;
        return $this->isManagerScoped($user, $loanOrder->manager)
            && $loanOrder->status === LoanOrderStatus::APPROVED;
    }

    public function cancel(User $user, LoanOrder $loanOrder): bool
    {
        if ($this->isAdmin($user)) return true;
        if ($loanOrder->status === LoanOrderStatus::CANCELLED) {
            return true;
        }
        if (!$loanOrder->status->isPending()) {
            return false;
        }
        if ($this->isManagerScoped($user, $loanOrder->manager)) {
            return true;
        }
        if ($loanOrder->entity?->user_id === $user->id) {
            return true;
        }
        return false;
    }

    public function complete(User $user, LoanOrder $loanOrder): bool
    {
        if ($this->isAdmin($user)) return true;
        return $this->isManagerScoped($user, $loanOrder->manager);
    }

    public function initiateReturn(User $user, LoanOrder $loanOrder): bool
    {
        if ($this->isAdmin($user)) return true;
        return $this->isManagerScoped($user, $loanOrder->manager);
    }

    public function delete(User $user, LoanOrder $loanOrder): bool
    {
        if ($this->isAdmin($user)) return true;
        return $this->isManagerScoped($user, $loanOrder->manager);
    }
}
