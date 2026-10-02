<?php

namespace App\Console\Commands;

use App\Services\AdminAttentionDigestService;
use Illuminate\Console\Command;

class SendAdminAttentionDigestCommand extends Command
{
    protected $signature = 'admin:send-attention-digest';

    protected $description = 'Email category digests for admin queues that still need attention';

    public function handle(AdminAttentionDigestService $digestService): int
    {
        $sent = $digestService->send();

        if ($sent === []) {
            $this->info('No backlog digests to send.');

            return self::SUCCESS;
        }

        foreach ($sent as $category => $ok) {
            $this->line(($ok ? 'Sent' : 'Failed')." digest for [{$category}]");
        }

        return self::SUCCESS;
    }
}
