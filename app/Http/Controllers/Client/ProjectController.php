<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    /**
     * Show list of published projects with category sidebar.
     */
    public function index(Request $request): View
    {
        $categorySlug = $request->query('category');

        $categories = ProjectCategory::active()
            ->ordered()
            ->withCount(['projects' => function ($q) {
                $q->published()->active();
            }])
            ->get();

        $projectsQuery = Project::published()
            ->active()
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

        return view('client.back.projects.index', [
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'projects' => $projects,
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

        $project->load(['category', 'tags', 'equipment', 'steps', 'resources', 'codes', 'platforms']);

        // Categories for sidebar; highlight the current project's category
        $categories = ProjectCategory::active()
            ->ordered()
            ->withCount(['projects' => function ($q) {
                $q->published()->active();
            }])
            ->get();

        $activeCategory = $project->category;

        return view('client.back.projects.show', [
            'project' => $project,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
        ]);
    }
}

