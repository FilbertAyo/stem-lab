<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Model3d;
use App\Models\ModelQuestionAnswer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgressController extends Controller
{
    /**
     * Display student progress for 3D model questions.
     */
    public function index(Request $request): View
    {
        $query = ModelQuestionAnswer::with(['user', 'model3d', 'question']);

        // Filter by model if provided
        if ($request->has('model_id') && $request->model_id) {
            $query->where('model_id', $request->model_id);
        }

        // Filter by user if provided
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Get all answers and group by user_id and model_id
        $allAnswers = $query->get();
        
        // Group by user_id and model_id
        $grouped = $allAnswers->groupBy(function ($answer) {
            return $answer->user_id . '_' . $answer->model_id;
        });

        // Create aggregated data for each student-model combination
        $progressData = $grouped->map(function ($answers, $key) {
            $firstAnswer = $answers->first();
            $totalQuestions = $answers->count();
            $correctAnswers = $answers->where('is_correct', true)->count();
            $totalScore = $answers->sum('score');
            $percentage = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0;
            $lastAnswered = $answers->max('created_at');

            return (object) [
                'user' => $firstAnswer->user,
                'model3d' => $firstAnswer->model3d,
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctAnswers,
                'total_score' => $totalScore,
                'percentage' => $percentage,
                'last_answered' => $lastAnswered,
                'answers' => $answers, // Keep for detail view
            ];
        })->sortByDesc('last_answered')->values();

        // Paginate manually
        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $items = $progressData->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $progressData->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
        $paginated->appends($request->query());

        // Get all models for filter dropdown
        $models = Model3d::withCount('questions')->orderBy('name')->get();

        // Get all students who have answered questions
        $students = User::whereHas('modelQuestionAnswers')
            ->orderBy('name')
            ->get();

        // Calculate statistics
        $stats = [
            'total_answers' => ModelQuestionAnswer::count(),
            'total_correct' => ModelQuestionAnswer::where('is_correct', true)->count(),
            'total_students' => User::whereHas('modelQuestionAnswers')->distinct()->count(),
            'total_models' => Model3d::whereHas('answers')->distinct()->count(),
        ];

        return view('admin.progress.index', compact('paginated', 'models', 'students', 'stats'));
    }

    /**
     * Show detailed answers for a specific student-model combination.
     */
    public function showDetail(Request $request, User $user, Model3d $model3d): View
    {
        $answers = ModelQuestionAnswer::with(['question'])
            ->where('user_id', $user->id)
            ->where('model_id', $model3d->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalQuestions = $answers->count();
        $correctAnswers = $answers->where('is_correct', true)->count();
        $percentage = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0;

        return view('admin.progress.detail', compact('user', 'model3d', 'answers', 'totalQuestions', 'correctAnswers', 'percentage'));
    }

    /**
     * Show detailed progress for a specific student.
     */
    public function showStudent(User $user): View
    {
        $answers = ModelQuestionAnswer::with(['model3d', 'question'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Group by model
        $answersByModel = $answers->groupBy('model_id');

        // Calculate statistics for this student
        $stats = [
            'total_answers' => $answers->count(),
            'total_correct' => $answers->where('is_correct', true)->count(),
            'total_models' => $answersByModel->count(),
            'average_score' => $answers->count() > 0 ? round(($answers->sum('score') / $answers->count()) * 100, 2) : 0,
        ];

        return view('admin.progress.student', compact('user', 'answers', 'answersByModel', 'stats'));
    }

    /**
     * Show detailed progress for a specific model.
     */
    public function showModel(Model3d $model3d): View
    {
        $answers = ModelQuestionAnswer::with(['user', 'question'])
            ->where('model_id', $model3d->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Group by user
        $answersByUser = $answers->groupBy('user_id');

        // Calculate statistics for this model
        $stats = [
            'total_answers' => $answers->count(),
            'total_correct' => $answers->where('is_correct', true)->count(),
            'total_students' => $answersByUser->count(),
            'average_score' => $answers->count() > 0 ? round(($answers->sum('score') / $answers->count()) * 100, 2) : 0,
        ];

        return view('admin.progress.model', compact('model3d', 'answers', 'answersByUser', 'stats'));
    }
}
