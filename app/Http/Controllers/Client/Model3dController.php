<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Client\Concerns\ResolvesStudentClassContext;
use App\Http\Controllers\Controller;
use App\Models\Model3d;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class Model3dController extends Controller
{
    use ResolvesStudentClassContext;

    /**
     * Display a listing of the 3D models available to students.
     */
    public function index(Request $request): View
    {
        $subjectId = $request->input('subject');
        $search = $request->input('search');
        $sort = $request->input('sort', 'by_subject');
        $userId = Auth::id();
        $schoolClassId = $this->studentSchoolClassId();

        // Always use flat paginated list (no categories); user filters via search/subject/sort
        $showGroupedView = false;

        // Get cached subjects
        $subjects = CacheService::getActiveSubjects();

        // Get total count for stats (only active models)
        $totalModels = Model3d::where('is_active', true)
            ->visibleToStudentClass($schoolClassId)
            ->count();

        // Get cached user progress
        $studentAnswers = CacheService::getUserProgress($userId);

        // Paginated view with optional subject/search filter and sorting
        $query = Model3d::active()
            ->withSubject()
            ->withCount('questions')
            ->visibleToStudentClass($schoolClassId)
            ->when($subjectId, fn ($q) => $q->forSubject($subjectId))
            ->when($search, fn ($q) => $q->search($search));

        // Apply sorting
        $query = match ($sort) {
            'oldest' => $query->oldest(),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            default => $query->latest(), // newest
        };

        $model3ds = $query->paginate(12)->withQueryString();

        // Calculate progress for each model using aggregated data
        $modelProgress = [];
        foreach ($model3ds as $model) {
            $answerData = $studentAnswers->get($model->id);
            $totalQuestions = $model->questions_count;
            $answeredQuestions = $answerData ? (int) $answerData->answered_count : 0;
            $correctAnswers = $answerData ? (int) $answerData->correct_count : 0;
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

        return view('client.back.models.3d.index', array_merge([
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
        ], $this->classFilterViewData()));
    }

    /**
     * Display a specific 3D model.
     */
    public function show(Model3d $model3d): View
    {
        $model3d->load('subject');
        
        // Ensure the model and subject are active
        if (!$model3d->is_active 
            || !$model3d->subject 
            || !$model3d->subject->is_active
            || ! $model3d->isAccessibleToStudentClass($this->studentSchoolClassId())) {
            abort(404);
        }
        
        $userId = Auth::id();

        // Get student's answers for this model
        $answers = \App\Models\ModelQuestionAnswer::forUser($userId)
            ->forModel($model3d->id)
            ->with('question')
            ->get();

        // Use withCount for better performance
        $model3d->loadCount('questions');
        $totalQuestions = $model3d->questions_count;
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

        return view('client.back.models.3d.show', array_merge(
            compact('model3d', 'progress'),
            $this->classFilterViewData()
        ));
    }
}

