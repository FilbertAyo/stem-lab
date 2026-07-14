<?php

namespace App\Services;

use App\Models\Competition;
use App\Models\CompetitionRegistration;
use App\Models\TeacherClub;
use Illuminate\Support\Collection;

class CompetitionEligibilityPoolService
{
    /**
     * Live competition pool for a teacher club membership (same rules as teacher register UI).
     *
     * @return array{
     *     students: Collection<int, \App\Models\Student>,
     *     count: int,
     *     girls_count: int,
     *     girls_ratio: float,
     *     eligible: bool,
     *     error: string|null,
     *     min: int,
     *     max: int|null,
     *     allowed_class_ids: list<int>
     * }
     */
    public function computePool(Competition $competition, TeacherClub $teacherClub): array
    {
        $allowedClassIds = $competition->classRequirements()
            ->pluck('school_class_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $students = $teacherClub->students()
            ->with('schoolClass')
            ->when(! empty($allowedClassIds), fn ($query) => $query->whereIn('school_class_id', $allowedClassIds))
            ->orderBy('name')
            ->get();

        $count = $students->count();
        $girlsCount = $students->where('gender', 'female')->count();
        $girlsRatio = $count > 0 ? ($girlsCount / $count) * 100 : 0.0;
        $bounds = $this->poolBounds($competition);
        $error = $this->eligibilityError($competition, $count, $girlsRatio);

        return [
            'students' => $students,
            'count' => $count,
            'girls_count' => $girlsCount,
            'girls_ratio' => round($girlsRatio, 2),
            'eligible' => $error === null,
            'error' => $error,
            'min' => $bounds['min'],
            'max' => $bounds['max'],
            'allowed_class_ids' => $allowedClassIds,
        ];
    }

    /**
     * Keep draft/rejected registration eligibility fields in sync with the live student pool.
     * When the pool passes eligibility, promote to pending (same outcome as teacher submit).
     * Submitted/approved registrations keep their immutable submission snapshot.
     */
    public function refreshOpenRegistrationSnapshots(TeacherClub $teacherClub): void
    {
        $teacherClub->loadMissing(['user', 'school']);

        $registrations = CompetitionRegistration::query()
            ->with('competition.classRequirements')
            ->where('teacher_club_id', $teacherClub->id)
            ->whereIn('status', ['draft', 'rejected'])
            ->get();

        foreach ($registrations as $registration) {
            $competition = $registration->competition;
            if (! $competition) {
                continue;
            }

            $this->applyPoolToOpenRegistration(
                $registration,
                $teacherClub,
                $this->computePool($competition, $teacherClub),
            );
        }
    }

    /**
     * @param  array{
     *     count: int,
     *     girls_count: int,
     *     girls_ratio: float,
     *     eligible: bool
     * }  $pool
     */
    public function applyPoolToOpenRegistration(
        CompetitionRegistration $registration,
        TeacherClub $teacherClub,
        array $pool,
    ): void {
        if (! in_array($registration->status, ['draft', 'rejected'], true)) {
            return;
        }

        $teacherClub->loadMissing(['user', 'school']);

        $attributes = [
            'student_count_snapshot' => $pool['count'],
            'girls_count_snapshot' => $pool['girls_count'],
            'girls_ratio_snapshot' => $pool['girls_ratio'],
            'eligibility_met' => $pool['eligible'],
        ];

        if ($pool['eligible']) {
            $user = $teacherClub->user;
            $school = $teacherClub->school;

            $attributes = array_merge($attributes, [
                'status' => 'pending',
                'submitted_at' => $registration->submitted_at ?? now(),
                'rejection_reason' => null,
                'school_id' => $teacherClub->school_id,
                'region_id' => $school?->region_id ?? $registration->region_id,
                'teacher_name' => $user?->name ?: $registration->teacher_name,
                'teacher_email' => $user?->email ?: $registration->teacher_email,
                'teacher_phone' => $user?->phone ?: $registration->teacher_phone,
                'school_name' => $school?->name ?: $registration->school_name,
                'school_district' => $school?->district ?? $registration->school_district,
                'school_ward' => $school?->ward ?? $registration->school_ward,
            ]);
        }

        $registration->forceFill($attributes)->save();
    }

    /**
     * @return array{min: int, max: int|null}
     */
    public function poolBounds(Competition $competition): array
    {
        return [
            'min' => max(1, (int) $competition->min_members),
            'max' => $competition->max_members !== null ? (int) $competition->max_members : null,
        ];
    }

    public function eligibilityError(Competition $competition, int $poolCount, float $poolRatio): ?string
    {
        $bounds = $this->poolBounds($competition);

        if ($poolCount < $bounds['min']) {
            return "Eligibility gate failed. You need at least {$bounds['min']} students in the competition class pool (set by the competition Min members field). Upload members via CSV if needed.";
        }

        if ($bounds['max'] !== null && $poolCount > $bounds['max']) {
            return "Eligibility gate failed. The pool may not exceed {$bounds['max']} students (competition Max members).";
        }

        if ($poolRatio < 70) {
            return 'Eligibility gate failed. At least 70% of the pool must be girls.';
        }

        return null;
    }
}
