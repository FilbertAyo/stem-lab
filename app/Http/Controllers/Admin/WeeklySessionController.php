<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Model3d;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\VrExperiment;
use App\Models\WeeklySession;
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

        $sessions = $query->paginate(15)->withQueryString();

        // Get data for filters
        $clubs = Club::active()->ordered()->get();
        $statuses = ['draft', 'scheduled', 'active', 'completed'];

        return view('admin.weekly-sessions.index', compact('sessions', 'clubs', 'statuses'));
    }

    /**
     * Show the form for creating a new weekly session.
     */
    public function create(): View
    {
        $clubs = Club::active()->ordered()->get();
        $subjects = Subject::active()->ordered()->get();
        $classes = SchoolClass::active()->ordered()->get();
        $vrExperiments = VrExperiment::active()->with(['schoolClass', 'subject'])->orderBy('name')->get();
        $model3ds = Model3d::active()->with('subject')->ordered()->get();

        return view('admin.weekly-sessions.create', compact('clubs', 'subjects', 'classes', 'vrExperiments', 'model3ds'));
    }

    /**
     * Store a newly created weekly session.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'club_id' => ['required', 'exists:clubs,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'session_date' => ['required', 'date'],
            'classes' => ['nullable', 'array'],
            'classes.*' => ['exists:school_classes,id'],
            'vr_experiment_id' => ['nullable', 'exists:vr_experiments,id'],
            'model3ds' => ['nullable', 'array'],
            'model3ds.*' => ['exists:model3ds,id'],
            'status' => ['nullable', Rule::in(['draft', 'scheduled', 'active', 'completed'])],
        ]);

        // Set created_by to current user
        $validated['created_by'] = Auth::id();
        $validated['status'] = $validated['status'] ?? 'draft';

        // Extract pivot data
        $classes = $validated['classes'] ?? [];
        $model3ds = $validated['model3ds'] ?? [];
        unset($validated['classes'], $validated['model3ds']);

        // Create session
        $session = WeeklySession::create($validated);

        // Attach classes and 3D models
        if (!empty($classes)) {
            $session->classes()->attach($classes);
        }
        if (!empty($model3ds)) {
            $session->model3ds()->attach($model3ds);
        }

        return redirect()->route('admin.weekly-sessions.index')
            ->with('status', 'Weekly session created successfully.');
    }

    /**
     * Display the specified weekly session.
     */
    public function show(WeeklySession $weeklySession): View
    {
        $weeklySession->load(['club', 'subject', 'vrExperiment', 'classes', 'model3ds', 'creator']);

        return view('admin.weekly-sessions.show', compact('weeklySession'));
    }

    /**
     * Show the form for editing the specified weekly session.
     */
    public function edit(WeeklySession $weeklySession): View
    {
        $weeklySession->load(['classes', 'model3ds']);
        $clubs = Club::active()->ordered()->get();
        $subjects = Subject::active()->ordered()->get();
        $classes = SchoolClass::active()->ordered()->get();
        $vrExperiments = VrExperiment::active()->with(['schoolClass', 'subject'])->orderBy('name')->get();
        $model3ds = Model3d::active()->with('subject')->ordered()->get();

        return view('admin.weekly-sessions.edit', compact('weeklySession', 'clubs', 'subjects', 'classes', 'vrExperiments', 'model3ds'));
    }

    /**
     * Update the specified weekly session.
     */
    public function update(Request $request, WeeklySession $weeklySession): RedirectResponse
    {
        $validated = $request->validate([
            'club_id' => ['required', 'exists:clubs,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'session_date' => ['required', 'date'],
            'classes' => ['nullable', 'array'],
            'classes.*' => ['exists:school_classes,id'],
            'vr_experiment_id' => ['nullable', 'exists:vr_experiments,id'],
            'model3ds' => ['nullable', 'array'],
            'model3ds.*' => ['exists:model3ds,id'],
            'status' => ['required', Rule::in(['draft', 'scheduled', 'active', 'completed'])],
        ]);

        // Extract pivot data
        $classes = $validated['classes'] ?? [];
        $model3ds = $validated['model3ds'] ?? [];
        unset($validated['classes'], $validated['model3ds']);

        // Update session
        $weeklySession->update($validated);

        // Sync classes and 3D models
        $weeklySession->classes()->sync($classes);
        $weeklySession->model3ds()->sync($model3ds);

        return redirect()->route('admin.weekly-sessions.index')
            ->with('status', 'Weekly session updated successfully.');
    }

    /**
     * Remove the specified weekly session.
     */
    public function destroy(WeeklySession $weeklySession): RedirectResponse
    {
        $weeklySession->delete();

        return redirect()->route('admin.weekly-sessions.index')
            ->with('status', 'Weekly session deleted successfully.');
    }

    /**
     * Update the status of the specified weekly session.
     */
    public function updateStatus(Request $request, WeeklySession $weeklySession): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['draft', 'scheduled', 'active', 'completed'])],
        ]);

        $weeklySession->update(['status' => $validated['status']]);

        return redirect()->back()
            ->with('status', 'Session status updated successfully.');
    }
}
