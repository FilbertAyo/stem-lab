<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Model3d;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class Model3dController extends Controller
{
    /**
     * Display a listing of the 3D models available to students.
     */
    public function index(Request $request): View
    {
        $subjectId = $request->input('subject');
        $search = $request->input('search');
        $sort = $request->input('sort', 'by_subject'); // Default to grouped by subject
        $userId = Auth::id();

        // Check if we should show grouped view (default when no filters/search)
        $showGroupedView = $sort === 'by_subject' && !$search && !$subjectId;

        $subjects = Subject::where('is_active', true)
            ->orderBy('display_order')
            ->get();

        // Get total count for stats
        $totalModels = Model3d::count();

        // Get student's answers for all models
        $studentAnswers = \App\Models\ModelQuestionAnswer::where('user_id', $userId)
            ->with('question')
            ->get()
            ->groupBy('model_id');

        if ($showGroupedView) {
            // Grouped view: Get all models grouped by subject, subjects in random order
            $allModels = Model3d::with('subject')
                ->withCount('questions')
                ->get();

            // Calculate progress for each model
            $modelProgress = [];
            foreach ($allModels as $model) {
                $answers = $studentAnswers->get($model->id, collect());
                $totalQuestions = $model->questions_count;
                $answeredQuestions = $answers->count();
                $correctAnswers = $answers->where('is_correct', true)->count();
                $percentage = $answeredQuestions > 0 ? round(($correctAnswers / $answeredQuestions) * 100, 2) : 0;
                $isCompleted = $answeredQuestions > 0 && $answeredQuestions >= $totalQuestions;

                $modelProgress[$model->id] = [
                    'answered' => $answeredQuestions,
                    'total' => $totalQuestions,
                    'correct' => $correctAnswers,
                    'percentage' => $percentage,
                    'is_completed' => $isCompleted,
                    'has_attempted' => $answeredQuestions > 0,
                ];
            }

            // Group models by subject
            $modelsBySubject = $allModels->groupBy('subject_id');
            $subjectsWithModels = $subjects->filter(fn ($s) => $modelsBySubject->has($s->id));

            return view('client.back.models.3d.index', [
                'model3ds' => collect(),
                'modelsBySubject' => $modelsBySubject,
                'subjectsWithModels' => $subjectsWithModels,
                'showGroupedView' => true,
                'subjects' => $subjects,
                'selectedSubject' => $subjectId,
                'modelProgress' => $modelProgress,
                'search' => $search,
                'sort' => $sort,
                'totalModels' => $totalModels,
            ]);
        }

        // Standard paginated view with filters/sorting
        $query = Model3d::with('subject')
            ->withCount('questions')
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            }));

        // Apply sorting
        $query = match ($sort) {
            'oldest' => $query->oldest(),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            default => $query->latest(), // newest
        };

        $model3ds = $query->paginate(12)->withQueryString();

        // Calculate progress for each model
        $modelProgress = [];
        foreach ($model3ds as $model) {
            $answers = $studentAnswers->get($model->id, collect());
            $totalQuestions = $model->questions_count;
            $answeredQuestions = $answers->count();
            $correctAnswers = $answers->where('is_correct', true)->count();
            $percentage = $answeredQuestions > 0 ? round(($correctAnswers / $answeredQuestions) * 100, 2) : 0;
            $isCompleted = $answeredQuestions > 0 && $answeredQuestions >= $totalQuestions;

            $modelProgress[$model->id] = [
                'answered' => $answeredQuestions,
                'total' => $totalQuestions,
                'correct' => $correctAnswers,
                'percentage' => $percentage,
                'is_completed' => $isCompleted,
                'has_attempted' => $answeredQuestions > 0,
            ];
        }

        return view('client.back.models.3d.index', [
            'model3ds' => $model3ds,
            'modelsBySubject' => collect(),
            'subjectsWithModels' => collect(),
            'showGroupedView' => false,
            'subjects' => $subjects,
            'selectedSubject' => $subjectId,
            'modelProgress' => $modelProgress,
            'search' => $search,
            'sort' => $sort,
            'totalModels' => $totalModels,
        ]);
    }

    /**
     * Display a specific 3D model.
     */
    public function show(Model3d $model3d): View
    {
        $model3d->load('subject');
        $userId = Auth::id();

        // Get student's answers for this model
        $answers = \App\Models\ModelQuestionAnswer::where('user_id', $userId)
            ->where('model_id', $model3d->id)
            ->with('question')
            ->get();

        $totalQuestions = $model3d->questions()->count();
        $answeredQuestions = $answers->count();
        $correctAnswers = $answers->where('is_correct', true)->count();
        $percentage = $answeredQuestions > 0 ? round(($correctAnswers / $answeredQuestions) * 100, 2) : 0;
        $isCompleted = $answeredQuestions > 0 && $answeredQuestions >= $totalQuestions;

        $progress = [
            'answered' => $answeredQuestions,
            'total' => $totalQuestions,
            'correct' => $correctAnswers,
            'percentage' => $percentage,
            'is_completed' => $isCompleted,
            'has_attempted' => $answeredQuestions > 0,
        ];

        return view('client.back.models.3d.show', compact('model3d', 'progress'));
    }
}

