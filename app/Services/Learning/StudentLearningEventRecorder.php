<?php

namespace App\Services\Learning;

use App\Enums\LearningEventType;
use App\Models\Student;
use App\Models\StudentLearningEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class StudentLearningEventRecorder
{
    public function __construct(
        private readonly StudentProgressSummaryService $summaryService,
    ) {}

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        User $user,
        LearningEventType $eventType,
        ?Model $subject = null,
        array $metadata = [],
        ?Carbon $occurredAt = null,
    ): ?StudentLearningEvent {
        if ($user->role !== 'student') {
            return null;
        }

        $student = Student::query()->where('user_id', $user->id)->first();
        $occurredAt ??= now();

        if ($subject !== null && $this->isDuplicateViewEvent($user, $eventType, $subject, $occurredAt)) {
            return null;
        }

        $event = StudentLearningEvent::query()->create([
            'user_id' => $user->id,
            'student_id' => $student?->id,
            'event_type' => $eventType,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'metadata' => $metadata ?: null,
            'occurred_at' => $occurredAt,
        ]);

        if ($student) {
            $this->summaryService->recomputeForStudent($student);
        }

        return $event;
    }

    private function isDuplicateViewEvent(
        User $user,
        LearningEventType $eventType,
        Model $subject,
        Carbon $occurredAt,
    ): bool {
        if (! in_array($eventType->value, LearningEventType::resourceTypes(), true)) {
            return false;
        }

        return StudentLearningEvent::query()
            ->where('user_id', $user->id)
            ->where('event_type', $eventType)
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey())
            ->where('occurred_at', '>=', $occurredAt->copy()->subHour())
            ->exists();
    }
}
