<?php

namespace App\Console\Commands;

use App\Services\CompetitionRegistrationSchoolSyncService;
use Illuminate\Console\Command;

class SyncCompetitionRegistrationSchoolSnapshots extends Command
{
    protected $signature = 'competitions:sync-registration-school-snapshots
                            {--dry-run : Report how many schools would be synced without writing}';

    protected $description = 'Refresh competition registration school snapshots from the linked schools table';

    public function handle(CompetitionRegistrationSchoolSyncService $syncService): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $schoolCount = \App\Models\School::query()
                ->whereHas('competitionRegistrations')
                ->count();

            $registrationCount = \App\Models\CompetitionRegistration::query()
                ->whereNotNull('school_id')
                ->count();

            $this->info("Would sync {$registrationCount} registration(s) across {$schoolCount} school(s).");

            return self::SUCCESS;
        }

        $updated = $syncService->syncAll();

        $this->info("Updated {$updated} competition registration snapshot(s).");

        return self::SUCCESS;
    }
}
