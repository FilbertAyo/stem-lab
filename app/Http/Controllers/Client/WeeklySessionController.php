<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\TeacherClub;
use App\Models\WeeklySession;
use App\Models\WeeklySessionReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WeeklySessionController extends Controller
{
    /**
     * Display a listing of weekly sessions for the teacher's clubs.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Get teacher's approved club memberships
        $teacherClubs = $user->teacherClubs()
            ->approved()
            ->with('club')
            ->get();

        $clubIds = $teacherClubs->pluck('club_id')->toArray();

        if (empty($clubIds)) {
            // Teacher has no clubs, return empty list
            return view('client.back.weekly-sessions.index', [
                'sessions' => collect()->paginate(15),
                'teacherClubs' => collect(),
                'statusFilter' => $request->get('status', 'all'),
            ]);
        }

        // Build query for sessions visible to teachers
        $query = WeeklySession::visibleToTeachers($clubIds)
            ->with(['club', 'subject', 'vrExperiment', 'classes', 'model3ds'])
            ->orderedByDate('asc');

        // Filter by status
        $statusFilter = $request->get('status', 'all');
        if ($statusFilter !== 'all') {
            if ($statusFilter === 'upcoming') {
                $query->upcoming();
            } elseif ($statusFilter === 'active') {
                $query->withStatus('active');
            } elseif ($statusFilter === 'completed') {
                $query->where(function ($q) {
                    $q->withStatus('completed')
                      ->orWhere(function ($subQ) {
                          $subQ->past()->where('status', 'scheduled');
                      });
                });
            } elseif ($statusFilter === 'past') {
                $query->past();
            } else {
                $query->withStatus($statusFilter);
            }
        }

        // Filter by club
        if ($request->filled('club_id')) {
            $query->where('club_id', $request->club_id);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('session_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('session_date', '<=', $request->end_date);
        }

        $sessions = $query->paginate(15)->withQueryString();

        // Load reports for each session to check if teacher has submitted one
        $sessionIds = $sessions->pluck('id')->toArray();
        $teacherClubIds = $teacherClubs->pluck('id')->toArray();
        
        $reports = WeeklySessionReport::whereIn('weekly_session_id', $sessionIds)
            ->whereIn('teacher_club_id', $teacherClubIds)
            ->get()
            ->keyBy(function ($report) {
                return $report->weekly_session_id . '_' . $report->teacher_club_id;
            });

        // Attach report status to each session
        foreach ($sessions as $session) {
            $session->teacherReport = null;
            foreach ($teacherClubs as $teacherClub) {
                $key = $session->id . '_' . $teacherClub->id;
                if ($reports->has($key)) {
                    $session->teacherReport = $reports->get($key);
                    break;
                }
            }
        }

        return view('client.back.weekly-sessions.index', [
            'sessions' => $sessions,
            'teacherClubs' => $teacherClubs,
            'statusFilter' => $statusFilter,
        ]);
    }

    /**
     * Display the specified weekly session.
     */
    public function show(WeeklySession $weeklySession): View
    {
        $user = Auth::user();

        // Verify teacher belongs to the session's club
        $teacherClub = $user->teacherClubs()
            ->approved()
            ->where('club_id', $weeklySession->club_id)
            ->first();

        if (!$teacherClub) {
            abort(403, 'You do not have access to this session.');
        }

        // Verify session is visible (not draft)
        if ($weeklySession->status === 'draft') {
            abort(404);
        }

        // Load relationships
        $weeklySession->load(['club', 'subject', 'vrExperiment', 'classes', 'model3ds']);

        // Get students assigned to this session
        // Students whose class matches session classes AND belong to teacher's teacher_club_id
        $sessionClassIds = $weeklySession->classes->pluck('id')->toArray();
        $students = collect();

        if (!empty($sessionClassIds)) {
            $students = Student::where('teacher_club_id', $teacherClub->id)
                ->whereIn('school_class_id', $sessionClassIds)
                ->with('schoolClass')
                ->orderBy('name')
                ->get();
        }

        // Get student counts by class
        $studentCountsByClass = [];
        foreach ($weeklySession->classes as $class) {
            $studentCountsByClass[$class->id] = Student::where('teacher_club_id', $teacherClub->id)
                ->where('school_class_id', $class->id)
                ->count();
        }

        // Check if teacher has submitted a report
        $report = WeeklySessionReport::where('weekly_session_id', $weeklySession->id)
            ->where('teacher_club_id', $teacherClub->id)
            ->first();

        return view('client.back.weekly-sessions.show', [
            'weeklySession' => $weeklySession,
            'teacherClub' => $teacherClub,
            'students' => $students,
            'studentCountsByClass' => $studentCountsByClass,
            'report' => $report,
        ]);
    }

    /**
     * Show the form for creating a new report.
     */
    public function createReport(WeeklySession $weeklySession): View
    {
        $user = Auth::user();

        // Verify teacher belongs to the session's club
        $teacherClub = $user->teacherClubs()
            ->approved()
            ->where('club_id', $weeklySession->club_id)
            ->first();

        if (!$teacherClub) {
            abort(403, 'You do not have access to this session.');
        }

        // Check if report already exists
        $report = WeeklySessionReport::where('weekly_session_id', $weeklySession->id)
            ->where('teacher_club_id', $teacherClub->id)
            ->first();

        if ($report) {
            return redirect()->route('client.weekly-sessions.report.edit', $weeklySession);
        }

        $weeklySession->load(['club', 'classes']);

        return view('client.back.weekly-sessions.report', [
            'weeklySession' => $weeklySession,
            'teacherClub' => $teacherClub,
            'report' => null,
        ]);
    }

    /**
     * Store a newly created report.
     */
    public function storeReport(Request $request, WeeklySession $weeklySession): RedirectResponse
    {
        $user = Auth::user();

        // Verify teacher belongs to the session's club
        $teacherClub = $user->teacherClubs()
            ->approved()
            ->where('club_id', $weeklySession->club_id)
            ->first();

        if (!$teacherClub) {
            abort(403, 'You do not have access to this session.');
        }

        // Check if report already exists
        $existingReport = WeeklySessionReport::where('weekly_session_id', $weeklySession->id)
            ->where('teacher_club_id', $teacherClub->id)
            ->first();

        if ($existingReport) {
            return redirect()->route('client.weekly-sessions.report.edit', $weeklySession)
                ->with('error', 'A report already exists for this session. Please edit it instead.');
        }

        $validated = $request->validate([
            'attendance_count' => ['nullable', 'integer', 'min:0'],
            'feedback' => ['nullable', 'string', 'max:2000'],
            'engagement_notes' => ['nullable', 'string', 'max:2000'],
            'conducted_at' => ['nullable', 'date'],
        ]);

        $validated['weekly_session_id'] = $weeklySession->id;
        $validated['teacher_club_id'] = $teacherClub->id;
        $validated['user_id'] = $user->id;

        if (empty($validated['conducted_at'])) {
            $validated['conducted_at'] = now();
        }

        WeeklySessionReport::create($validated);

        return redirect()->route('client.weekly-sessions.show', $weeklySession)
            ->with('status', 'Report submitted successfully.');
    }

    /**
     * Show the form for editing an existing report.
     */
    public function editReport(WeeklySession $weeklySession): View
    {
        $user = Auth::user();

        // Verify teacher belongs to the session's club
        $teacherClub = $user->teacherClubs()
            ->approved()
            ->where('club_id', $weeklySession->club_id)
            ->first();

        if (!$teacherClub) {
            abort(403, 'You do not have access to this session.');
        }

        $report = WeeklySessionReport::where('weekly_session_id', $weeklySession->id)
            ->where('teacher_club_id', $teacherClub->id)
            ->first();

        if (!$report) {
            return redirect()->route('client.weekly-sessions.report.create', $weeklySession);
        }

        $weeklySession->load(['club', 'classes']);

        return view('client.back.weekly-sessions.report', [
            'weeklySession' => $weeklySession,
            'teacherClub' => $teacherClub,
            'report' => $report,
        ]);
    }

    /**
     * Update an existing report.
     */
    public function updateReport(Request $request, WeeklySession $weeklySession): RedirectResponse
    {
        $user = Auth::user();

        // Verify teacher belongs to the session's club
        $teacherClub = $user->teacherClubs()
            ->approved()
            ->where('club_id', $weeklySession->club_id)
            ->first();

        if (!$teacherClub) {
            abort(403, 'You do not have access to this session.');
        }

        $report = WeeklySessionReport::where('weekly_session_id', $weeklySession->id)
            ->where('teacher_club_id', $teacherClub->id)
            ->first();

        if (!$report) {
            return redirect()->route('client.weekly-sessions.report.create', $weeklySession);
        }

        $validated = $request->validate([
            'attendance_count' => ['nullable', 'integer', 'min:0'],
            'feedback' => ['nullable', 'string', 'max:2000'],
            'engagement_notes' => ['nullable', 'string', 'max:2000'],
            'conducted_at' => ['nullable', 'date'],
        ]);

        $report->update($validated);

        return redirect()->route('client.weekly-sessions.show', $weeklySession)
            ->with('status', 'Report updated successfully.');
    }
}
