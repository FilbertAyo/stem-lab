<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * @deprecated Prefer `php artisan public-ids:backfill model3ds`.
 */
class BackfillModel3dPublicIds extends Command
{
    protected $signature = 'model3ds:backfill-public-ids
                            {--dry-run : Report how many rows need a public_id without writing}
                            {--verify : Only report remaining nulls and duplicate counts}';

    protected $description = 'Backfill six-character lowercase public_id values for existing 3D models';

    public function handle(): int
    {
        $options = [];

        if ($this->option('dry-run')) {
            $options['--dry-run'] = true;
        }

        if ($this->option('verify')) {
            $options['--verify'] = true;
        }

        return $this->call('public-ids:backfill', array_merge([
            'resource' => 'model3ds',
        ], $options));
    }
}
