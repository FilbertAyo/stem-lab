<?php

namespace App\Enums;

enum LearningEventType: string
{
    case ModelQuestionAnswered = 'model.question_answered';
    case ModelCompleted = 'model.completed';
    case ModelViewed = 'model.viewed';
    case VrQuizCompleted = 'vr.quiz_completed';
    case ProjectViewed = 'project.viewed';
    case ResourceAccessed = 'resource.accessed';

    /** @return list<string> */
    public static function resourceTypes(): array
    {
        return [
            self::ModelViewed->value,
            self::ProjectViewed->value,
            self::ResourceAccessed->value,
        ];
    }
}
