<?php

namespace App\Services\Teacher;

use App\Models\Student;
use App\Models\TeacherClub;
use App\Models\User;
use App\Services\Learning\StudentProgressSummaryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TeacherStudentScopeService
{
    public function __construct(
        private readonly StudentProgressSummaryService $summaryService,
    ) {}

    /**
     * @return Collection<int, TeacherClub>
     */
    public function manageableTeacherClubs(User $teacher): Collection
    {
        return $teacher->teacherClubs()
            ->where('status', 'approved')
            ->with(['club', 'school'])
            ->get();
    }

    /**
     * @return Builder<Student>
     */
    public function studentsQuery(User $teacher, ?int $teacherClubId = null): Builder
    {
        $clubIds = $teacher->approvedCoMemberTeacherClubIds();

        if ($teacherClubId !== null) {
            abort_unless($clubIds->contains($teacherClubId), 403);
            $clubIds = collect([$teacherClubId]);
        }

        return Student::query()
            ->whereIn('teacher_club_id', $clubIds)
            ->with(['schoolClass', 'progressSummary', 'user'])
            ->orderBy('name');
    }

    public function canManageStudent(User $teacher, Student $student): bool
    {
        return $teacher->approvedCoMemberTeacherClubIds()->contains($student->teacher_club_id);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<Student>
     */
    public function filteredStudentsQuery(User $teacher, array $filters = []): Builder
    {
        $clubFilter = $filters['club'] ?? 'all';
        $teacherClubId = $clubFilter !== 'all' && $clubFilter !== null
            ? (int) $clubFilter
            : null;

        $query = $this->studentsQuery($teacher, $teacherClubId);

        if (! empty($filters['class'])) {
            $query->where('school_class_id', (int) $filters['class']);
        }

        if (($filters['registration'] ?? 'all') === 'registered') {
            $query->whereNotNull('user_id');
        } elseif (($filters['registration'] ?? 'all') === 'not_registered') {
            $query->whereNull('user_id');
        }

        if (! empty($filters['q'])) {
            $search = '%'.$filters['q'].'%';
            $query->where(function (Builder $q) use ($search): void {
                $q->where('name', 'like', $search)
                    ->orWhere('admission_number', 'like', $search);
            });
        }

        if (($filters['activity'] ?? 'all') !== 'all' || ($filters['attention'] ?? 'all') !== 'all') {
            $query->with('progressSummary');
        }

        if (($filters['activity'] ?? 'all') === 'active') {
            $query->whereHas('progressSummary', function (Builder $q): void {
                $q->where('last_activity_at', '>=', now()->subDays(StudentProgressSummaryService::ACTIVE_DAYS));
            });
        } elseif (($filters['activity'] ?? 'all') === 'inactive') {
            $query->where(function (Builder $q): void {
                $q->whereDoesntHave('progressSummary')
                    ->orWhereHas('progressSummary', function (Builder $inner): void {
                        $inner->where(function (Builder $activity): void {
                            $activity->whereNull('last_activity_at')
                                ->orWhere('last_activity_at', '<', now()->subDays(StudentProgressSummaryService::ACTIVE_DAYS));
                        });
                    });
            })->whereNotNull('user_id');
        }

        if (($filters['attention'] ?? 'all') === 'needs_support') {
            $query->whereHas('progressSummary', function (Builder $q): void {
                $q->where(function (Builder $flags): void {
                    $flags->whereJsonContains('attention_flags', 'inactive')
                        ->orWhereJsonContains('attention_flags', 'low_quiz_score');
                });
            });
        }

        if (! empty($filters['student'])) {
            $query->where('id', (int) $filters['student']);
        }

        return $query;
    }
}
