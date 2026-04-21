<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProjectCategoryController extends Controller
{
    public function index(): View
    {
        $categories = ProjectCategory::query()
            ->withCount('projects')
            ->ordered()
            ->get();

        return view('admin.projects.categories', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:project_categories,name'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        ProjectCategory::create([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug(Str::slug($validated['name'])),
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.project-categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function update(Request $request, ProjectCategory $projectCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:project_categories,name,'.$projectCategory->id],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', false),
        ];

        if ($projectCategory->name !== $validated['name']) {
            $data['slug'] = $this->uniqueSlug(Str::slug($validated['name']), $projectCategory->id);
        }

        $projectCategory->update($data);

        return redirect()
            ->route('admin.project-categories.index')
            ->with('success', "Category \"{$projectCategory->name}\" updated.");
    }

    public function destroy(ProjectCategory $projectCategory): RedirectResponse
    {
        $projectCategory->delete();

        return redirect()
            ->route('admin.project-categories.index')
            ->with('success', "Category \"{$projectCategory->name}\" deleted. Projects using it are now uncategorized.");
    }

    private function uniqueSlug(string $baseSlug, ?int $ignoreId = null): string
    {
        $slug = $baseSlug !== '' ? $baseSlug : Str::random(8);
        $candidate = $slug;
        $counter = 2;

        while (ProjectCategory::query()
            ->where('slug', $candidate)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $candidate = $slug.'-'.$counter;
            $counter++;
        }

        return $candidate;
    }
}
