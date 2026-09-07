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

    public function label(): string
    {
        return match ($this) {
            self::ModelQuestionAnswered => 'Answered a lesson question',
            self::ModelCompleted => 'Finished a 3D lesson',
            self::ModelViewed => 'Viewed a 3D model',
            self::VrQuizCompleted => 'Completed a VR quiz',
            self::ProjectViewed => 'Viewed a project',
            self::ResourceAccessed => 'Opened a resource',
        };
    }

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
