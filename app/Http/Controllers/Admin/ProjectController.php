<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectTag;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $query = Project::query()
            ->with(['category'])
            ->withCount(['tags', 'equipment']);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('platform', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($catQ) use ($search) {
                        $catQ->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('tags', function ($tagQ) use ($search) {
                        $tagQ->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'published') {
                $query->where('is_published', true);
            } elseif ($status === 'draft') {
                $query->where('is_published', false);
            }
        }

        if ($request->filled('active')) {
            $active = $request->input('active');
            if ($active === 'active') {
                $query->where('is_active', true);
            } elseif ($active === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $projects = $query->latest()->paginate(15)->withQueryString();

        return view('admin.projects.index', compact('projects'));
    }

    public function create(): View
    {
        $categories = ProjectCategory::ordered()->get();
        $tags = ProjectTag::orderBy('name')->get();
        $equipment = Equipment::orderBy('name')->get();

        return view('admin.projects.create', compact('categories', 'tags', 'equipment'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'difficulty' => ['required', 'in:beginner,intermediate,advanced'],
            'project_category_id' => ['nullable', 'exists:project_categories,id'],
            'new_category' => ['nullable', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'guide' => ['nullable', 'string'],
            'embedded_code' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'circuit_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'tags' => ['nullable', 'string', 'max:500'], // comma-separated
            'equipment_name' => ['array'],
            'equipment_name.*' => ['nullable', 'string', 'max:255'],
            'equipment_quantity' => ['array'],
            'equipment_quantity.*' => ['nullable', 'string', 'max:50'],
            'equipment_notes' => ['array'],
            'equipment_notes.*' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        return DB::transaction(function () use ($request, $validated) {
            $categoryId = $validated['project_category_id'] ?? null;

            if (!empty($validated['new_category'])) {
                $newCategory = ProjectCategory::firstOrCreate(
                    ['slug' => $this->uniqueSlug(ProjectCategory::class, Str::slug($validated['new_category']), 'slug')],
                    ['name' => $validated['new_category'], 'is_active' => true, 'sort_order' => 0]
                );
                $categoryId = $newCategory->id;
            }

            $projectData = [
                'project_category_id' => $categoryId,
                'created_by' => Auth::id(),
                'name' => $validated['name'],
                'slug' => $this->uniqueSlug(Project::class, Str::slug($validated['name'])),
                'difficulty' => $validated['difficulty'],
                'platform' => $validated['platform'] ?? null,
                'description' => $validated['description'] ?? null,
                'guide' => $validated['guide'] ?? null,
                'embedded_code' => $validated['embedded_code'] ?? null,
                'is_active' => $request->boolean('is_active', true),
                'is_published' => $request->boolean('is_published', false),
                'published_at' => $request->filled('published_at')
                    ? Carbon::parse($request->input('published_at'))
                    : ($request->boolean('is_published', false) ? now() : null),
            ];

            if ($request->hasFile('cover_image')) {
                $projectData['cover_image'] = $request->file('cover_image')->store('projects/covers', 'public');
            }
            if ($request->hasFile('circuit_image')) {
                $projectData['circuit_image'] = $request->file('circuit_image')->store('projects/circuits', 'public');
            }

            $project = Project::create($projectData);

            // Tags (comma-separated)
            $tagIds = $this->upsertTagsFromString($validated['tags'] ?? '');
            if (count($tagIds) > 0) {
                $project->tags()->sync($tagIds);
            }

            // Equipment rows
            $this->syncEquipmentFromRows(
                $project,
                $validated['equipment_name'] ?? [],
                $validated['equipment_quantity'] ?? [],
                $validated['equipment_notes'] ?? []
            );

            return redirect()
                ->route('admin.projects.edit', $project)
                ->with('success', 'Project created successfully.');
        });
    }

    public function edit(Project $project): View
    {
        $project->load(['tags', 'equipment', 'category']);

        $categories = ProjectCategory::ordered()->get();
        $tags = ProjectTag::orderBy('name')->get();
        $equipment = Equipment::orderBy('name')->get();

        // Flatten for form
        $tagsString = $project->tags->pluck('name')->implode(', ');

        return view('admin.projects.edit', compact('project', 'categories', 'tags', 'equipment', 'tagsString'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'difficulty' => ['required', 'in:beginner,intermediate,advanced'],
            'project_category_id' => ['nullable', 'exists:project_categories,id'],
            'new_category' => ['nullable', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'guide' => ['nullable', 'string'],
            'embedded_code' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'circuit_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'tags' => ['nullable', 'string', 'max:500'],
            'equipment_name' => ['array'],
            'equipment_name.*' => ['nullable', 'string', 'max:255'],
            'equipment_quantity' => ['array'],
            'equipment_quantity.*' => ['nullable', 'string', 'max:50'],
            'equipment_notes' => ['array'],
            'equipment_notes.*' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'remove_cover_image' => ['nullable', 'boolean'],
            'remove_circuit_image' => ['nullable', 'boolean'],
        ]);

        return DB::transaction(function () use ($request, $validated, $project) {
            $categoryId = $validated['project_category_id'] ?? null;

            if (!empty($validated['new_category'])) {
                $newCategory = ProjectCategory::firstOrCreate(
                    ['slug' => $this->uniqueSlug(ProjectCategory::class, Str::slug($validated['new_category']), 'slug')],
                    ['name' => $validated['new_category'], 'is_active' => true, 'sort_order' => 0]
                );
                $categoryId = $newCategory->id;
            }

            $data = [
                'project_category_id' => $categoryId,
                'name' => $validated['name'],
                'difficulty' => $validated['difficulty'],
                'platform' => $validated['platform'] ?? null,
                'description' => $validated['description'] ?? null,
                'guide' => $validated['guide'] ?? null,
                'embedded_code' => $validated['embedded_code'] ?? null,
                'is_active' => $request->boolean('is_active', true),
                'is_published' => $request->boolean('is_published', false),
                'published_at' => $request->filled('published_at')
                    ? Carbon::parse($request->input('published_at'))
                    : ($request->boolean('is_published', false) ? ($project->published_at ?? now()) : null),
            ];

            // Update slug if name changed
            if ($project->name !== $validated['name']) {
                $data['slug'] = $this->uniqueSlug(Project::class, Str::slug($validated['name']), 'slug', $project->id);
            }

            // Remove images if requested
            if ($request->boolean('remove_cover_image') && $project->cover_image) {
                if (Storage::disk('public')->exists($project->cover_image)) {
                    Storage::disk('public')->delete($project->cover_image);
                }
                $data['cover_image'] = null;
            }
            if ($request->boolean('remove_circuit_image') && $project->circuit_image) {
                if (Storage::disk('public')->exists($project->circuit_image)) {
                    Storage::disk('public')->delete($project->circuit_image);
                }
                $data['circuit_image'] = null;
            }

            // Handle uploads (replace)
            if ($request->hasFile('cover_image')) {
                if ($project->cover_image && Storage::disk('public')->exists($project->cover_image)) {
                    Storage::disk('public')->delete($project->cover_image);
                }
                $data['cover_image'] = $request->file('cover_image')->store('projects/covers', 'public');
            }
            if ($request->hasFile('circuit_image')) {
                if ($project->circuit_image && Storage::disk('public')->exists($project->circuit_image)) {
                    Storage::disk('public')->delete($project->circuit_image);
                }
                $data['circuit_image'] = $request->file('circuit_image')->store('projects/circuits', 'public');
            }

            $project->update($data);

            // Tags
            $tagIds = $this->upsertTagsFromString($validated['tags'] ?? '');
            $project->tags()->sync($tagIds);

            // Equipment
            $this->syncEquipmentFromRows(
                $project,
                $validated['equipment_name'] ?? [],
                $validated['equipment_quantity'] ?? [],
                $validated['equipment_notes'] ?? []
            );

            return redirect()
                ->route('admin.projects.edit', $project)
                ->with('success', 'Project updated successfully.');
        });
    }

    public function destroy(Project $project): RedirectResponse
    {
        // Delete stored images
        if ($project->cover_image && Storage::disk('public')->exists($project->cover_image)) {
            Storage::disk('public')->delete($project->cover_image);
        }
        if ($project->circuit_image && Storage::disk('public')->exists($project->circuit_image)) {
            Storage::disk('public')->delete($project->circuit_image);
        }

        $project->delete();

        return redirect()
            ->route('admin.projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    /**
     * Build a unique slug for a model.
     *
     * @param class-string<\Illuminate\Database\Eloquent\Model> $modelClass
     */
    private function uniqueSlug(string $modelClass, string $baseSlug, string $column = 'slug', ?int $ignoreId = null): string
    {
        $slug = $baseSlug ?: Str::random(8);
        $i = 2;

        while (true) {
            $query = $modelClass::query()->where($column, $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
            if (!$query->exists()) {
                return $slug;
            }
            $slug = "{$baseSlug}-{$i}";
            $i++;
        }
    }

    /**
     * Upsert tags from comma-separated string and return IDs.
     *
     * @return array<int, int>
     */
    private function upsertTagsFromString(string $tagsString): array
    {
        $tagsString = trim($tagsString);
        if ($tagsString === '') {
            return [];
        }

        $names = collect(explode(',', $tagsString))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->unique()
            ->values();

        $ids = [];

        foreach ($names as $name) {
            $slugBase = Str::slug($name);
            $slug = $this->uniqueSlug(ProjectTag::class, $slugBase, 'slug');
            $tag = ProjectTag::firstOrCreate(
                ['name' => $name],
                ['slug' => $slug]
            );
            // Ensure slug if pre-existed without it (safety)
            if (!$tag->slug) {
                $tag->slug = $slug;
                $tag->save();
            }
            $ids[] = $tag->id;
        }

        return $ids;
    }

    private function syncEquipmentFromRows(Project $project, array $names, array $quantities, array $notes): void
    {
        $sync = [];

        $rowCount = max(count($names), count($quantities), count($notes));
        for ($i = 0; $i < $rowCount; $i++) {
            $name = trim((string) ($names[$i] ?? ''));
            if ($name === '') {
                continue;
            }

            $equipment = Equipment::firstOrCreate(['name' => $name]);

            $sync[$equipment->id] = [
                'quantity' => isset($quantities[$i]) ? trim((string) $quantities[$i]) : null,
                'notes' => isset($notes[$i]) ? trim((string) $notes[$i]) : null,
            ];
        }

        $project->equipment()->sync($sync);
    }
}

