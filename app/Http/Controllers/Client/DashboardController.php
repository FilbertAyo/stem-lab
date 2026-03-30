<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Model3d;
use App\Models\ModelQuestionAnswer;
use App\Models\Student;
use App\Models\VrAssessmentAttempt;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the client dashboard.
     */
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $userId = $user->id;

        // Get user's model question answers
        $totalAnswers = ModelQuestionAnswer::where('user_id', $userId)->count();
        $correctAnswers = ModelQuestionAnswer::where('user_id', $userId)->where('is_correct', true)->count();
        $modelsCompleted = ModelQuestionAnswer::where('user_id', $userId)->distinct('model_id')->count('model_id');

        // Get user's VR assessment attempts
        $totalVrAttempts = VrAssessmentAttempt::where('user_id', $userId)->count();
        $completedVrAttempts = VrAssessmentAttempt::where('user_id', $userId)->whereNotNull('completed_at')->count();
        $passedVrAttempts = VrAssessmentAttempt::where('user_id', $userId)->where('passed', true)->count();

        // Get user's competition participations (disabled for phase-1 admin competition setup)
        $teacherClubs = $user->teacherClubs()->pluck('id');
        $totalCompetitions = 0;
        $totalSubmissions = 0;
        $totalStudents = 0;

        if ($teacherClubs->isNotEmpty()) {
            // Get user's students (if they're a teacher)
            $totalStudents = Student::whereIn('teacher_club_id', $teacherClubs)->count();
        }

        // Get latest active models
        $latestModels = Model3d::active()->with('subject')->latest()->take(3)->get();

        // Calculate accuracy
        $accuracy = $totalAnswers > 0 ? round(($correctAnswers / $totalAnswers) * 100, 1) : 0;

        $stats = [
            'total_answers' => $totalAnswers,
            'correct_answers' => $correctAnswers,
            'models_completed' => $modelsCompleted,
            'accuracy' => $accuracy,
            'total_vr_attempts' => $totalVrAttempts,
            'completed_vr_attempts' => $completedVrAttempts,
            'passed_vr_attempts' => $passedVrAttempts,
            'total_competitions' => $totalCompetitions,
            'total_submissions' => $totalSubmissions,
            'total_students' => $totalStudents,
        ];

        return view('client.back.dashboard', compact('stats', 'latestModels'));
    }
}
