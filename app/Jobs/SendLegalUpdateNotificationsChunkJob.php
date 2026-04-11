<?php

namespace App\Jobs;

use App\Mail\LegalDocumentsUpdatedMail;
use App\Models\User;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendLegalUpdateNotificationsChunkJob implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * @param  list<int>  $userIds
     */
    public function __construct(
        public array $userIds,
    ) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $users = User::query()
            ->whereIn('id', $this->userIds)
            ->where('role', '!=', 'admin')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        foreach ($users as $user) {
            Mail::to($user->email)->send(new LegalDocumentsUpdatedMail($user));
        }
    }
}
