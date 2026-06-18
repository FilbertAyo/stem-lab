<?php

namespace App\Services\Learning;

use App\Enums\LearningEventType;
use App\Models\ModelQuestion;
use App\Models\ModelQuestionAnswer;
use App\Models\Student;
use App\Models\StudentLearningEvent;
use App\Models\StudentProgressSummary;
use App\Models\User;
use App\Models\VrAssessmentAttempt;
use Carbon\Carbon;

class StudentProgressSummaryService
{
    public const ACTIVE_DAYS = 7;

    public function __construct(
        private readonly StudentAttentionService $attentionService,
    ) {}

    public function recomputeForUser(User $user): ?StudentProgressSummary
    {
        $student = Student::query()->where('user_id', $user->id)->first();

        if (! $student) {
            return null;
        }

        return $this->recomputeForStudent($student);
    }

    public function recomputeForStudent(Student $student): StudentProgressSummary
    {
        $userId = $student->user_id;
        $metrics = $this->computeMetrics($userId);

        $attentionFlags = $this->attentionService->computeFlags(
            $student,
            $metrics['last_activity_at'],
            $metrics['avg_vr_score'],
            $metrics['vr_attempts'],
        );

        return StudentProgressSummary::query()->updateOrCreate(
            ['student_id' => $student->id],
            [
                'user_id' => $userId,
                'teacher_club_id' => $student->teacher_club_id,
                'models_attempted' => $metrics['models_attempted'],
                'models_completed' => $metrics['models_completed'],
                'vr_attempts' => $metrics['vr_attempts'],
                'vr_passed' => $metrics['vr_passed'],
                'avg_vr_score' => $metrics['avg_vr_score'],
                'resources_accessed' => $metrics['resources_accessed'],
                'projects_viewed' => $metrics['projects_viewed'],
                'last_activity_at' => $metrics['last_activity_at'],
                'attention_flags' => $attentionFlags,
                'computed_at' => now(),
            ],
        );
    }

    /**
     * @return array{
     *     models_attempted: int,
     *     models_completed: int,
     *     vr_attempts: int,
     *     vr_passed: int,
     *     avg_vr_score: float|null,
     *     resources_accessed: int,
     *     projects_viewed: int,
     *     last_activity_at: Carbon|null
     * }
     */
    private function computeMetrics(?int $userId): array
    {
        if ($userId === null) {
            return [
                'models_attempted' => 0,
                'models_completed' => 0,
                'vr_attempts' => 0,
                'vr_passed' => 0,
                'avg_vr_score' => null,
                'resources_accessed' => 0,
                'projects_viewed' => 0,
                'last_activity_at' => null,
            ];
        }

        $modelIds = ModelQuestionAnswer::query()
            ->where('user_id', $userId)
            ->distinct()
            ->pluck('model_id');

        $modelsAttempted = $modelIds->count();
        $modelsCompleted = 0;

        if ($modelIds->isNotEmpty()) {
            $questionCounts = ModelQuestion::query()
                ->whereIn('model_id', $modelIds)
                ->selectRaw('model_id, COUNT(*) as total')
                ->groupBy('model_id')
                ->pluck('total', 'model_id');

            $answerCounts = ModelQuestionAnswer::query()
                ->where('user_id', $userId)
                ->whereIn('model_id', $modelIds)
                ->selectRaw('model_id, COUNT(*) as total')
                ->groupBy('model_id')
                ->pluck('total', 'model_id');

            foreach ($modelIds as $modelId) {
                $totalQuestions = (int) ($questionCounts[$modelId] ?? 0);
                $answered = (int) ($answerCounts[$modelId] ?? 0);

                if ($totalQuestions > 0 && $answered >= $totalQuestions) {
                    $modelsCompleted++;
                }
            }
        }

        $vrQuery = VrAssessmentAttempt::query()
            ->where('user_id', $userId)
            ->whereNotNull('completed_at');

        $vrAttempts = (clone $vrQuery)->count();
        $vrPassed = (clone $vrQuery)->where('passed', true)->count();
        $avgVrScore = $vrAttempts > 0 ? round((float) (clone $vrQuery)->avg('score'), 2) : null;

        $resourcesAccessed = StudentLearningEvent::query()
            ->where('user_id', $userId)
            ->whereIn('event_type', [
                LearningEventType::ModelViewed,
                LearningEventType::ResourceAccessed,
            ])
            ->distinct('subject_id')
            ->count('subject_id');

        $projectsViewed = StudentLearningEvent::query()
            ->where('user_id', $userId)
            ->where('event_type', LearningEventType::ProjectViewed)
            ->distinct('subject_id')
            ->count('subject_id');

        return [
            'models_attempted' => $modelsAttempted,
            'models_completed' => $modelsCompleted,
            'vr_attempts' => $vrAttempts,
            'vr_passed' => $vrPassed,
            'avg_vr_score' => $avgVrScore,
            'resources_accessed' => $resourcesAccessed,
            'projects_viewed' => $projectsViewed,
            'last_activity_at' => $this->resolveLastActivityAt($userId),
        ];
    }

    private function resolveLastActivityAt(int $userId): ?Carbon
    {
        $timestamps = collect([
            ModelQuestionAnswer::query()->where('user_id', $userId)->max('updated_at'),
            VrAssessmentAttempt::query()->where('user_id', $userId)->max('completed_at'),
            StudentLearningEvent::query()->where('user_id', $userId)->max('occurred_at'),
        ])->filter();

        if ($timestamps->isEmpty()) {
            return null;
        }

        return Carbon::parse($timestamps->max());
    }

    public function isActive(?Carbon $lastActivityAt): bool
    {
        return $lastActivityAt !== null
            && $lastActivityAt->gte(now()->subDays(self::ACTIVE_DAYS));
    }
}
