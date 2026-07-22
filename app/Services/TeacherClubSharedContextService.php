<?php

namespace App\Services;

use App\Models\TeacherClub;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class TeacherClubSharedContextService
{
    /**
     * Approved memberships for the same club + school (shared day & roster).
     *
     * @return Collection<int, TeacherClub>
     */
    public function coMemberMemberships(TeacherClub $membership): Collection
    {
        return $this->coMemberQuery($membership)->get();
    }

    /**
     * @return list<int>
     */
    public function coMemberIds(TeacherClub $membership): array
    {
        $ids = $this->coMemberQuery($membership)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($ids === []) {
            return [(int) $membership->id];
        }

        return $ids;
    }

    /**
     * Students for a school club belong on the leader membership when one exists.
     */
    public function resolveRosterOwner(TeacherClub $membership): TeacherClub
    {
        $leader = $this->coMemberQuery($membership)
            ->where('is_leader', true)
            ->orderBy('id')
            ->first();

        return $leader ?? $membership;
    }

    public function sharedClubDay(TeacherClub $membership): ?string
    {
        $owner = $this->resolveRosterOwner($membership);

        if (filled($owner->club_day)) {
            return $owner->club_day;
        }

        $withDay = $this->coMemberQuery($membership)
            ->whereNotNull('club_day')
            ->orderByDesc('is_leader')
            ->orderBy('id')
            ->first();

        return $withDay?->club_day;
    }

    /**
     * Keep dedicated club day identical for every approved teacher at this school club.
     */
    public function syncClubDay(TeacherClub $membership, ?string $clubDay): void
    {
        $day = filled($clubDay) ? $clubDay : null;

        $this->coMemberQuery($membership)->update(['club_day' => $day]);

        $membership->club_day = $day;
    }

    /**
     * Club day already used by this school club, if any.
     */
    public function existingClubDayForSchoolClub(int $clubId, int $schoolId): ?string
    {
        $membership = TeacherClub::query()
            ->where('club_id', $clubId)
            ->where('school_id', $schoolId)
            ->approved()
            ->whereNotNull('club_day')
            ->orderByDesc('is_leader')
            ->orderBy('id')
            ->first();

        return $membership?->club_day;
    }

    /**
     * @return Builder<TeacherClub>
     */
    private function coMemberQuery(TeacherClub $membership): Builder
    {
        $query = TeacherClub::query()
            ->where('club_id', $membership->club_id)
            ->approved();

        if ($membership->school_id === null) {
            $query->whereNull('school_id');
        } else {
            $query->where('school_id', $membership->school_id);
        }

        return $query;
    }
}
