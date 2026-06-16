<?php

namespace App\Services;

use App\Models\TeacherClub;
use Illuminate\Support\Facades\DB;

class TeacherClubCodeService
{
    public function __construct(
        private readonly ShortCodeGenerator $codeGenerator,
    ) {}

    public function resolveForMembership(TeacherClub $membership): string
    {
        if ($membership->club_id !== null && $membership->school_id !== null) {
            $existingCode = TeacherClub::query()
                ->where('club_id', $membership->club_id)
                ->where('school_id', $membership->school_id)
                ->whereNotNull('club_code')
                ->value('club_code');

            if (is_string($existingCode) && $existingCode !== '') {
                return $existingCode;
            }
        }

        return $this->generateUniqueClubCode(
            $membership->club_id !== null ? (int) $membership->club_id : null,
            $membership->school_id !== null ? (int) $membership->school_id : null,
        );
    }

    public function generateUniqueClubCode(?int $clubId, ?int $schoolId): string
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $code = $this->codeGenerator->randomCode();

            if ($this->isCodeAvailableForGroup($code, $clubId, $schoolId)) {
                return $code;
            }
        }

        throw new \RuntimeException('Unable to generate a unique club code after multiple attempts.');
    }

    /**
     * A club code may be shared by all teacher_clubs in the same school+club group,
     * but must not belong to a different school+club group.
     */
    public function isCodeAvailableForGroup(string $code, ?int $clubId, ?int $schoolId): bool
    {
        $query = DB::table('teacher_clubs')->where('club_code', $code);

        if ($clubId === null || $schoolId === null) {
            return ! $query->exists();
        }

        $conflict = $query
            ->where(function ($builder) use ($clubId, $schoolId) {
                $builder->where('club_id', '!=', $clubId)
                    ->orWhere('school_id', '!=', $schoolId)
                    ->orWhereNull('club_id')
                    ->orWhereNull('school_id');
            })
            ->exists();

        return ! $conflict;
    }

    /**
     * Backfill club codes grouped by school+club.
     *
     * @return array<int, string> Map of generated codes keyed by group index.
     */
    public function backfillAllGroups(): int
    {
        $groups = DB::table('teacher_clubs')
            ->select('club_id', 'school_id')
            ->whereNull('club_code')
            ->whereNotNull('club_id')
            ->whereNotNull('school_id')
            ->groupBy('club_id', 'school_id')
            ->get();

        $updated = 0;

        foreach ($groups as $group) {
            $code = $this->generateUniqueClubCode(
                $group->club_id !== null ? (int) $group->club_id : null,
                $group->school_id !== null ? (int) $group->school_id : null,
            );

            $query = DB::table('teacher_clubs')->whereNull('club_code');

            if ($group->club_id === null) {
                $query->whereNull('club_id');
            } else {
                $query->where('club_id', $group->club_id);
            }

            if ($group->school_id === null) {
                $query->whereNull('school_id');
            } else {
                $query->where('school_id', $group->school_id);
            }

            $updated += $query->update(['club_code' => $code]);
        }

        return $updated;
    }
}
