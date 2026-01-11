<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Model3d;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\VrExperiment;
use App\Models\WeeklySession;
use App\Models\WeeklySessionReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WeeklySessionController extends Controller
{
    /**
     * Display a listing of weekly sessions.
     */
    public function index(Request $request): View
    {
        $query = WeeklySession::with(['club', 'subject', 'vrExperiment', 'classes', 'model3ds', 'creator'])
            ->orderedByDate();

        // Filter by club
        if ($request->filled('club_id')) {
            $query->where('club_id', $request->club_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('session_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('session_date', '<=', $request->end_date);
        }

        // Search by title or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhereHas('club', function ($clubQuery) use ($search) {
                      $clubQuery->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        $sessions = $query->paginate(20)->withQueryString();

        $clubs = Club::active()->ordered()->get();
        $statuses = ['draft', 'scheduled', 'active', 'completed'];

        return view('admin.weekly-sessions.index', compact('sessions', 'clubs', 'statuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $clubs = Club::active()->ordered()->get();
        $subjects = Subject::active()->ordered()->get();
        $classes = SchoolClass::active()->ordered()->get();
        $vrExperiments = VrExperiment::active()->ordered()->get();
        $model3ds = Model3d::active()->with('subject')->ordered()->get();

        return view('admin.weekly-sessions.create', compact('clubs', 'subjects', 'classes', 'vrExperiments', 'model3ds'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'club_id' => ['required', 'exists:clubs,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'session_date' => ['required', 'date'],
            'vr_experiment_id' => ['nullable', 'exists:vr_experiments,id'],
            'classes' => ['nullable', 'array'],
            'classes.*' => ['exists:school_classes,id'],
            'model3ds' => ['nullable', 'array'],
            'model3ds.*' => ['exists:model3ds,id'],
        ]);

        $validated['created_by'] = Auth::id();
        $validated['status'] = 'scheduled';

        $session = WeeklySession::create($validated);

        if ($request->has('classes')) {
            $session->classes()->sync($request->classes);
        }

        if ($request->has('model3ds')) {
            $session->model3ds()->sync($request->model3ds);
        }

        return redirect()->route('admin.weekly-sessions.index')
            ->with('status', 'Weekly session created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(WeeklySession $weeklySession): View
    {
        $weeklySession->load(['club', 'subject', 'vrExperiment', 'classes', 'model3ds', 'creator']);

        return view('admin.weekly-sessions.show', compact('weeklySession'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WeeklySession $weeklySession): View
    {
        $clubs = Club::active()->ordered()->get();
        $subjects = Subject::active()->ordered()->get();
        $classes = SchoolClass::active()->ordered()->get();
        $vrExperiments = VrExperiment::active()->ordered()->get();
        $model3ds = Model3d::active()->with('subject')->ordered()->get();

        $weeklySession->load(['classes', 'model3ds']);

        return view('admin.weekly-sessions.edit', compact('weeklySession', 'clubs', 'subjects', 'classes', 'vrExperiments', 'model3ds'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WeeklySession $weeklySession): RedirectResponse
    {
        $validated = $request->validate([
            'club_id' => ['required', 'exists:clubs,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'session_date' => ['required', 'date'],
            'vr_experiment_id' => ['nullable', 'exists:vr_experiments,id'],
            'classes' => ['nullable', 'array'],
            'classes.*' => ['exists:school_classes,id'],
            'model3ds' => ['nullable', 'array'],
            'model3ds.*' => ['exists:model3ds,id'],
        ]);

        $weeklySession->update($validated);

        if ($request->has('classes')) {
            $weeklySession->classes()->sync($request->classes);
        } else {
            $weeklySession->classes()->sync([]);
        }

        if ($request->has('model3ds')) {
            $weeklySession->model3ds()->sync($request->model3ds);
        } else {
            $weeklySession->model3ds()->sync([]);
        }

        return redirect()->route('admin.weekly-sessions.show', $weeklySession)
            ->with('status', 'Weekly session updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WeeklySession $weeklySession): RedirectResponse
    {
        $weeklySession->delete();

        return redirect()->route('admin.weekly-sessions.index')
            ->with('status', 'Weekly session deleted successfully.');
    }

    /**
     * Update the status of a weekly session.
     */
    public function updateStatus(Request $request, WeeklySession $weeklySession): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['draft', 'scheduled', 'active', 'completed'])],
        ]);

        $weeklySession->update(['status' => $validated['status']]);

        return redirect()->back()
            ->with('status', 'Session status updated successfully.');
    }

    /**
     * Display a listing of all session reports.
     */
    public function reportsIndex(Request $request): View
    {
        $query = WeeklySessionReport::with(['weeklySession.club', 'weeklySession.subject', 'teacherClub.club', 'teacher', 'photos'])
            ->orderBy('submitted_at', 'desc');

        // Filter by club
        if ($request->filled('club_id')) {
            $query->whereHas('weeklySession', function ($q) use ($request) {
                $q->where('club_id', $request->club_id);
            });
        }

        // Filter by date range (session date)
        if ($request->filled('start_date')) {
            $query->whereHas('weeklySession', function ($q) use ($request) {
                $q->where('session_date', '>=', $request->start_date);
            });
        }
        if ($request->filled('end_date')) {
            $query->whereHas('weeklySession', function ($q) use ($request) {
                $q->where('session_date', '<=', $request->end_date);
            });
        }

        // Filter by teacher (search by name)
        if ($request->filled('teacher')) {
            $query->whereHas('teacher', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->teacher . '%');
            });
        }

        // Filter by session title
        if ($request->filled('session')) {
            $query->whereHas('weeklySession', function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->session . '%');
            });
        }

        // Filter by has photos
        if ($request->filled('has_photos')) {
            if ($request->has_photos === 'yes') {
                $query->has('photos');
            } elseif ($request->has_photos === 'no') {
                $query->doesntHave('photos');
            }
        }

        $reports = $query->paginate(25)->withQueryString();

        // Get clubs for filter dropdown
        $clubs = Club::active()->ordered()->get();

        // Calculate statistics
        $totalReports = WeeklySessionReport::count();
        $reportsThisWeek = WeeklySessionReport::where('submitted_at', '>=', now()->startOfWeek())->count();
        $reportsThisMonth = WeeklySessionReport::where('submitted_at', '>=', now()->startOfMonth())->count();
        $avgAttendanceThisMonth = WeeklySessionReport::where('submitted_at', '>=', now()->startOfMonth())
            ->whereNotNull('attendance_count')
            ->avg('attendance_count');
        $totalPhotos = \App\Models\WeeklySessionReportPhoto::count();

        $statistics = [
            'total' => $totalReports,
            'this_week' => $reportsThisWeek,
            'this_month' => $reportsThisMonth,
            'avg_attendance' => round($avgAttendanceThisMonth ?? 0, 1),
            'total_photos' => $totalPhotos,
        ];

        return view('admin.weekly-sessions.reports.index', compact('reports', 'clubs', 'statistics'));
    }

    /**
     * Display the specified session report.
     */
    public function showReport(WeeklySessionReport $report): View
    {
        $report->load([
            'weeklySession.club',
            'weeklySession.subject',
            'weeklySession.classes',
            'weeklySession.vrExperiment',
            'weeklySession.model3ds',
            'teacherClub.club',
            'teacherClub.school',
            'teacher',
            'photos'
        ]);

        return view('admin.weekly-sessions.reports.show', compact('report'));
    }
}
