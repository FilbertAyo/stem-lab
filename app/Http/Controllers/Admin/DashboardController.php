<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Competition;
use App\Models\Model3d;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Models\Visitor;
use App\Models\VisitorWeeklyStat;
use App\Models\VrAssessmentAttempt;
use App\Models\VrExperiment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

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
            'total_students' => User::where('role', 'student')->count(),
            'students_in_club' => Student::count(),
            'total_3d_models' => Model3d::count(),
            'active_3d_models' => Model3d::where('is_active', true)->count(),
            'total_vr_experiments' => VrExperiment::count(),
            'active_vr_experiments' => VrExperiment::where('is_active', true)->count(),
            'total_competitions' => Competition::count(),
            'active_competitions' => Competition::where('is_active', true)->count(),
            'total_schools' => School::count(),
            'total_visitors' => Visitor::count(),
            'visitors_today' => Visitor::whereDate('last_seen_at', today())->count(),
            'total_landing_views' => Visitor::sum('landing_views'),
            'visitors_not_converted' => Visitor::notConverted()->count(),
            'active_learners' => 14400,
            'active_clubs' => Club::active()->count(),
            'subscribers' => 100,
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
            ->concat($todayUsers)
            ->concat($todayAttempts)
            ->concat($todayModels)
            ->sortByDesc('time')
            ->take(10);

        // Weekly visitor data for charts (last 12 weeks)
        $chartWeeks = 12;
        $visitorChartData = [
            'labels' => [],
            'unique_visitors' => [],
            'landing_views' => [],
        ];
        for ($i = $chartWeeks - 1; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekStr = $weekStart->toDateString();
            $visitorChartData['labels'][] = $weekStart->format('d M');
            $visitorChartData['unique_visitors'][] = VisitorWeeklyStat::where('week_start', $weekStr)->count();
            $visitorChartData['landing_views'][] = (int) VisitorWeeklyStat::where('week_start', $weekStr)->sum('views');
        }

        $dashboardCompetitions = Competition::query()
            ->with(['club', 'phases' => fn ($query) => $query->orderBy('sequence')])
            ->latest('created_at')
            ->limit(8)
            ->get();

        $mapSchools = School::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('name')
            ->get(['id', 'name', 'latitude', 'longitude', 'district', 'ward']);

        return view('admin.dashboard', compact(
            'stats',
            'latestActiveAdmin',
            'recentActivities',
            'visitorChartData',
            'dashboardCompetitions',
            'mapSchools'
        ));
    }
}
