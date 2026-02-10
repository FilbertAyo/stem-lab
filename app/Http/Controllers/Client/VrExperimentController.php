<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\CacheService;
use App\Models\Subject;
use App\Models\VrExperiment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VrExperimentController extends Controller
{
    /**
     * Display a listing of the VR experiments available to students.
     */
    public function index(Request $request): View
    {
        $subjectId = $request->input('subject');
        $search = $request->input('search');
        $sort = $request->input('sort', 'by_subject');

        // Always use flat paginated list: "All Experiments" = all; sidebar subject = filter by that subject (no categories, no "View all")
        $showGroupedView = false;

        // Get cached subjects
        $subjects = CacheService::getActiveSubjects();

        // Get total count for stats
        $totalExperiments = VrExperiment::where('is_active', true)->count();

        // Paginated view with optional subject/search filter and sorting
        $query = VrExperiment::active()
            ->withSubject()
            ->when($subjectId, fn ($q) => $q->forSubject($subjectId))
            ->when($search, fn ($q) => $q->search($search));

        // Apply sorting
        $query = match ($sort) {
            'oldest' => $query->oldest(),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            default => $query->latest(), // newest
        };

        $vrExperiments = $query->paginate(12)->withQueryString();

        return view('client.back.vr-experiments.index', [
            'vrExperiments' => $vrExperiments,
            'experimentsBySubject' => collect(),
            'subjectsWithExperiments' => collect(),
            'showGroupedView' => false,
            'subjects' => $subjects,
            'selectedSubject' => $subjectId,
            'search' => $search,
            'sort' => $sort,
            'totalExperiments' => $totalExperiments,
        ]);
    }

    /**
     * Display a specific VR experiment.
     */
    public function show(VrExperiment $vrExperiment): View
    {
        $vrExperiment->load('subject');
        $subjects = CacheService::getActiveSubjects();

        return view('client.back.vr-experiments.show', compact('vrExperiment', 'subjects'));
    }
}

