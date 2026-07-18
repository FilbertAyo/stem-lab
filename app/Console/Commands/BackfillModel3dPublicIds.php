<?php

namespace App\Console\Commands;

use App\Models\Model3d;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillModel3dPublicIds extends Command
{
    protected $signature = 'model3ds:backfill-public-ids
                            {--dry-run : Report how many rows need a public_id without writing}
                            {--verify : Only report remaining nulls and duplicate counts}';

    protected $description = 'Backfill six-character lowercase public_id values for existing 3D models';

    public function handle(): int
    {
        if ($this->option('verify')) {
            return $this->verify();
        }

        $missing = Model3d::query()->whereNull('public_id')->count();

        if ($this->option('dry-run')) {
            $this->info("Dry run: {$missing} model3d row(s) are missing public_id.");

            return self::SUCCESS;
        }

        if ($missing === 0) {
            $this->info('All model3d rows already have a public_id.');

            return $this->verify();
        }

        $assigned = 0;

        Model3d::query()
            ->whereNull('public_id')
            ->orderBy('id')
            ->chunkById(100, function ($models) use (&$assigned): void {
                foreach ($models as $model) {
                    if ($model->assignPublicIdIfMissing()) {
                        $assigned++;
                    }
                }
            });

        $this->info("Assigned public_id to {$assigned} model3d row(s).");

        return $this->verify();
    }

    protected function verify(): int
    {
        $total = Model3d::query()->count();
        $missing = Model3d::query()->whereNull('public_id')->count();
        $duplicates = (int) DB::table('model3ds')
            ->select('public_id')
            ->whereNotNull('public_id')
            ->groupBy('public_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total model3ds', $total],
                ['Missing public_id', $missing],
                ['Duplicate public_id groups', $duplicates],
            ]
        );

        if ($missing > 0 || $duplicates > 0) {
            $this->warn('Verification found issues that need attention.');

            return self::FAILURE;
        }

        $this->info('Verification passed: every model3d has a unique public_id.');

        return self::SUCCESS;
    }
}
