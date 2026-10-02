<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\AdminActivityNotifier;
use Illuminate\Auth\Events\Registered;

class NotifyAdminsOfNewUser
{
    public function __construct(
        private readonly AdminActivityNotifier $adminActivity,
    ) {}

    public function handle(Registered $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        $this->adminActivity->newUser($user);
    }
}
