<?php

namespace App\Services\Learning;

use App\Models\Student;
use Carbon\Carbon;

class StudentAttentionService
{
    public const INACTIVE_DAYS = 14;

    public const LOW_QUIZ_SCORE_THRESHOLD = 50.0;

    /**
     * @return list<string>
     */
    public function computeFlags(
        Student $student,
        ?Carbon $lastActivityAt,
        ?float $avgVrScore,
        int $vrAttempts,
    ): array {
        if (! $student->isLinked()) {
            return ['not_registered'];
        }

        $flags = [];

        if ($lastActivityAt === null || $lastActivityAt->lt(now()->subDays(self::INACTIVE_DAYS))) {
            $flags[] = 'inactive';
        }

        if ($vrAttempts > 0 && $avgVrScore !== null && $avgVrScore < self::LOW_QUIZ_SCORE_THRESHOLD) {
            $flags[] = 'low_quiz_score';
        }

        return $flags;
    }
}
