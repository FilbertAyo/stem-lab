<?php

namespace App\Services;

use App\Models\Competition;
use App\Models\CompetitionRegistration;
use App\Models\TeacherClub;
use Illuminate\Support\Collection;

class CompetitionParticipantRegistrationService
{
    /**
     * Ensure a draft competition registration exists for follow-up / admin participant list.
     */
    public function ensureDraftRegistration(TeacherClub $teacherClub, Competition $competition): ?CompetitionRegistration
    {
        $teacherClub->loadMissing(['user', 'school']);

        $user = $teacherClub->user;
        $school = $teacherClub->school;

        $existingRegistration = CompetitionRegistration::query()
            ->where('competition_id', $competition->id)
            ->where('teacher_club_id', $teacherClub->id)
            ->first();

        // Legacy teacher_club records may not have school/user attached.
        // Creating a draft in that state throws DB errors (required FKs in registrations table).
        if (! $teacherClub->school_id || ! $teacherClub->user_id) {
            return $existingRegistration;
        }

        $attributes = [
            'teacher_user_id' => $teacherClub->user_id,
            'school_id' => $teacherClub->school_id,
            'region_id' => $school?->region_id,
            'status' => 'draft',
            'teacher_name' => $user?->name ?? '',
            'teacher_email' => $user?->email ?? '',
            'teacher_phone' => $user?->phone,
            'school_name' => $school?->name ?? 'Unknown school',
            'school_district' => $school?->district,
            'school_ward' => $school?->ward,
            'student_count_snapshot' => 0,
            'girls_count_snapshot' => 0,
            'girls_ratio_snapshot' => 0,
            'eligibility_met' => false,
            'submitted_at' => null,
        ];

        $registration = CompetitionRegistration::query()->firstOrCreate(
            [
                'competition_id' => $competition->id,
                'teacher_club_id' => $teacherClub->id,
            ],
            $attributes,
        );

        if ($registration->wasRecentlyCreated) {
            return $registration;
        }

        if (! in_array($registration->status, ['draft', 'rejected'], true)) {
            return $registration;
        }

        $registration->fill([
            'teacher_user_id' => $attributes['teacher_user_id'],
            'school_id' => $attributes['school_id'],
            'region_id' => $attributes['region_id'],
            'teacher_name' => $attributes['teacher_name'],
            'teacher_email' => $attributes['teacher_email'],
            'teacher_phone' => $attributes['teacher_phone'],
            'school_name' => $attributes['school_name'],
            'school_district' => $attributes['school_district'],
            'school_ward' => $attributes['school_ward'],
        ]);

        if ($registration->isDirty()) {
            $registration->save();
        }

        return $registration;
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
