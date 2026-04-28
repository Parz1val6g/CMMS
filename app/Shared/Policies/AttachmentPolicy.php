<?php

namespace App\Shared\Policies;

use App\Core\Policies\BasePolicy;
use App\Shared\Models\Attachment;
use App\Shared\Models\User;

class AttachmentPolicy extends BasePolicy
{
    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create', 'attachments');
    }

    public function delete(User $user, Attachment $attachment): bool
    {
        return $this->hasPermission($user, 'delete', 'attachments');
    }
}
