<?php

namespace App\Services\Teacher;

use App\Models\Student;
use App\Models\StudentProgressSummary;
use App\Models\User;
use App\Services\Learning\StudentProgressSummaryService;
use Illuminate\Support\Collection;

class TeacherClubAnalyticsService
{
    public function __construct(
        private readonly TeacherStudentScopeService $scopeService,
        private readonly StudentProgressSummaryService $summaryService,
    ) {}

    /**
     * @return array{
     *     total_roster: int,
     *     registered: int,
     *     active: int,
     *     needs_attention: int,
     *     avg_vr_score: float|null
     * }
     */
    public function aggregate(User $teacher, ?int $teacherClubId = null): array
    {
        $clubIds = $teacher->approvedCoMemberTeacherClubIds();

        if ($teacherClubId !== null) {
            abort_unless($clubIds->contains($teacherClubId), 403);
            $clubIds = collect([$teacherClubId]);
        }

        if ($clubIds->isEmpty()) {
            return [
                'total_roster' => 0,
                'registered' => 0,
                'active' => 0,
                'needs_attention' => 0,
                'avg_vr_score' => null,
            ];
        }

        $students = Student::query()->whereIn('teacher_club_id', $clubIds);
        $totalRoster = (clone $students)->count();
        $registered = (clone $students)->whereNotNull('user_id')->count();

        $summaries = StudentProgressSummary::query()
            ->whereIn('student_id', (clone $students)->select('id'))
            ->get(['last_activity_at', 'attention_flags', 'avg_vr_score']);

        $active = $summaries->filter(
            fn (StudentProgressSummary $summary) => $this->summaryService->isActive($summary->last_activity_at)
        )->count();

        $needsAttention = $summaries->filter(
            fn (StudentProgressSummary $summary) => $summary->needsAttention()
        )->count();

        $avgScores = $summaries
            ->filter(fn (StudentProgressSummary $summary) => $summary->avg_vr_score !== null)
            ->pluck('avg_vr_score');

        return [
            'total_roster' => $totalRoster,
            'registered' => $registered,
            'active' => $active,
            'needs_attention' => $needsAttention,
            'avg_vr_score' => $avgScores->isNotEmpty()
                ? round((float) $avgScores->avg(), 1)
                : null,
        ];
    }

    /**
     * @return Collection<int, \App\Models\SchoolClass>
     */
    public function availableClasses(User $teacher, ?int $teacherClubId = null): Collection
    {
        return $this->scopeService
            ->studentsQuery($teacher, $teacherClubId)
            ->whereNotNull('school_class_id')
            ->with('schoolClass')
            ->get()
            ->pluck('schoolClass')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();
    }
}
