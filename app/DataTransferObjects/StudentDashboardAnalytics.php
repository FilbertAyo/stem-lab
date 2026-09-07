<?php

namespace App\DataTransferObjects;

use Carbon\Carbon;
use Illuminate\Support\Collection;

readonly class StudentDashboardAnalytics
{
    /**
     * @param  Collection<int, object{date: string, label: string, count: int}>  $weeklyActivity
     * @param  Collection<int, object{model: \App\Models\Model3d, total_questions: int, correct_answers: int, percentage: float, last_answered: string}>  $modelProgress
     * @param  Collection<int, \App\Models\VrAssessmentAttempt>  $vrAttempts
     * @param  Collection<int, \App\Models\StudentLearningEvent>  $recentEvents
     */
    public function __construct(
        public int $modelsAttempted,
        public int $modelsCompleted,
        public int $totalAnswers,
        public int $correctAnswers,
        public float $accuracy,
        public int $vrAttemptsCount,
        public int $vrPassed,
        public ?float $avgVrScore,
        public int $resourcesAccessed,
        public int $projectsViewed,
        public ?Carbon $lastActivityAt,
        public Collection $weeklyActivity,
        public int $weeklyMax,
        public Collection $modelProgress,
        public Collection $vrAttempts,
        public Collection $recentEvents,
        public bool $hasActivity,
    ) {}
}
