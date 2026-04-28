<?php
namespace App\Features\Clients\Listeners;
use App\Features\Admin\Events\UserCreatedEvent;
use App\Features\Clients\Models\Client;
class CreateClientProfile
{
    public function handle(UserCreatedEvent $event): void
    {
        $user = $event->user;
        // Check if the user has a Role named 'Client'
        $isClient = $user->roles()->where('name', 'Client')->exists();
        if ($isClient && !$user->clientProfile()->exists()) {
            Client::create(['user_id' => $user->id]);
        }
    }
}