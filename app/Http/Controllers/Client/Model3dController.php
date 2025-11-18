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
        $userId = Auth::id();

        $model3ds = Model3d::with('subject')
            ->withCount('questions')
            ->when($subjectId, fn ($query) => $query->where('subject_id', $subjectId))
            ->latest()
            ->get();

        // Get student's answers for all models
        $studentAnswers = \App\Models\ModelQuestionAnswer::where('user_id', $userId)
            ->with('question')
            ->get()
            ->groupBy('model_id');

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

        $subjects = Subject::where('is_active', true)
            ->orderBy('subject_name')
            ->get();

        return view('client.back.models.3d.index', [
            'model3ds' => $model3ds,
            'subjects' => $subjects,
            'selectedSubject' => $subjectId,
            'modelProgress' => $modelProgress,
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

