<?php

namespace App\Listeners;

use App\Mail\WelcomeEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail
{
    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        if (blank($event->user->email)) {
            return;
        }

        Mail::to($event->user->email)->send(new WelcomeEmail($event->user));
    }
}
