<?php

namespace App\Console\Commands;

use App\Models\CompetitionRegistration;
use App\Models\District;
use App\Models\School;
use App\Support\SchoolLocation;
use Illuminate\Console\Command;

class BackfillSchoolDistrictIds extends Command
{
    protected $signature = 'schools:backfill-district-ids
                            {--dry-run : Report changes without writing}
                            {--sync-registrations : Sync draft/rejected competition registrations from schools}';

    protected $description = 'Backfill schools.district_id from region_id + district name, optionally sync open competition registrations';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $syncRegistrations = (bool) $this->option('sync-registrations');

        $matched = 0;
        $cleared = 0;
        $unmatched = 0;

        School::query()
            ->orderBy('id')
            ->chunkById(200, function ($schools) use ($dryRun, &$matched, &$cleared, &$unmatched): void {
                foreach ($schools as $school) {
                    if ($school->district_id) {
                        continue;
                    }

                    $districtId = District::findIdForRegionAndName(
                        $school->region_id ? (int) $school->region_id : null,
                        $school->district,
                    );

                    if ($districtId) {
                        $matched++;

                        if (! $dryRun) {
                            $school->update(['district_id' => $districtId]);
                        }

                        continue;
                    }

                    if (blank($school->district)) {
                        $cleared++;

                        continue;
                    }

                    $unmatched++;
                    $this->warn("Unmatched school #{$school->id} ({$school->name}): region_id={$school->region_id}, district=\"{$school->district}\"");
                }
            });

        $this->info("Schools matched: {$matched}, empty district: {$cleared}, unmatched: {$unmatched}");

        if ($syncRegistrations) {
            $updated = 0;

            CompetitionRegistration::query()
                ->whereIn('status', ['draft', 'rejected'])
                ->whereNotNull('school_id')
                ->with('school.districtModel')
                ->orderBy('id')
                ->chunkById(200, function ($registrations) use ($dryRun, &$updated): void {
                    foreach ($registrations as $registration) {
                        $school = $registration->school;
                        if (! $school) {
                            continue;
                        }

                        $location = SchoolLocation::registrationSnapshotFromSchool($school);

                        $payload = [
                            'region_id' => $location['region_id'],
                            'district_id' => $location['district_id'],
                            'school_district' => $location['school_district'],
                        ];

                        if (
                            (int) $registration->region_id !== (int) ($location['region_id'] ?? 0)
                            || (int) $registration->district_id !== (int) ($location['district_id'] ?? 0)
                            || (string) $registration->school_district !== (string) ($location['school_district'] ?? '')
                        ) {
                            $updated++;

                            if (! $dryRun) {
                                $registration->update($payload);
                            }
                        }
                    }
                });

            $this->info("Open competition registrations synced: {$updated}");
        }

        if ($dryRun) {
            $this->comment('Dry run only — no records were updated.');
        }

        return self::SUCCESS;
    }
}
