<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VrExperiment;
use App\Models\VrQuestionBank;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VrQuestionBankController extends Controller
{
    /**
     * Display a listing of all question banks.
     */
    public function index(Request $request): View
    {
        $query = VrQuestionBank::with(['vrExperiment.schoolClass', 'vrExperiment.subject'])
            ->withCount('questions');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhereHas('vrExperiment', function ($expQuery) use ($search) {
                      $expQuery->where('name', 'like', '%' . $search . '%')
                               ->orWhereHas('subject', function ($subjectQuery) use ($search) {
                                   $subjectQuery->where('subject_name', 'like', '%' . $search . '%');
                               })
                               ->orWhereHas('schoolClass', function ($classQuery) use ($search) {
                                   $classQuery->where('name', 'like', '%' . $search . '%');
                               });
                  });
            });
        }

        $questionBanks = $query->latest()->paginate(10)->withQueryString();
            
        return view('admin.vr-assessments.question-banks.index', compact('questionBanks'));
    }

    /**
     * Show the form for creating a new question bank.
     */
    public function create(): View
    {
        $experiments = VrExperiment::with(['schoolClass', 'subject'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        return view('admin.vr-assessments.question-banks.create', compact('experiments'));
    }

    /**
     * Store a newly created question bank.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vr_experiment_id' => ['required', 'exists:vr_experiments,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'time_limit' => ['nullable', 'integer', 'min:1', 'max:180'],
            'passing_score' => ['required', 'integer', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        // Set is_active
        $validated['is_active'] = $request->has('is_active');

        // Get the next display order
        $maxOrder = VrQuestionBank::where('vr_experiment_id', $validated['vr_experiment_id'])->max('display_order') ?? 0;
        $validated['display_order'] = $maxOrder + 1;

        $questionBank = VrQuestionBank::create($validated);

        return redirect()->route('admin.vr-assessments.question-banks.show', $questionBank)
            ->with('success', 'Question bank created successfully. Now add questions to it.');
    }

    /**
     * Display the specified question bank with its questions.
     */
    public function show(VrQuestionBank $questionBank): View
    {
        $questionBank->load(['vrExperiment.schoolClass', 'vrExperiment.subject', 'questions' => function ($query) {
            $query->orderBy('order');
        }]);

        return view('admin.vr-assessments.question-banks.show', compact('questionBank'));
    }

    /**
     * Show the form for editing the specified question bank.
     */
    public function edit(VrQuestionBank $questionBank): View
    {
        $experiments = VrExperiment::with(['schoolClass', 'subject'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.vr-assessments.question-banks.edit', compact('questionBank', 'experiments'));
    }

    /**
     * Update the specified question bank.
     */
    public function update(Request $request, VrQuestionBank $questionBank): RedirectResponse
    {
        $validated = $request->validate([
            'vr_experiment_id' => ['required', 'exists:vr_experiments,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'time_limit' => ['nullable', 'integer', 'min:1', 'max:180'],
            'passing_score' => ['required', 'integer', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        // Set is_active
        $validated['is_active'] = $request->has('is_active');

        $questionBank->update($validated);

        return redirect()->route('admin.vr-assessments.question-banks.index')
            ->with('success', 'Question bank updated successfully.');
    }

    /**
     * Remove the specified question bank.
     */
    public function destroy(VrQuestionBank $questionBank): RedirectResponse
    {
        $questionBank->delete();

        return redirect()->route('admin.vr-assessments.question-banks.index')
            ->with('success', 'Question bank deleted successfully.');
    }

    /**
     * Toggle the active status of a question bank.
     */
    public function toggleStatus(VrQuestionBank $questionBank): RedirectResponse
    {
        $questionBank->update(['is_active' => !$questionBank->is_active]);

        $status = $questionBank->is_active ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "Question bank {$status} successfully.");
    }
}

