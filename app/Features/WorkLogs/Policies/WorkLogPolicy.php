<?php

namespace App\Features\WorkLogs\Policies;

use App\Core\Enums\PermissionAction;
use App\Core\Enums\PermissionResource;
use App\Core\Policies\BasePolicy;
use App\Features\WorkLogs\Models\WorkLog;
use App\Shared\Models\User;

class WorkLogPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'view', 'work_logs');
    }

    public function view(User $user, WorkLog $workLog): bool
    {
        $isAssignedWorker = $workLog->workers()->where('workers.user_id', $user->id)->exists();
        
        return $this->hasPermission($user, 'view', 'work_logs')
            || $isAssignedWorker
            || $this->isOwner($user, $workLog->miniTask?->supervisor);
    }

    public function create(User $user): bool
    {
        // Workers log their own work
        return $this->hasPermission($user, 'create', 'work_logs') || $user->workerProfile()->exists();
    }

    public function update(User $user, WorkLog $workLog): bool
    {
        $isAssignedWorker = $workLog->workers()->where('workers.user_id', $user->id)->exists();
        return $this->hasPermission($user, 'update', 'work_logs') || $isAssignedWorker;
    }

    public function complete(User $user, WorkLog $workLog): bool
    {
        if ($this->isAdmin($user)) return true;
        $isAssignedWorker = $workLog->workers()->where('workers.user_id', $user->id)->exists();
        return $this->hasPermission($user, PermissionAction::COMPLETE->value, PermissionResource::WORK_LOGS->value)
            || $isAssignedWorker;
    }

    public function approve(User $user, WorkLog $workLog): bool
    {
        if ($this->isAdmin($user)) return true;
        $isSupervisor = $this->isOwner($user, $workLog->miniTask?->supervisor);
        return $this->hasPermission($user, PermissionAction::APPROVE->value, PermissionResource::WORK_LOGS->value)
            || $isSupervisor;
    }

    public function reject(User $user, WorkLog $workLog): bool
    {
        if ($this->isAdmin($user)) return true;
        $isSupervisor = $this->isOwner($user, $workLog->miniTask?->supervisor);
        return $this->hasPermission($user, PermissionAction::REJECT->value, PermissionResource::WORK_LOGS->value)
            || $isSupervisor;
    }
}
