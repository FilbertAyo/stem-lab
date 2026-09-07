<?php

namespace App\Services\Learning;

use App\DataTransferObjects\StudentDashboardAnalytics;
use App\Enums\LearningEventType;
use App\Models\Model3d;
use App\Models\ModelQuestion;
use App\Models\ModelQuestionAnswer;
use App\Models\StudentLearningEvent;
use App\Models\User;
use App\Models\VrAssessmentAttempt;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StudentDashboardAnalyticsService
{
    public const WEEK_DAYS = 7;

    public function forUser(User $user): StudentDashboardAnalytics
    {
        $userId = $user->id;

        $totalAnswers = ModelQuestionAnswer::query()->where('user_id', $userId)->count();
        $correctAnswers = ModelQuestionAnswer::query()
            ->where('user_id', $userId)
            ->where('is_correct', true)
            ->count();
        $accuracy = $totalAnswers > 0
            ? round(($correctAnswers / $totalAnswers) * 100, 1)
            : 0.0;

        $modelProgress = $this->modelProgress($userId);
        $modelsAttempted = ModelQuestionAnswer::query()
            ->where('user_id', $userId)
            ->distinct()
            ->count('model_id');
        $modelsCompleted = $this->completedModelCount($userId);

        $vrAttempts = VrAssessmentAttempt::query()
            ->where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->with('questionBank')
            ->orderByDesc('completed_at')
            ->limit(8)
            ->get();

        $vrAttemptsCount = VrAssessmentAttempt::query()
            ->where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->count();
        $vrPassed = VrAssessmentAttempt::query()
            ->where('user_id', $userId)
            ->where('passed', true)
            ->count();
        $avgVrScore = $vrAttemptsCount > 0
            ? round((float) VrAssessmentAttempt::query()
                ->where('user_id', $userId)
                ->whereNotNull('completed_at')
                ->avg('score'), 1)
            : null;

        $resourcesAccessed = StudentLearningEvent::query()
            ->where('user_id', $userId)
            ->whereIn('event_type', [
                LearningEventType::ModelViewed,
                LearningEventType::ResourceAccessed,
            ])
            ->distinct()
            ->count('subject_id');

        $projectsViewed = StudentLearningEvent::query()
            ->where('user_id', $userId)
            ->where('event_type', LearningEventType::ProjectViewed)
            ->distinct()
            ->count('subject_id');

        $recentEvents = StudentLearningEvent::query()
            ->where('user_id', $userId)
            ->with('subject')
            ->orderByDesc('occurred_at')
            ->limit(8)
            ->get();

        $weeklyActivity = $this->weeklyActivity($userId);
        $weeklyMax = (int) $weeklyActivity->max('count');

        $lastActivityAt = $this->lastActivityAt($userId);

        return new StudentDashboardAnalytics(
            modelsAttempted: $modelsAttempted,
            modelsCompleted: $modelsCompleted,
            totalAnswers: $totalAnswers,
            correctAnswers: $correctAnswers,
            accuracy: $accuracy,
            vrAttemptsCount: $vrAttemptsCount,
            vrPassed: $vrPassed,
            avgVrScore: $avgVrScore,
            resourcesAccessed: $resourcesAccessed,
            projectsViewed: $projectsViewed,
            lastActivityAt: $lastActivityAt,
            weeklyActivity: $weeklyActivity,
            weeklyMax: $weeklyMax,
            modelProgress: $modelProgress,
            vrAttempts: $vrAttempts,
            recentEvents: $recentEvents,
            hasActivity: $totalAnswers > 0
                || $vrAttemptsCount > 0
                || $recentEvents->isNotEmpty()
                || $resourcesAccessed > 0
                || $projectsViewed > 0,
        );
    }

    /**
     * @return Collection<int, object{model: Model3d, total_questions: int, correct_answers: int, percentage: float, last_answered: string}>
     */
    private function modelProgress(int $userId): Collection
    {
        $grouped = ModelQuestionAnswer::query()
            ->where('user_id', $userId)
            ->selectRaw('
                model_id,
                COUNT(*) as total_questions,
                SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct_answers,
                MAX(updated_at) as last_answered
            ')
            ->groupBy('model_id')
            ->orderByDesc('last_answered')
            ->limit(8)
            ->get();

        if ($grouped->isEmpty()) {
            return collect();
        }

        $models = Model3d::query()
            ->whereIn('id', $grouped->pluck('model_id'))
            ->get()
            ->keyBy('id');

        $questionCounts = ModelQuestion::query()
            ->whereIn('model_id', $grouped->pluck('model_id'))
            ->selectRaw('model_id, COUNT(*) as total')
            ->groupBy('model_id')
            ->pluck('total', 'model_id');

        return $grouped
            ->map(function ($item) use ($models, $questionCounts) {
                $model = $models->get($item->model_id);

                if ($model === null) {
                    return null;
                }

                $answered = (int) $item->total_questions;
                $correct = (int) $item->correct_answers;
                $totalQuestions = (int) ($questionCounts[$item->model_id] ?? $answered);

                return (object) [
                    'model' => $model,
                    'total_questions' => $totalQuestions,
                    'answered' => $answered,
                    'correct_answers' => $correct,
                    'percentage' => $answered > 0 ? round(($correct / $answered) * 100, 1) : 0.0,
                    'completed' => $totalQuestions > 0 && $answered >= $totalQuestions,
                    'last_answered' => $item->last_answered,
                ];
            })
            ->filter()
            ->values();
    }

    private function completedModelCount(int $userId): int
    {
        $modelIds = ModelQuestionAnswer::query()
            ->where('user_id', $userId)
            ->distinct()
            ->pluck('model_id');

        if ($modelIds->isEmpty()) {
            return 0;
        }

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

        $completed = 0;

        foreach ($modelIds as $modelId) {
            $totalQuestions = (int) ($questionCounts[$modelId] ?? 0);
            $answered = (int) ($answerCounts[$modelId] ?? 0);

            if ($totalQuestions > 0 && $answered >= $totalQuestions) {
                $completed++;
            }
        }

        return $completed;
    }

    /**
     * @return Collection<int, object{date: string, label: string, count: int}>
     */
    private function weeklyActivity(int $userId): Collection
    {
        $start = now()->subDays(self::WEEK_DAYS - 1)->startOfDay();

        $days = collect(range(0, self::WEEK_DAYS - 1))->mapWithKeys(function (int $offset) use ($start) {
            $date = $start->copy()->addDays($offset);

            return [
                $date->toDateString() => (object) [
                    'date' => $date->toDateString(),
                    'label' => $date->format('D'),
                    'count' => 0,
                ],
            ];
        });

        $eventTimestamps = StudentLearningEvent::query()
            ->where('user_id', $userId)
            ->where('occurred_at', '>=', $start)
            ->pluck('occurred_at');

        $timestamps = $eventTimestamps->isNotEmpty()
            ? $eventTimestamps
            : collect()
                ->merge(
                    ModelQuestionAnswer::query()
                        ->where('user_id', $userId)
                        ->where('updated_at', '>=', $start)
                        ->pluck('updated_at')
                )
                ->merge(
                    VrAssessmentAttempt::query()
                        ->where('user_id', $userId)
                        ->whereNotNull('completed_at')
                        ->where('completed_at', '>=', $start)
                        ->pluck('completed_at')
                );

        foreach ($timestamps as $timestamp) {
            $key = Carbon::parse($timestamp)->toDateString();

            if (! $days->has($key)) {
                continue;
            }

            $days[$key]->count++;
        }

        return $days->values();
    }

    private function lastActivityAt(int $userId): ?Carbon
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
}
