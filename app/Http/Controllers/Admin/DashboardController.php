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
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): View
    {
        $stats = Cache::remember('admin.dashboard.stats', 60, function (): array {
            return [
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
                'total_landing_views' => (int) Visitor::sum('landing_views'),
                'visitors_not_converted' => Visitor::notConverted()->count(),
                'active_learners' => 14400,
                'active_clubs' => Club::active()->count(),
                'subscribers' => 100,
            ];
        });

        // Latest admin who has a session today. Filter by the small admin id
        // list so this does not scan every session row and its payload.
        $latestActiveAdmin = null;
        $todayTimestamp = now()->startOfDay()->timestamp;
        $adminIds = User::query()->where('role', 'admin')->pluck('id');
        $recentSession = $adminIds->isEmpty()
            ? null
            : DB::table('sessions')
                ->whereIn('user_id', $adminIds)
                ->where('last_activity', '>=', $todayTimestamp)
                ->orderByDesc('last_activity')
                ->first(['user_id', 'last_activity']);

        if ($recentSession) {
            $adminUser = User::find($recentSession->user_id);
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

        $visitorChartData = $this->visitorChartData();

        $dashboardCompetitions = Competition::query()
            ->with(['club', 'phases' => fn ($query) => $query->orderBy('sequence')])
            ->latest('created_at')
            ->limit(8)
            ->get();

        $mapSchoolCount = Cache::remember('admin.dashboard.schools-map-count', 300, function (): int {
            return School::query()
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->count();
        });

        return view('admin.dashboard', compact(
            'stats',
            'latestActiveAdmin',
            'recentActivities',
            'visitorChartData',
            'dashboardCompetitions',
            'mapSchoolCount'
        ));
    }

    /**
     * School pins for the dashboard map. Loaded when the modal opens so the
     * dashboard HTML does not wait on every school row.
     */
    public function schoolsMap(): JsonResponse
    {
        $schools = Cache::remember('admin.dashboard.schools-map', 300, function () {
            return School::query()
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->orderBy('name')
                ->get(['id', 'name', 'latitude', 'longitude', 'district', 'ward'])
                ->map(fn (School $school): array => [
                    'id' => $school->id,
                    'name' => $school->name,
                    'latitude' => (float) $school->latitude,
                    'longitude' => (float) $school->longitude,
                    'district' => $school->district,
                    'ward' => $school->ward,
                ])
                ->values();
        });

        return response()->json($schools);
    }

    /**
     * @return array{labels: list<string>, unique_visitors: list<int>, landing_views: list<int>}
     */
    private function visitorChartData(): array
    {
        $chartWeeks = 12;
        $rangeStart = Carbon::now()->subWeeks($chartWeeks - 1)->startOfWeek();
        $rows = VisitorWeeklyStat::query()
            ->where('week_start', '>=', $rangeStart->toDateString())
            ->selectRaw('week_start, COUNT(*) as unique_visitors, COALESCE(SUM(views), 0) as landing_views')
            ->groupBy('week_start')
            ->get()
            ->keyBy(fn ($row): string => Carbon::parse($row->week_start)->toDateString());

        $visitorChartData = [
            'labels' => [],
            'unique_visitors' => [],
            'landing_views' => [],
        ];

        for ($i = $chartWeeks - 1; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekStr = $weekStart->toDateString();
            $row = $rows->get($weekStr);
            $visitorChartData['labels'][] = $weekStart->format('d M');
            $visitorChartData['unique_visitors'][] = (int) ($row->unique_visitors ?? 0);
            $visitorChartData['landing_views'][] = (int) ($row->landing_views ?? 0);
        }

        return $visitorChartData;
    }
}
