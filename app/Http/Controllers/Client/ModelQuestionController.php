<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Client\Concerns\ResolvesStudentClassContext;
use App\Http\Controllers\Controller;
use App\Models\Model3d;
use App\Models\ModelQuestion;
use App\Models\ModelQuestionAnswer;
use App\Enums\LearningEventType;
use App\Services\CacheService;
use App\Services\Learning\StudentLearningEventRecorder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ModelQuestionController extends Controller
{
    use ResolvesStudentClassContext;

    public function __construct(
        private readonly StudentLearningEventRecorder $eventRecorder,
    ) {}

    /**
     * Submit answers for questions related to a 3D model.
     */
    public function submitAnswers(Request $request, Model3d $model3d): JsonResponse
    {
        if (! $model3d->isAccessibleToStudentClass($this->studentSchoolClassId())) {
            abort(404);
        }

        $request->validate([
            'answers' => ['required', 'array'],
            'answers.*.question_id' => ['required', 'exists:model_questions,id'],
            'answers.*.answer' => ['required', 'string'],
        ]);

        $userId = Auth::id();
        $results = [];
        $totalScore = 0;
        $totalQuestions = count($request->answers);

        foreach ($request->answers as $answerData) {
            $question = ModelQuestion::findOrFail($answerData['question_id']);

            // Verify question belongs to this model
            if ($question->model_id !== $model3d->id) {
                continue;
            }

            $studentAnswer = trim($answerData['answer']);
            $correctAnswer = trim($question->correct_answer);

            // Check if answer is correct (case-insensitive for short answers)
            $isCorrect = false;
            if ($question->question_type === 'short_answer') {
                $isCorrect = strcasecmp($studentAnswer, $correctAnswer) === 0;
            } else {
                $isCorrect = $studentAnswer === $correctAnswer;
            }

            $score = $isCorrect ? 1 : 0;
            $totalScore += $score;

            // Save or update the answer
            ModelQuestionAnswer::updateOrCreate(
                [
                    'user_id' => $userId,
                    'model_id' => $model3d->id,
                    'question_id' => $question->id,
                ],
                [
                    'student_answer' => $studentAnswer,
                    'is_correct' => $isCorrect,
                    'score' => $score,
                ]
            );
            
            // Clear user progress cache (handled by model event, but explicit for clarity)
            CacheService::clearUserCache($userId);

            $results[] = [
                'question_id' => $question->id,
                'is_correct' => $isCorrect,
                'correct_answer' => $correctAnswer,
                'explanation' => $question->explanation,
            ];
        }

        $user = Auth::user();
        if ($user) {
            $this->eventRecorder->record(
                $user,
                LearningEventType::ModelQuestionAnswered,
                $model3d,
                ['score' => $totalScore, 'total' => $totalQuestions],
            );

            $totalOnModel = ModelQuestion::query()->where('model_id', $model3d->id)->count();
            $answeredOnModel = ModelQuestionAnswer::query()
                ->where('user_id', $userId)
                ->where('model_id', $model3d->id)
                ->count();

            if ($totalOnModel > 0 && $answeredOnModel >= $totalOnModel) {
                $this->eventRecorder->record(
                    $user,
                    LearningEventType::ModelCompleted,
                    $model3d,
                    ['score' => $totalScore, 'total' => $totalQuestions],
                );
            }
        }

        return response()->json([
            'success' => true,
            'score' => $totalScore,
            'total' => $totalQuestions,
            'percentage' => $totalQuestions > 0 ? round(($totalScore / $totalQuestions) * 100, 2) : 0,
            'results' => $results,
        ]);
    }

    /**
     * Get questions for a 3D model (for AJAX loading).
     * Cached for 1 hour since questions rarely change.
     */
    public function getQuestions(Model3d $model3d): JsonResponse
    {
        if (! $model3d->isAccessibleToStudentClass($this->studentSchoolClassId())) {
            abort(404);
        }

        $cacheKey = "model_questions_{$model3d->id}";
        
        $questions = Cache::remember($cacheKey, CacheService::TTL_LONG, function () use ($model3d) {
            return $model3d->questions()->ordered()->get()->map(function ($question) {
                return [
                    'id' => $question->id,
                    'question_type' => $question->question_type,
                    'question_text' => $question->question_text,
                    'options' => $question->options,
                ];
            })->values()->toArray(); // Convert to array and re-index
        });

        return response()->json([
            'success' => true,
            'questions' => $questions ?? [],
        ]);
    }
}
