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
        $students = $this->scopeService
            ->studentsQuery($teacher, $teacherClubId)
            ->with('progressSummary')
            ->get();

        $summaries = $students
            ->map(fn (Student $student) => $student->progressSummary)
            ->filter();

        $registered = $students->filter(fn (Student $s) => $s->isLinked())->count();
        $active = $summaries->filter(
            fn (?StudentProgressSummary $summary) => $summary && $this->summaryService->isActive($summary->last_activity_at)
        )->count();

        $needsAttention = $summaries->filter(
            fn (?StudentProgressSummary $summary) => $summary && $summary->needsAttention()
        )->count();

        $avgScores = $summaries
            ->filter(fn (?StudentProgressSummary $summary) => $summary && $summary->avg_vr_score !== null)
            ->pluck('avg_vr_score');

        return [
            'total_roster' => $students->count(),
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
