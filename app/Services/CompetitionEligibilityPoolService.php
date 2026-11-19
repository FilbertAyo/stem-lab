<?php

namespace App\Services;

use App\Models\Competition;
use App\Models\CompetitionRegistration;
use App\Models\Student;
use App\Models\TeacherClub;
use App\Support\SchoolLocation;

class CompetitionEligibilityPoolService
{
    /**
     * Live competition pool for a school club membership (shared co-teacher roster).
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
        $allowedClassIds = $this->allowedClassIds($competition);

        $teacherClubIds = $this->coMemberTeacherClubIds($teacherClub);

        $students = Student::query()
            ->with('schoolClass')
            ->whereIn('teacher_club_id', $teacherClubIds)
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
     * Count-only pool summary for list pages. Does not load student rows.
     *
     * @return array{count: int, girls_count: int, girls_ratio: float, eligible: bool}
     */
    public function summarizePool(Competition $competition, TeacherClub $teacherClub): array
    {
        $allowedClassIds = $this->allowedClassIds($competition);
        $teacherClubIds = $this->coMemberTeacherClubIds($teacherClub);

        $row = Student::query()
            ->whereIn('teacher_club_id', $teacherClubIds)
            ->when($allowedClassIds !== [], fn ($query) => $query->whereIn('school_class_id', $allowedClassIds))
            ->selectRaw('COUNT(*) as pool_count, SUM(CASE WHEN gender = ? THEN 1 ELSE 0 END) as girls_count', ['female'])
            ->first();

        $count = (int) ($row->pool_count ?? 0);
        $girlsCount = (int) ($row->girls_count ?? 0);
        $girlsRatio = $count > 0 ? ($girlsCount / $count) * 100 : 0.0;
        $error = $this->eligibilityError($competition, $count, $girlsRatio);

        return [
            'count' => $count,
            'girls_count' => $girlsCount,
            'girls_ratio' => round($girlsRatio, 2),
            'eligible' => $error === null,
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
            ->with(['competition.classRequirements', 'teacherClub'])
            ->whereIn('status', ['draft', 'rejected'])
            ->where(function ($query) use ($teacherClub): void {
                $query->where('teacher_club_id', $teacherClub->id);
                if ($teacherClub->school_id !== null) {
                    $query->orWhere('school_id', $teacherClub->school_id);
                }
            })
            ->get()
            ->unique('id');

        foreach ($registrations as $registration) {
            $competition = $registration->competition;
            if (! $competition) {
                continue;
            }

            // Prefer the registration's primary membership for contact snapshot when promoting.
            $poolTeacherClub = $registration->teacherClub && (int) $registration->teacherClub->school_id === (int) $teacherClub->school_id
                ? $registration->teacherClub
                : $teacherClub;

            $this->applyPoolToOpenRegistration(
                $registration,
                $poolTeacherClub,
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

            $location = SchoolLocation::registrationSnapshotFromSchool($school);

            $attributes = array_merge($attributes, [
                'status' => 'pending',
                'submitted_at' => $registration->submitted_at ?? now(),
                'rejection_reason' => null,
                'teacher_club_id' => $registration->teacher_club_id ?: $teacherClub->id,
                'teacher_user_id' => $registration->teacher_user_id ?: $teacherClub->user_id,
                'school_id' => $teacherClub->school_id,
                'region_id' => $location['region_id'] ?? $registration->region_id,
                'district_id' => $location['district_id'] ?? $registration->district_id,
                'teacher_name' => $registration->teacher_name ?: ($user?->name ?: ''),
                'teacher_email' => $registration->teacher_email ?: ($user?->email ?: ''),
                'teacher_phone' => $registration->teacher_phone ?: $user?->phone,
                'school_name' => $school?->name ?: $registration->school_name,
                'school_district' => $location['school_district'] ?? $registration->school_district,
                'school_ward' => $school?->ward ?? $registration->school_ward,
            ]);
        }

        $nextStatus = (string) ($attributes['status'] ?? $registration->status);
        $unchanged = (int) $registration->student_count_snapshot === (int) $attributes['student_count_snapshot']
            && (int) $registration->girls_count_snapshot === (int) $attributes['girls_count_snapshot']
            && round((float) $registration->girls_ratio_snapshot, 2) === round((float) $attributes['girls_ratio_snapshot'], 2)
            && (bool) $registration->eligibility_met === (bool) $attributes['eligibility_met']
            && $registration->status === $nextStatus;

        if ($unchanged) {
            return;
        }

        $registration->forceFill($attributes)->save();
    }

    /**
     * @return list<int>
     */
    private function allowedClassIds(Competition $competition): array
    {
        $competition->loadMissing('classRequirements');

        return $competition->classRequirements
            ->pluck('school_class_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return list<int>
     */
    public function coMemberTeacherClubIds(TeacherClub $teacherClub): array
    {
        $query = TeacherClub::query()
            ->where('club_id', $teacherClub->club_id)
            ->approved();

        if ($teacherClub->school_id === null) {
            $query->whereNull('school_id');
        } else {
            $query->where('school_id', $teacherClub->school_id);
        }

        $ids = $query->pluck('id')->map(fn ($id) => (int) $id)->all();

        if ($ids === []) {
            return [(int) $teacherClub->id];
        }

        return $ids;
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
