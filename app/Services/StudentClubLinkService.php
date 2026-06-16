<?php

namespace App\Services;

use App\DataTransferObjects\StudentClubMatch;
use App\Models\Student;
use App\Models\TeacherClub;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentClubLinkService
{
    public const GENERIC_NOT_FOUND_MESSAGE = "We couldn't find a club with those details. Check your admission number and club code, then try again.";

    public function __construct(
        private readonly ShortCodeGenerator $codeGenerator,
    ) {}

    public function normalizeCode(string $code): string
    {
        return strtoupper(trim($code));
    }

    public function lookup(string $admissionNumber, string $clubCode): ?StudentClubMatch
    {
        $admissionNumber = $this->normalizeCode($admissionNumber);
        $clubCode = $this->normalizeCode($clubCode);

        if (! $this->codeGenerator->isValidFormat($admissionNumber)
            || ! $this->codeGenerator->isValidFormat($clubCode)) {
            return null;
        }

        $student = Student::query()
            ->with([
                'schoolClass',
                'teacherClub.club',
                'teacherClub.school.regionModel',
            ])
            ->where('admission_number', $admissionNumber)
            ->first();

        if (! $student || ! $student->teacherClub) {
            return null;
        }

        $teacherClub = $student->teacherClub;

        if ($teacherClub->status !== 'approved') {
            return null;
        }

        if ($this->normalizeCode((string) $teacherClub->club_code) !== $clubCode) {
            return null;
        }

        $leader = $this->resolveLeader($teacherClub);

        return new StudentClubMatch(
            student: $student,
            club: $teacherClub->club,
            school: $teacherClub->school,
            leader: $leader,
            clubCode: $clubCode,
        );
    }

    public function link(User $user, string $admissionNumber, string $clubCode): Student
    {
        if ($user->role !== 'student') {
            throw ValidationException::withMessages([
                'admission_number' => self::GENERIC_NOT_FOUND_MESSAGE,
            ]);
        }

        if ($user->isClubLinked()) {
            throw ValidationException::withMessages([
                'admission_number' => 'Your account is already linked to a school club.',
            ]);
        }

        $match = $this->lookup($admissionNumber, $clubCode);

        if (! $match) {
            throw ValidationException::withMessages([
                'admission_number' => self::GENERIC_NOT_FOUND_MESSAGE,
            ]);
        }

        $student = $match->student;

        if ($student->isLinked() && $student->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'admission_number' => self::GENERIC_NOT_FOUND_MESSAGE,
            ]);
        }

        return DB::transaction(function () use ($user, $student) {
            $linkedAt = now();

            $student->forceFill([
                'user_id' => $user->id,
                'linked_at' => $linkedAt,
            ])->save();

            $user->forceFill([
                'subscription_tier' => 'public_school',
                'club_linked_at' => $linkedAt,
            ])->save();

            return $student->fresh([
                'schoolClass',
                'teacherClub.club',
                'teacherClub.school.regionModel',
            ]);
        });
    }

    public function resolveMatchForUser(User $user): ?StudentClubMatch
    {
        $student = $user->linkedRosterStudent()
            ->with([
                'schoolClass',
                'teacherClub.club',
                'teacherClub.school.regionModel',
            ])
            ->first();

        if (! $student || ! $student->teacherClub || $student->teacherClub->status !== 'approved') {
            return null;
        }

        return new StudentClubMatch(
            student: $student,
            club: $student->teacherClub->club,
            school: $student->teacherClub->school,
            leader: $this->resolveLeader($student->teacherClub),
            clubCode: $this->normalizeCode((string) $student->teacherClub->club_code),
        );
    }

    private function resolveLeader(TeacherClub $teacherClub): ?User
    {
        $query = TeacherClub::query()
            ->with('user')
            ->where('club_id', $teacherClub->club_id)
            ->where('status', 'approved')
            ->where('is_leader', true);

        if ($teacherClub->school_id === null) {
            $query->whereNull('school_id');
        } else {
            $query->where('school_id', $teacherClub->school_id);
        }

        return $query->first()?->user;
    }
}
