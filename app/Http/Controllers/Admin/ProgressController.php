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
        // Use database aggregation instead of loading all records
        $baseQuery = ModelQuestionAnswer::query();

        // Filter by model if provided
        if ($request->has('model_id') && $request->model_id) {
            $baseQuery->where('model_id', $request->model_id);
        }

        // Filter by user if provided
        if ($request->has('user_id') && $request->user_id) {
            $baseQuery->where('user_id', $request->user_id);
        }

        // Get aggregated progress data using database aggregation
        $progressData = $baseQuery
            ->selectRaw('
                user_id,
                model_id,
                COUNT(*) as total_questions,
                SUM(CASE WHEN is_correct = true THEN 1 ELSE 0 END) as correct_answers,
                SUM(score) as total_score,
                MAX(created_at) as last_answered
            ')
            ->groupBy('user_id', 'model_id')
            ->orderByDesc('last_answered')
            ->get();

        // Extract unique IDs from the results (more efficient than cloning query)
        $userIds = $progressData->pluck('user_id')->unique()->values()->all();
        $modelIds = $progressData->pluck('model_id')->unique()->values()->all();

        // Eager load users and models only if we have IDs
        $users = !empty($userIds) ? User::whereIn('id', $userIds)->get()->keyBy('id') : collect();
        $models = !empty($modelIds) ? Model3d::whereIn('id', $modelIds)->get()->keyBy('id') : collect();

        // Transform aggregated data and attach relationships
        $progressData = $progressData->map(function ($item) use ($users, $models) {
            $percentage = $item->total_questions > 0 
                ? round(($item->correct_answers / $item->total_questions) * 100, 2) 
                : 0;

            return (object) [
                'user' => $users->get($item->user_id),
                'model3d' => $models->get($item->model_id),
                'total_questions' => (int) $item->total_questions,
                'correct_answers' => (int) $item->correct_answers,
                'total_score' => (int) $item->total_score,
                'percentage' => $percentage,
                'last_answered' => $item->last_answered,
            ];
        })->filter(function ($item) {
            // Filter out items where user or model was not found (shouldn't happen, but safety check)
            return $item->user && $item->model3d;
        });

        // Paginate the results
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
        $models = Model3d::withCount('questions')->ordered()->get();

        // Get all students who have answered questions (optimized query)
        $students = User::withAnswers()
            ->orderBy('name')
            ->get();

        // Calculate statistics using single query with aggregation
        $statsQuery = ModelQuestionAnswer::selectRaw('
            COUNT(*) as total_answers,
            SUM(CASE WHEN is_correct = true THEN 1 ELSE 0 END) as total_correct,
            COUNT(DISTINCT user_id) as total_students,
            COUNT(DISTINCT model_id) as total_models
        ')->first();

        $stats = [
            'total_answers' => (int) $statsQuery->total_answers,
            'total_correct' => (int) $statsQuery->total_correct,
            'total_students' => (int) $statsQuery->total_students,
            'total_models' => (int) $statsQuery->total_models,
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

        // Calculate statistics using database aggregation for better performance
        $statsQuery = ModelQuestionAnswer::where('user_id', $user->id)
            ->selectRaw('
                COUNT(*) as total_answers,
                SUM(CASE WHEN is_correct = true THEN 1 ELSE 0 END) as total_correct,
                COUNT(DISTINCT model_id) as total_models,
                AVG(score) * 100 as average_score
            ')
            ->first();

        $stats = [
            'total_answers' => (int) $statsQuery->total_answers,
            'total_correct' => (int) $statsQuery->total_correct,
            'total_models' => (int) $statsQuery->total_models,
            'average_score' => round((float) $statsQuery->average_score, 2),
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

        // Calculate statistics using database aggregation for better performance
        $statsQuery = ModelQuestionAnswer::where('model_id', $model3d->id)
            ->selectRaw('
                COUNT(*) as total_answers,
                SUM(CASE WHEN is_correct = true THEN 1 ELSE 0 END) as total_correct,
                COUNT(DISTINCT user_id) as total_students,
                AVG(score) * 100 as average_score
            ')
            ->first();

        $stats = [
            'total_answers' => (int) $statsQuery->total_answers,
            'total_correct' => (int) $statsQuery->total_correct,
            'total_students' => (int) $statsQuery->total_students,
            'average_score' => round((float) $statsQuery->average_score, 2),
        ];

        return view('admin.progress.model', compact('model3d', 'answers', 'answersByUser', 'stats'));
    }
}
