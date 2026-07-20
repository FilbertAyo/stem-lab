<?php

namespace App\Support;

use App\Models\District;
use App\Models\School;

class SchoolLocation
{
    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public static function normalizeSchoolAttributes(array $validated): array
    {
        $districtId = isset($validated['district_id']) && $validated['district_id'] !== ''
            ? (int) $validated['district_id']
            : null;

        if (! array_key_exists('district_id', $validated)) {
            return $validated;
        }

        if ($districtId) {
            $district = District::query()->find($districtId);
            $validated['district_id'] = $district?->id;
            $validated['district'] = $district?->name;

            if ($district && empty($validated['region_id'])) {
                $validated['region_id'] = $district->region_id;
            }
        } else {
            $validated['district_id'] = null;
            $validated['district'] = null;
        }

        return $validated;
    }

    /**
     * @return array{region_id: int|null, district_id: int|null, school_district: string|null}
     */
    public static function registrationSnapshotFromSchool(?School $school): array
    {
        $school?->loadMissing('districtModel');

        return [
            'region_id' => $school?->region_id,
            'district_id' => $school?->district_id,
            'school_district' => $school?->districtName(),
        ];
    }
}
