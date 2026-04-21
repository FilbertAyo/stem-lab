<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Project;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherClub;
use App\Models\VrExperiment;
use App\Models\WeeklySession;
use App\Models\WeeklySessionReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

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
        $projects = Project::active()->ordered()->get();

        return view('admin.weekly-sessions.create', compact('clubs', 'subjects', 'classes', 'vrExperiments', 'projects'));
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
            'session_end_date' => ['required', 'date', 'after_or_equal:session_date'],
            'vr_experiment_id' => ['nullable', 'exists:vr_experiments,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'project_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:10240', 'required_without:project_id'],
            'status' => ['required', 'string', Rule::in(['draft', 'scheduled', 'active', 'completed'])],
            'classes' => ['nullable', 'array'],
            'classes.*' => ['exists:school_classes,id'],
        ]);

        if ($request->hasFile('project_pdf')) {
            $validated['project_pdf'] = $request->file('project_pdf')->store('weekly-sessions/project-pdfs', 'public');
        }

        $validated['created_by'] = Auth::id();
        $session = WeeklySession::create($validated);

        if ($request->has('classes')) {
            $session->classes()->sync($request->classes);
        }

        return redirect()->route('admin.weekly-sessions.index')
            ->with('status', 'Weekly session created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(WeeklySession $weeklySession): View
    {
        $weeklySession->load(['club', 'subject', 'vrExperiment', 'classes', 'project', 'creator']);

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
        $projects = Project::active()->ordered()->get();

        $weeklySession->load(['classes', 'project']);

        return view('admin.weekly-sessions.edit', compact('weeklySession', 'clubs', 'subjects', 'classes', 'vrExperiments', 'projects'));
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
            'session_end_date' => ['required', 'date', 'after_or_equal:session_date'],
            'vr_experiment_id' => ['nullable', 'exists:vr_experiments,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'project_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'status' => ['required', 'string', Rule::in(['draft', 'scheduled', 'active', 'completed'])],
            'classes' => ['nullable', 'array'],
            'classes.*' => ['exists:school_classes,id'],
        ]);

        if (empty($validated['project_id']) && !$request->hasFile('project_pdf') && empty($weeklySession->project_pdf)) {
            return redirect()
                ->back()
                ->withErrors(['project_pdf' => 'Please upload a project PDF when no project is selected.'])
                ->withInput();
        }

        if (!empty($validated['project_id']) && $request->boolean('remove_project_pdf') && $weeklySession->project_pdf) {
            Storage::disk('public')->delete($weeklySession->project_pdf);
            $validated['project_pdf'] = null;
        } elseif ($request->hasFile('project_pdf')) {
            if ($weeklySession->project_pdf) {
                Storage::disk('public')->delete($weeklySession->project_pdf);
            }
            $validated['project_pdf'] = $request->file('project_pdf')->store('weekly-sessions/project-pdfs', 'public');
        } elseif ($request->boolean('remove_project_pdf')) {
            if ($weeklySession->project_pdf) {
                Storage::disk('public')->delete($weeklySession->project_pdf);
            }
            $validated['project_pdf'] = null;
        }

        $weeklySession->update($validated);

        if ($request->has('classes')) {
            $weeklySession->classes()->sync($request->classes);
        } else {
            $weeklySession->classes()->sync([]);
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
        $query = $this->buildReportsQuery($request);

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
        $weeklySessions = WeeklySession::with('club')
            ->where('status', '!=', 'draft')
            ->whereBetween('session_date', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()])
            ->orderBy('session_date')
            ->get();
        $teacherClubs = TeacherClub::with(['user', 'club', 'school'])
            ->where('status', 'approved')
            ->get()
            ->sortBy(fn ($teacherClub) => strtolower(($teacherClub->user->name ?? '').' '.$teacherClub->club->name))
            ->values();

        return view('admin.weekly-sessions.reports.index', compact('reports', 'clubs', 'statistics', 'weeklySessions', 'teacherClubs'));
    }

    public function storeReport(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'weekly_session_id' => ['nullable', 'exists:weekly_sessions,id'],
            'teacher_club_id' => ['required', 'exists:teacher_clubs,id'],
            'attendance_count' => ['nullable', 'integer', 'min:0'],
            'total_students_registered' => ['nullable', 'integer', 'min:0'],
            'girls_present' => ['nullable', 'integer', 'min:0'],
            'students_learned' => ['nullable', 'string', 'max:5000'],
            'challenge_types' => ['nullable', 'array'],
            'challenge_types.*' => ['string', Rule::in([
                'internet',
                'lack_of_equipment',
                'power_outage',
                'low_attendance',
                'time_constraints',
                'insufficient_materials',
                'space_limitations',
                'other',
            ])],
            'challenge_other' => ['nullable', 'string', 'max:255'],
            'challenge_description' => ['nullable', 'string', 'max:5000'],
            'additional_comments' => ['nullable', 'string', 'max:5000'],
            'outside_weekly_session_project' => ['nullable', 'boolean'],
            'outside_weekly_session_notes' => ['nullable', 'string', 'max:5000'],
            'conducted_at' => ['nullable', 'date'],
        ]);

        $teacherClub = TeacherClub::query()->findOrFail($validated['teacher_club_id']);
        $weeklySession = isset($validated['weekly_session_id']) ? WeeklySession::query()->findOrFail($validated['weekly_session_id']) : null;

        if ($weeklySession && (int) $teacherClub->club_id !== (int) $weeklySession->club_id) {
            return redirect()
                ->route('admin.weekly-sessions.reports.index')
                ->with('error', 'The selected teacher is not assigned to the selected session club.')
                ->withInput();
        }

        $outsideWeeklySession = (bool) ($validated['outside_weekly_session_project'] ?? false);
        $outsideNotes = $validated['outside_weekly_session_notes'] ?? null;
        $challengeTypes = $validated['challenge_types'] ?? [];
        $hasChallenges = ! empty($challengeTypes) || filled($validated['challenge_description'] ?? null) || filled($validated['challenge_other'] ?? null);

        WeeklySessionReport::updateOrCreate(
            [
                'weekly_session_id' => $weeklySession?->id,
                'teacher_club_id' => $teacherClub->id,
            ],
            [
                'user_id' => $teacherClub->user_id,
                'attendance_count' => $validated['attendance_count'] ?? null,
                'total_students_registered' => $validated['total_students_registered'] ?? null,
                'girls_present' => $validated['girls_present'] ?? null,
                'students_learned' => $validated['students_learned'] ?? null,
                'had_challenges' => $hasChallenges,
                'challenge_types' => ! empty($challengeTypes) ? $challengeTypes : null,
                'challenge_other' => $validated['challenge_other'] ?? null,
                'challenge_description' => $validated['challenge_description'] ?? null,
                'additional_comments' => $validated['additional_comments'] ?? null,
                'has_active_projects' => $outsideWeeklySession || ! $weeklySession ? true : null,
                'project_description' => $outsideNotes,
                'activity_type' => ($outsideWeeklySession || ! $weeklySession) ? 'project_work' : null,
                'conducted_at' => isset($validated['conducted_at']) ? Carbon::parse($validated['conducted_at']) : now(),
                'submitted_at' => now(),
            ]
        );

        return redirect()
            ->route('admin.weekly-sessions.reports.index')
            ->with('status', 'Call report saved successfully.');
    }

    public function downloadWeeklyPdf(Request $request): Response
    {
        $range = $this->resolveWeekRange($request);

        $reports = $this->buildReportsQuery($request)
            ->where(function ($query) use ($range) {
                $query->whereHas('weeklySession', function ($sessionQuery) use ($range) {
                    $sessionQuery->whereBetween('session_date', [$range['start']->toDateString(), $range['end']->toDateString()]);
                })->orWhereBetween('conducted_at', [$range['start']->toDateTimeString(), $range['end']->toDateTimeString()]);
            })
            ->get();

        $attendanceTotal = (int) $reports->sum(fn ($report) => (int) ($report->attendance_count ?? 0));
        $attendanceAverage = $reports->count() > 0
            ? round($reports->avg(fn ($report) => (float) ($report->attendance_count ?? 0)), 1)
            : 0;

        $schoolBreakdown = $reports
            ->groupBy(fn ($report) => $report->teacherClub?->school?->name ?? 'Unknown School')
            ->map(function ($schoolReports) {
                return [
                    'reports' => $schoolReports->count(),
                    'attendance_total' => (int) $schoolReports->sum(fn ($r) => (int) ($r->attendance_count ?? 0)),
                    'attendance_avg' => round($schoolReports->avg(fn ($r) => (float) ($r->attendance_count ?? 0)), 1),
                ];
            })
            ->sortKeys();

        $challenges = $reports
            ->pluck('challenge_description')
            ->filter(fn ($value) => filled($value))
            ->values();

        $otherIssues = $reports
            ->flatMap(function ($report) {
                return collect([
                    $report->additional_comments,
                    $report->project_description,
                ]);
            })
            ->filter(fn ($value) => filled($value))
            ->values();

        $pdf = Pdf::loadView('admin.weekly-sessions.reports.weekly-summary-pdf', [
            'reports' => $reports,
            'startDate' => $range['start'],
            'endDate' => $range['end'],
            'stats' => [
                'count' => $reports->count(),
                'attendance_total' => $attendanceTotal,
                'attendance_average' => $attendanceAverage,
            ],
            'schoolBreakdown' => $schoolBreakdown,
            'challenges' => $challenges,
            'otherIssues' => $otherIssues,
        ])->setPaper('a4', 'portrait');

        $fileName = sprintf(
            'weekly-session-report-%s-to-%s.pdf',
            $range['start']->format('Y-m-d'),
            $range['end']->format('Y-m-d')
        );

        return $pdf->download($fileName);
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

    private function buildReportsQuery(Request $request)
    {
        $query = WeeklySessionReport::with(['weeklySession.club', 'weeklySession.subject', 'teacherClub.club', 'teacherClub.school', 'teacher', 'photos'])
            ->orderBy('submitted_at', 'desc');

        if ($request->filled('club_id')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('weeklySession', function ($sessionQuery) use ($request) {
                    $sessionQuery->where('club_id', $request->club_id);
                })->orWhereHas('teacherClub', function ($teacherClubQuery) use ($request) {
                    $teacherClubQuery->where('club_id', $request->club_id);
                });
            });
        }

        if ($request->filled('start_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('weeklySession', function ($sessionQuery) use ($request) {
                    $sessionQuery->where('session_date', '>=', $request->start_date);
                })->orWhereDate('conducted_at', '>=', $request->start_date);
            });
        }

        if ($request->filled('end_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('weeklySession', function ($sessionQuery) use ($request) {
                    $sessionQuery->where('session_date', '<=', $request->end_date);
                })->orWhereDate('conducted_at', '<=', $request->end_date);
            });
        }

        if ($request->filled('teacher')) {
            $query->whereHas('teacher', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->teacher . '%');
            });
        }

        if ($request->filled('session')) {
            $query->whereHas('weeklySession', function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->session . '%');
            });
        }

        if ($request->filled('has_photos')) {
            if ($request->has_photos === 'yes') {
                $query->has('photos');
            } elseif ($request->has_photos === 'no') {
                $query->doesntHave('photos');
            }
        }

        return $query;
    }

    private function resolveWeekRange(Request $request): array
    {
        $start = $request->filled('start_date')
            ? CarbonImmutable::parse($request->input('start_date'))->startOfDay()
            : CarbonImmutable::now()->startOfWeek();
        $end = $request->filled('end_date')
            ? CarbonImmutable::parse($request->input('end_date'))->endOfDay()
            : $start->addDays(6)->endOfDay();

        return ['start' => $start, 'end' => $end];
    }
}
