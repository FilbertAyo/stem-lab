<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Model3d;
use App\Models\Competition;
use App\Models\School;
use App\Models\Club;
use App\Models\ModelQuestionAnswer;
use App\Models\CompetitionSubmission;
use App\Models\VrAssessmentAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): View
    {
        $stats = [
            'total_admins' => User::where('role', 'admin')->count(),
            'total_teachers' => User::where('role', 'teacher')->count(),
            'total_students' => Student::count(),
            'total_3d_models' => Model3d::count(),
            'active_3d_models' => Model3d::where('is_active', true)->count(),
            'total_competitions' => Competition::count(),
            'active_competitions' => Competition::where('is_active', true)->count(),
            'total_schools' => School::count(),
            'total_clubs' => Club::count(),
            'total_answers' => ModelQuestionAnswer::count(),
            'total_correct_answers' => ModelQuestionAnswer::where('is_correct', true)->count(),
        ];

        // Get latest active admin user (based on session last_activity)
        $latestActiveAdmin = null;
        
        // Try to get admin with recent session activity (today)
        $todayTimestamp = now()->startOfDay()->timestamp;
        $recentSession = DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->where('users.role', 'admin')
            ->where('sessions.last_activity', '>=', $todayTimestamp)
            ->orderBy('sessions.last_activity', 'desc')
            ->select('users.*', 'sessions.last_activity')
            ->first();

        if ($recentSession) {
            $adminUser = User::find($recentSession->id);
            $latestActiveAdmin = [
                'user' => $adminUser,
                'last_activity' => Carbon::createFromTimestamp($recentSession->last_activity),
            ];
        } else {
            // If no admin with recent session, get admin with latest created_at
            $latestAdminUser = User::where('role', 'admin')
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($latestAdminUser) {
                $latestActiveAdmin = [
                    'user' => $latestAdminUser,
                    'last_activity' => null,
                ];
            }
        }

        // Get recent activities
        $today = now()->startOfDay();
        $recentActivities = collect();

        // Recent competitions created today
        $todayCompetitions = Competition::where('created_at', '>=', $today)
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($competition) {
                return [
                    'type' => 'competition_created',
                    'title' => $competition->name,
                    'user' => $competition->creator,
                    'time' => $competition->created_at,
                    'icon' => 'ti-trophy',
                    'color' => 'warning',
                ];
            });

        // Recent students registered today
        $todayStudents = Student::where('created_at', '>=', $today)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($student) {
                return [
                    'type' => 'student_registered',
                    'title' => "New student: {$student->name}",
                    'user' => null,
                    'time' => $student->created_at,
                    'icon' => 'ti-user-circle',
                    'color' => 'info',
                ];
            });

        // Recent competition submissions today
        $todaySubmissions = CompetitionSubmission::where('submitted_at', '>=', $today)
            ->with(['participant.competition'])
            ->orderBy('submitted_at', 'desc')
            ->get()
            ->map(function ($submission) {
                $competitionName = $submission->participant?->competition?->name ?? 'Competition';
                return [
                    'type' => 'submission_submitted',
                    'title' => "Submission for: {$competitionName}",
                    'user' => null,
                    'time' => $submission->submitted_at,
                    'icon' => 'ti-file-text',
                    'color' => 'success',
                ];
            });

        // Recent users (teachers/admins) registered today
        $todayUsers = User::whereIn('role', ['teacher', 'admin'])
            ->where('created_at', '>=', $today)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'type' => 'user_registered',
                    'title' => "New {$user->role}: {$user->name}",
                    'user' => $user,
                    'time' => $user->created_at,
                    'icon' => $user->role === 'admin' ? 'ti-shield' : 'ti-user',
                    'color' => $user->role === 'admin' ? 'danger' : 'primary',
                ];
            });

        // Recent VR assessment attempts completed today
        $todayAttempts = VrAssessmentAttempt::where('completed_at', '>=', $today)
            ->whereNotNull('completed_at')
            ->with('user')
            ->orderBy('completed_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($attempt) {
                return [
                    'type' => 'assessment_completed',
                    'title' => "VR Assessment completed by {$attempt->user->name}",
                    'user' => $attempt->user,
                    'time' => $attempt->completed_at,
                    'icon' => 'ti-check-circle',
                    'color' => 'success',
                ];
            });

        // Recent 3D models created today
        $todayModels = Model3d::where('created_at', '>=', $today)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($model) {
                return [
                    'type' => 'model_created',
                    'title' => "New 3D Model: {$model->name}",
                    'user' => null,
                    'time' => $model->created_at,
                    'icon' => 'ti-box',
                    'color' => 'primary',
                ];
            });

        // Combine all activities and sort by time
        $recentActivities = $todayCompetitions
            ->concat($todayStudents)
            ->concat($todaySubmissions)
            ->concat($todayUsers)
            ->concat($todayAttempts)
            ->concat($todayModels)
            ->sortByDesc('time')
            ->take(10);

        return view('admin.dashboard', compact('stats', 'latestActiveAdmin', 'recentActivities'));
    }
}

