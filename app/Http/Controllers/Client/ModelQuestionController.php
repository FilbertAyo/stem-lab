<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Model3d;
use App\Models\ModelQuestion;
use App\Models\ModelQuestionAnswer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModelQuestionController extends Controller
{
    /**
     * Submit answers for questions related to a 3D model.
     */
    public function submitAnswers(Request $request, Model3d $model3d): JsonResponse
    {
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

            $results[] = [
                'question_id' => $question->id,
                'is_correct' => $isCorrect,
                'correct_answer' => $correctAnswer,
                'explanation' => $question->explanation,
            ];
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
     */
    public function getQuestions(Model3d $model3d): JsonResponse
    {
        $questions = $model3d->questions()->orderBy('order')->get()->map(function ($question) {
            return [
                'id' => $question->id,
                'question_type' => $question->question_type,
                'question_text' => $question->question_text,
                'options' => $question->options,
            ];
        });

        return response()->json([
            'success' => true,
            'questions' => $questions,
        ]);
    }
}
