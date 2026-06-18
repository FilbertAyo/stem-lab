<?php

namespace App\Services;

use App\Models\Student;
use App\Models\TeacherClub;
use App\Models\User;
use App\Notifications\StudentJoinedClubNotification;
use Illuminate\Support\Collection;

class TeacherClubNotificationService
{
    /**
     * Notify all approved co-teachers at the same club + school.
     */
    public function notifyStudentJoined(Student $student): void
    {
        $student->loadMissing('teacherClub');

        $teacherClub = $student->teacherClub;

        if (! $teacherClub) {
            return;
        }

        $this->coTeachersForMembership($teacherClub)->each(function (User $teacher) use ($student): void {
            $teacher->notify(new StudentJoinedClubNotification($student));
        });
    }

    /**
     * @return Collection<int, User>
     */
    public function coTeachersForMembership(TeacherClub $teacherClub): Collection
    {
        $query = TeacherClub::query()
            ->with('user')
            ->where('club_id', $teacherClub->club_id)
            ->where('status', 'approved');

        if ($teacherClub->school_id === null) {
            $query->whereNull('school_id');
        } else {
            $query->where('school_id', $teacherClub->school_id);
        }

        return $query->get()
            ->pluck('user')
            ->filter()
            ->unique('id')
            ->values();
    }
}
