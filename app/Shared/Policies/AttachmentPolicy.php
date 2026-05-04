<?php

namespace App\Shared\Policies;

use App\Core\Policies\BasePolicy;
use App\Shared\Models\Attachment;
use App\Shared\Models\User;
use Illuminate\Http\Request;

class AttachmentPolicy extends BasePolicy
{
    public function create(User $user): bool
    {
        if (!$this->hasPermission($user, 'create', 'attachments')) {
            return false;
        }

        $request = request();
        $serviceOrderId = $request->input('service_order_id');
        $miniTaskId = $request->input('mini_task_id');

        if ($serviceOrderId) {
            $serviceOrder = \App\Features\ServiceOrders\Models\ServiceOrder::find($serviceOrderId);
            return $serviceOrder && $this->isManagerScoped($user, $serviceOrder->manager);
        }

        if ($miniTaskId) {
            $miniTask = \App\Features\MiniTasks\Models\MiniTask::find($miniTaskId);
            return $miniTask && $this->isOwner($user, $miniTask->supervisor);
        }

        return false;
    }

    public function delete(User $user, Attachment $attachment): bool
    {
        return $this->hasPermission($user, 'delete', 'attachments');
    }
}
