<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\CacheService;
use App\Models\Subject;
use App\Models\VrExperiment;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VrExperimentController extends Controller
{
    /**
     * Get static VR experiments from config (not from DB).
     *
     * @return \Illuminate\Support\Collection<int, object>
     */
    protected function getStaticExperiments(): \Illuminate\Support\Collection
    {
        $items = config('static_vr_experiments', []);
        return collect($items)->map(fn ($item) => (object) array_merge($item, ['is_static' => true]));
    }
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

        $staticExperiments = $this->getStaticExperiments();
        $staticCount = $staticExperiments->count();
        $dbTotal = VrExperiment::where('is_active', true)->count();
        $totalExperiments = $staticCount + $dbTotal;

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

        $perPage = 12;
        $showStaticOnFirstPage = ! $subjectId && ! $search && $staticCount > 0;
        $currentPage = (int) request('page', 1);

        if ($showStaticOnFirstPage && $currentPage === 1) {
            $dbTake = $perPage - $staticCount;
            $dbItems = $query->take($dbTake)->get();
            $allItems = $staticExperiments->merge($dbItems);
            $vrExperiments = new \Illuminate\Pagination\LengthAwarePaginator(
                $allItems,
                $totalExperiments,
                $perPage,
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        } elseif ($showStaticOnFirstPage && $currentPage > 1) {
            $offset = $perPage - $staticCount + ($currentPage - 2) * $perPage;
            $dbItems = $query->offset($offset)->limit($perPage)->get();
            $vrExperiments = new \Illuminate\Pagination\LengthAwarePaginator(
                $dbItems,
                $totalExperiments,
                $perPage,
                $currentPage,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        } else {
            $vrExperiments = $query->paginate($perPage)->withQueryString();
        }

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

        return view('client.back.vr-experiments.show', [
            'vrExperiment' => $vrExperiment,
            'subjects' => $subjects,
            'isStatic' => false,
        ]);
    }

    /**
     * Display a static (config-defined) VR experiment. Opens external URL in new tab; no iframe.
     */
    public function showStatic(string $slug): View
    {
        $experiments = $this->getStaticExperiments();
        $experiment = $experiments->firstWhere('slug', $slug);

        if (! $experiment) {
            throw new NotFoundHttpException('Static VR experiment not found.');
        }

        $subjects = CacheService::getActiveSubjects();

        // Use launch_url when set so "Open experience" goes straight into the simulation (no extra "Launch" click on their site)
        $effectiveUrl = $experiment->launch_url ?? $experiment->external_url;

        // Shape like vrExperiment for the shared show view (subject as object with subject_name)
        $vrExperiment = (object) [
            'name' => $experiment->name,
            'description' => $experiment->description,
            'external_url' => $effectiveUrl,
            'launch_url' => $experiment->launch_url ?? null,
            'subject' => (object) ['subject_name' => $experiment->subject_name],
            'subject_id' => null,
            'created_at' => $experiment->created_at,
            'image' => $experiment->image ?? null,
            'use_iframe' => $experiment->use_iframe ?? false,
        ];

        return view('client.back.vr-experiments.show', [
            'vrExperiment' => $vrExperiment,
            'subjects' => $subjects,
            'isStatic' => true,
        ]);
    }
}

