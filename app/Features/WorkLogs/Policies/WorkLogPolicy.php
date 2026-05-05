<?php

namespace App\Features\WorkLogs\Policies;

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
        $isAssignedWorker = $workLog->workers()->where('workers.user_id', $user->id)->exists();
        return $this->hasPermission($user, 'complete', 'work_logs') || $isAssignedWorker;
    }

    public function approve(User $user, WorkLog $workLog): bool
    {
        // Only the mini-task supervisor or manager can approve
        $isSupervisor = $this->isOwner($user, $workLog->miniTask?->supervisor);
        return $this->hasPermission($user, 'approve', 'work_logs') || $isSupervisor || $this->isAdmin($user);
    }

    public function reject(User $user, WorkLog $workLog): bool
    {
        // Only the mini-task supervisor or manager can reject
        $isSupervisor = $this->isOwner($user, $workLog->miniTask?->supervisor);
        return $this->hasPermission($user, 'reject', 'work_logs') || $isSupervisor || $this->isAdmin($user);
    }
}
