<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Client\Concerns\ResolvesStudentClassContext;
use App\Http\Controllers\Controller;
use App\Enums\LearningEventType;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Services\Learning\StudentLearningEventRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProjectController extends Controller
{
    use ResolvesStudentClassContext;

    public function __construct(
        private readonly StudentLearningEventRecorder $eventRecorder,
    ) {}

    /**
     * Show list of published projects with category sidebar.
     */
    public function index(Request $request): View
    {
        $categorySlug = $request->query('category');
        $schoolClassId = $this->studentSchoolClassId();

        $categories = ProjectCategory::active()
            ->ordered()
            ->withCount(['projects' => function ($q) use ($schoolClassId) {
                $q->published()->active()->visibleToStudentClass($schoolClassId);
            }])
            ->get();

        $projectsQuery = Project::published()
            ->active()
            ->visibleToStudentClass($schoolClassId)
            ->with(['category', 'platforms'])
            ->withCount(['steps', 'codes', 'resources'])
            ->ordered();

        $activeCategory = null;

        if ($categorySlug) {
            $activeCategory = $categories->firstWhere('slug', $categorySlug)
                ?? ProjectCategory::where('slug', $categorySlug)->first();

            if ($activeCategory) {
                $projectsQuery->where('project_category_id', $activeCategory->id);
            }
        }

        $projects = $projectsQuery->paginate(12)->withQueryString();

        $gridData = [
            'loading' => false,
            'projects' => $projects,
            'activeCategory' => $activeCategory,
        ];

        if ($request->header('X-Card-Grid')) {
            return view('client.back.projects._cards-grid', $gridData);
        }

        return view('client.back.projects.index', [
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'projects' => $projects,
            'loading' => false,
        ]);
    }

    /**
     * Show a single project with full details.
     */
    public function show(Project $project): View
    {
        // Only published + active are resolved in model binding for client, but keep this as a safety-net
        if (!$project->is_active || !$project->is_published) {
            abort(404);
        }

        if (! $project->isAccessibleToStudentClass($this->studentSchoolClassId())) {
            abort(404);
        }

        $project->load(['category', 'tags', 'equipment', 'steps', 'resources', 'codes', 'platforms']);

        // Categories for sidebar; highlight the current project's category
        $schoolClassId = $this->studentSchoolClassId();
        $categories = ProjectCategory::active()
            ->ordered()
            ->withCount(['projects' => function ($q) use ($schoolClassId) {
                $q->published()->active()->visibleToStudentClass($schoolClassId);
            }])
            ->get();

        $activeCategory = $project->category;

        $user = Auth::user();
        if ($user) {
            $this->eventRecorder->record(
                $user,
                LearningEventType::ProjectViewed,
                $project,
                ['label' => $project->title],
            );
        }

        return view('client.back.projects.show', [
            'project' => $project,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
        ]);
    }
}

