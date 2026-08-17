<?php

namespace App\Services;

use App\Models\Competition;
use App\Models\CompetitionRegistration;
use App\Models\TeacherClub;
use App\Support\SchoolLocation;
use Illuminate\Support\Collection;

class CompetitionParticipantRegistrationService
{
    /**
     * Ensure a draft competition registration exists for this school + competition.
     * Co-teachers at the same school share one registration row.
     */
    public function ensureDraftRegistration(TeacherClub $teacherClub, Competition $competition): CompetitionRegistration
    {
        $teacherClub->loadMissing(['user', 'school']);

        $schoolId = $teacherClub->school_id;
        if ($schoolId === null) {
            return $this->createOrRefreshForTeacherClub($teacherClub, $competition);
        }

        $existing = $this->findForSchool($competition, (int) $schoolId);
        if ($existing) {
            return $this->refreshOpenContactSnapshot($existing, $teacherClub);
        }

        return $this->createOrRefreshForTeacherClub($teacherClub, $competition);
    }

    public function findForSchool(Competition $competition, int $schoolId): ?CompetitionRegistration
    {
        return $this->preferRegistration(
            CompetitionRegistration::query()
                ->where('competition_id', $competition->id)
                ->where('school_id', $schoolId)
                ->get()
        );
    }

    public function findForTeacherClub(Competition $competition, TeacherClub $teacherClub): ?CompetitionRegistration
    {
        $coMemberIds = [];
        if ($teacherClub->school_id !== null) {
            $coMemberIds = app(TeacherClubSharedContextService::class)->coMemberIds($teacherClub);
        }

        return $this->preferRegistration(
            CompetitionRegistration::query()
                ->where('competition_id', $competition->id)
                ->where(function ($query) use ($teacherClub, $coMemberIds): void {
                    $query->where('teacher_club_id', $teacherClub->id);

                    if ($teacherClub->school_id !== null) {
                        $query->orWhere('school_id', (int) $teacherClub->school_id);
                    }

                    if ($coMemberIds !== []) {
                        $query->orWhereIn('teacher_club_id', $coMemberIds);
                    }
                })
                ->get()
        );
    }

    /**
     * Whether this membership may edit the school registration (draft/rejected only).
     * Any approved co-teacher at the same school+club may continue an open draft.
     */
    public function canEditRegistration(CompetitionRegistration $registration, TeacherClub $teacherClub): bool
    {
        if (! in_array($registration->status, ['draft', 'rejected'], true)) {
            return false;
        }

        if ((int) $registration->teacher_club_id === (int) $teacherClub->id) {
            return true;
        }

        if ($teacherClub->school_id === null || $registration->school_id === null) {
            return false;
        }

        if ((int) $teacherClub->school_id !== (int) $registration->school_id) {
            return false;
        }

        $registration->loadMissing('teacherClub');
        $registrationClubId = (int) ($registration->teacherClub?->club_id ?? 0);
        if ($registrationClubId !== 0 && $registrationClubId !== (int) $teacherClub->club_id) {
            return false;
        }

        return $teacherClub->status === 'approved';
    }

    /**
     * Registrations visible to this teacher across competitions (school-scoped).
     *
     * @return Collection<int, CompetitionRegistration> keyed by competition_id
     */
    public function registrationsForTeacher(TeacherClub ...$teacherClubs): Collection
    {
        $schoolIds = collect($teacherClubs)
            ->pluck('school_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $teacherClubIds = collect($teacherClubs)->pluck('id')->map(fn ($id) => (int) $id)->all();

        if ($schoolIds === [] && $teacherClubIds === []) {
            return collect();
        }

        return CompetitionRegistration::query()
            ->with([
                'competition',
                'idea.shortlist',
                'prototypeSubmission.prototypeShortlist',
                'prototypeModification',
            ])
            ->where(function ($query) use ($schoolIds, $teacherClubIds): void {
                if ($schoolIds !== []) {
                    $query->whereIn('school_id', $schoolIds);
                }
                if ($teacherClubIds !== []) {
                    $query->orWhereIn('teacher_club_id', $teacherClubIds);
                }
            })
            ->get()
            ->groupBy(fn (CompetitionRegistration $registration): int => (int) $registration->competition_id)
            ->map(fn (Collection $group) => $this->preferRegistration($group))
            ->filter()
            ->keyBy(fn (CompetitionRegistration $registration): int => (int) $registration->competition_id);
    }

    /**
     * When duplicate rows exist for one school/competition, keep the furthest-along registration.
     *
     * @param  Collection<int, CompetitionRegistration>  $registrations
     */
    private function preferRegistration(Collection $registrations): ?CompetitionRegistration
    {
        if ($registrations->isEmpty()) {
            return null;
        }

        return $registrations
            ->sortByDesc(function (CompetitionRegistration $registration): array {
                $statusRank = match ($registration->status) {
                    'approved' => 50,
                    'pending' => 40,
                    'draft' => 20,
                    'rejected' => 10,
                    default => 0,
                };

                return [$statusRank, (int) $registration->id];
            })
            ->first();
    }

    /**
     * @param  Competition|null  $only  When set (e.g. explore signup), ensure only this competition.
     */
    public function ensureForClubMembership(TeacherClub $teacherClub, ?Competition $only = null): void
    {
        $competitions = $this->competitionsForClub($teacherClub, $only);

        foreach ($competitions as $competition) {
            $this->ensureDraftRegistration($teacherClub, $competition);
        }
    }

    private function createOrRefreshForTeacherClub(
        TeacherClub $teacherClub,
        Competition $competition,
    ): CompetitionRegistration {
        $teacherClub->loadMissing(['user', 'school']);

        $user = $teacherClub->user;
        $school = $teacherClub->school;

        $location = SchoolLocation::registrationSnapshotFromSchool($school);

        $attributes = [
            'teacher_club_id' => $teacherClub->id,
            'teacher_user_id' => $teacherClub->user_id,
            'school_id' => $teacherClub->school_id,
            'region_id' => $location['region_id'],
            'district_id' => $location['district_id'],
            'status' => 'draft',
            'teacher_name' => $user?->name ?? '',
            'teacher_email' => $user?->email ?? '',
            'teacher_phone' => $user?->phone,
            'school_name' => $school?->name ?? 'Unknown school',
            'school_district' => $location['school_district'],
            'school_ward' => $school?->ward,
            'student_count_snapshot' => 0,
            'girls_count_snapshot' => 0,
            'girls_ratio_snapshot' => 0,
            'eligibility_met' => false,
            'submitted_at' => null,
        ];

        // Prefer school-scoped create when school_id is present.
        if ($teacherClub->school_id !== null) {
            return CompetitionRegistration::query()->firstOrCreate(
                [
                    'competition_id' => $competition->id,
                    'school_id' => $teacherClub->school_id,
                ],
                $attributes,
            );
        }

        return CompetitionRegistration::query()->firstOrCreate(
            [
                'competition_id' => $competition->id,
                'teacher_club_id' => $teacherClub->id,
            ],
            $attributes,
        );
    }

    private function refreshOpenContactSnapshot(
        CompetitionRegistration $registration,
        TeacherClub $teacherClub,
    ): CompetitionRegistration {
        if (! in_array($registration->status, ['draft', 'rejected'], true)) {
            return $registration;
        }

        // Keep original contact for an established draft; only fill gaps if empty.
        $teacherClub->loadMissing(['user', 'school']);
        $user = $teacherClub->user;
        $school = $teacherClub->school;

        $location = SchoolLocation::registrationSnapshotFromSchool($school);

        $updates = [
            'school_id' => $teacherClub->school_id ?? $registration->school_id,
            'region_id' => $location['region_id'] ?? $registration->region_id,
            'district_id' => $location['district_id'] ?? $registration->district_id,
            'school_name' => $school?->name ?: $registration->school_name,
            'school_district' => $location['school_district'] ?? $registration->school_district,
            'school_ward' => $school?->ward ?? $registration->school_ward,
        ];

        if (! filled($registration->teacher_name) && $user) {
            $updates['teacher_user_id'] = $teacherClub->user_id;
            $updates['teacher_club_id'] = $teacherClub->id;
            $updates['teacher_name'] = $user->name ?? '';
            $updates['teacher_email'] = $user->email ?? '';
            $updates['teacher_phone'] = $user->phone;
        }

        $registration->fill($updates);

        if ($registration->isDirty()) {
            $registration->save();
        }

        return $registration;
    }

    /**
     * @return Collection<int, Competition>
     */
    private function competitionsForClub(TeacherClub $teacherClub, ?Competition $only): Collection
    {
        if ($only !== null && (int) $only->club_id === (int) $teacherClub->club_id) {
            return collect([$only]);
        }

        if ($only !== null) {
            return collect();
        }

        return Competition::query()
            ->active()
            ->published()
            ->where('club_id', $teacherClub->club_id)
            ->get();
    }
}
