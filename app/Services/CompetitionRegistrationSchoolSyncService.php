<?php

namespace App\Services;

use App\Models\CompetitionRegistration;
use App\Models\School;
use App\Support\SchoolLocation;

class CompetitionRegistrationSchoolSyncService
{
    /**
     * Refresh denormalized school snapshot fields on all registrations for this school.
     *
     * @return int Number of registration rows updated
     */
    public function syncFromSchool(School $school): int
    {
        $location = SchoolLocation::registrationSnapshotFromSchool($school);

        return CompetitionRegistration::query()
            ->where('school_id', $school->id)
            ->update([
                'school_name' => $school->name,
                'school_ward' => $school->ward,
                'region_id' => $location['region_id'],
                'district_id' => $location['district_id'],
                'school_district' => $location['school_district'],
            ]);
    }

    /**
     * Sync snapshots for every school that has at least one competition registration.
     *
     * @return int Total registration rows updated
     */
    public function syncAll(): int
    {
        $updated = 0;

        School::query()
            ->whereHas('competitionRegistrations')
            ->with('districtModel')
            ->orderBy('id')
            ->chunkById(100, function ($schools) use (&$updated): void {
                foreach ($schools as $school) {
                    $updated += $this->syncFromSchool($school);
                }
            });

        return $updated;
    }
}
