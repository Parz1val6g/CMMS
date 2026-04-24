<?php
namespace App\Features\Admin\Events;
use App\Shared\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
class UserCreatedEvent
{
    use Dispatchable;
    public function __construct(public User $user)
    {
    }
}