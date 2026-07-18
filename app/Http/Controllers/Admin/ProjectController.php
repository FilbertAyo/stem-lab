<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\Platform;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectTag;
use App\Models\SchoolClass;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $projects = $this->projectsIndexQuery($request)
            ->with(['category', 'createdBy', 'updatedBy', 'author'])
            ->withCount(['tags', 'equipment'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.projects.index', compact('projects'));
    }

    /**
     * Download projects list as PDF (current filters).
     */
    public function export(Request $request): Response
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:published,draft'],
            'active' => ['nullable', 'in:active,inactive'],
        ]);

        $projects = $this->projectsIndexQuery($request)
            ->with('category')
            ->withCount(['tags', 'equipment'])
            ->latest()
            ->get();

        $filterSummary = $this->buildProjectsExportFilterSummary($request);
        $filename = 'projects_'.($filterSummary ? 'filtered_' : 'all_').now()->format('Y-m-d').'.pdf';

        $pdf = Pdf::loadView('admin.downloads.projects-list', [
            'projects' => $projects,
            'title' => 'Projects',
            'generatedAt' => now(),
            'filterSummary' => $filterSummary,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    private function projectsIndexQuery(Request $request): Builder
    {
        $query = Project::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($catQ) use ($search) {
                        $catQ->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('tags', function ($tagQ) use ($search) {
                        $tagQ->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('platforms', function ($platQ) use ($search) {
                        $platQ->where('name', 'like', "%{$search}%");
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

        return $query;
    }

    private function buildProjectsExportFilterSummary(Request $request): ?string
    {
        $parts = [];

        if ($request->filled('search')) {
            $parts[] = 'Search: '.$request->string('search');
        }

        if ($request->filled('status')) {
            $parts[] = 'Publish: '.ucfirst((string) $request->status);
        }

        if ($request->filled('active')) {
            $parts[] = 'Active: '.ucfirst((string) $request->active);
        }

        return $parts === [] ? null : implode(' · ', $parts);
    }

    public function show(Project $project): View
    {
        $project->load([
            'category', 'tags', 'equipment', 'author', 'createdBy', 'updatedBy',
            'resources', 'codes', 'platforms',
        ]);

        return view('admin.projects.show', compact('project'));
    }

    public function create(): View
    {
        $categories = ProjectCategory::ordered()->get();
        $tags = ProjectTag::orderBy('name')->get();
        $equipment = Equipment::orderBy('name')->get();
        $platforms = Platform::active()->ordered()->get();
        $classes = SchoolClass::active()->ordered()->get();

        return view('admin.projects.create', compact('categories', 'tags', 'equipment', 'platforms', 'classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'difficulty' => ['required', 'in:beginner,intermediate,advanced'],
            'project_category_id' => ['nullable', 'exists:project_categories,id'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'estimated_time' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'youtube_url' => ['nullable', 'string', 'url', 'max:500'],
            'external_link' => ['nullable', 'string', 'url', 'max:500'],
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
            'platform_ids' => ['nullable', 'array'],
            'platform_ids.*' => ['integer', 'exists:platforms,id'],
            'resource_title' => ['nullable', 'array'],
            'resource_title.*' => ['nullable', 'string', 'max:255'],
            'resource_type' => ['nullable', 'array'],
            'resource_type.*' => ['nullable', 'in:pdf,image,link,file'],
            'resource_url' => ['nullable', 'array'],
            'resource_url.*' => ['nullable', 'string', 'max:1000'],
            'resource_file' => ['nullable', 'array'],
            'resource_file.*' => ['nullable', 'file', 'max:10240'],
            'code_title' => ['nullable', 'array'],
            'code_title.*' => ['nullable', 'string', 'max:255'],
            'code_language' => ['nullable', 'array'],
            'code_language.*' => ['nullable', 'string', 'max:100'],
            'code_body' => ['nullable', 'array'],
            'code_body.*' => ['nullable', 'string'],
            'code_description' => ['nullable', 'array'],
            'code_description.*' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        return DB::transaction(function () use ($request, $validated) {
            $projectData = [
                'project_category_id' => $validated['project_category_id'] ?? null,
                'school_class_id' => $validated['school_class_id'],
                'created_by' => Auth::id(),
                'name' => $validated['name'],
                'slug' => $this->uniqueSlug(Project::class, Str::slug($validated['name'])),
                'difficulty' => $validated['difficulty'],
                'estimated_time' => isset($validated['estimated_time']) ? (int) $validated['estimated_time'] : null,
                'youtube_url' => $validated['youtube_url'] ?? null,
                'external_link' => $validated['external_link'] ?? null,
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

            $tagIds = $this->upsertTagsFromString($validated['tags'] ?? '');
            if (count($tagIds) > 0) {
                $project->tags()->sync($tagIds);
            }

            $project->platforms()->sync($validated['platform_ids'] ?? []);

            $this->syncResources($project, $validated, $request);
            $this->syncCodes($project, $validated);

            $this->syncEquipmentFromRows(
                $project,
                $validated['equipment_name'] ?? [],
                $validated['equipment_quantity'] ?? [],
                $validated['equipment_notes'] ?? []
            );

            $this->logProjectActivity('created', $project, [
                'status' => [
                    'is_active' => $project->is_active,
                    'is_published' => $project->is_published,
                ],
            ]);

            return redirect()
                ->route('admin.projects.edit', $project)
                ->with('success', 'Project created successfully.');
        });
    }

    public function edit(Project $project): View
    {
        $project->load(['tags', 'equipment', 'category', 'resources', 'codes', 'platforms', 'createdBy', 'updatedBy', 'author']);

        $categories = ProjectCategory::ordered()->get();
        $tags = ProjectTag::orderBy('name')->get();
        $equipment = Equipment::orderBy('name')->get();
        $platforms = Platform::active()->ordered()->get();
        $classes = SchoolClass::active()->ordered()->get();
        $tagsString = $project->tags->pluck('name')->implode(', ');
        $platformIds = $project->platforms->pluck('id')->toArray();

        return view('admin.projects.edit', compact('project', 'categories', 'tags', 'equipment', 'platforms', 'classes', 'tagsString', 'platformIds'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $originalData = Arr::only($project->getOriginal(), [
            'name',
            'slug',
            'difficulty',
            'project_category_id',
            'estimated_time',
            'youtube_url',
            'external_link',
            'is_active',
            'is_published',
            'published_at',
            'cover_image',
            'circuit_image',
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'difficulty' => ['required', 'in:beginner,intermediate,advanced'],
            'project_category_id' => ['nullable', 'exists:project_categories,id'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'estimated_time' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'youtube_url' => ['nullable', 'string', 'url', 'max:500'],
            'external_link' => ['nullable', 'string', 'url', 'max:500'],
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
            'platform_ids' => ['nullable', 'array'],
            'platform_ids.*' => ['integer', 'exists:platforms,id'],
            'resource_title' => ['nullable', 'array'],
            'resource_title.*' => ['nullable', 'string', 'max:255'],
            'resource_type' => ['nullable', 'array'],
            'resource_type.*' => ['nullable', 'in:pdf,image,link,file'],
            'resource_url' => ['nullable', 'array'],
            'resource_url.*' => ['nullable', 'string', 'max:1000'],
            'resource_file' => ['nullable', 'array'],
            'resource_file.*' => ['nullable', 'file', 'max:10240'],
            'code_title' => ['nullable', 'array'],
            'code_title.*' => ['nullable', 'string', 'max:255'],
            'code_language' => ['nullable', 'array'],
            'code_language.*' => ['nullable', 'string', 'max:100'],
            'code_body' => ['nullable', 'array'],
            'code_body.*' => ['nullable', 'string'],
            'code_description' => ['nullable', 'array'],
            'code_description.*' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'remove_cover_image' => ['nullable', 'boolean'],
            'remove_circuit_image' => ['nullable', 'boolean'],
        ]);

        return DB::transaction(function () use ($request, $validated, $project, $originalData) {
            $data = [
                'project_category_id' => $validated['project_category_id'] ?? null,
                'school_class_id' => $validated['school_class_id'],
                'name' => $validated['name'],
                'difficulty' => $validated['difficulty'],
                'estimated_time' => isset($validated['estimated_time']) ? (int) $validated['estimated_time'] : null,
                'youtube_url' => $validated['youtube_url'] ?? null,
                'external_link' => $validated['external_link'] ?? null,
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

            $tagIds = $this->upsertTagsFromString($validated['tags'] ?? '');
            $project->tags()->sync($tagIds);
            $project->platforms()->sync($validated['platform_ids'] ?? []);

            $this->syncResources($project, $validated, $request);
            $this->syncCodes($project, $validated);

            $this->syncEquipmentFromRows(
                $project,
                $validated['equipment_name'] ?? [],
                $validated['equipment_quantity'] ?? [],
                $validated['equipment_notes'] ?? []
            );

            $updatedData = Arr::only($project->fresh()->toArray(), array_keys($originalData));
            $changedFields = collect(array_keys($originalData))
                ->filter(fn ($field) => ($originalData[$field] ?? null) != ($updatedData[$field] ?? null))
                ->values()
                ->all();

            $this->logProjectActivity('updated', $project, [
                'changed_fields' => $changedFields,
                'old_values' => Arr::only($originalData, $changedFields),
                'new_values' => Arr::only($updatedData, $changedFields),
            ]);

            return redirect()
                ->route('admin.projects.edit', $project)
                ->with('success', 'Project updated successfully.');
        });
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->load(['resources']);
        $projectName = $project->name;
        $projectSlug = $project->slug;
        $projectId = $project->id;

        if ($project->cover_image && Storage::disk('public')->exists($project->cover_image)) {
            Storage::disk('public')->delete($project->cover_image);
        }
        if ($project->circuit_image && Storage::disk('public')->exists($project->circuit_image)) {
            Storage::disk('public')->delete($project->circuit_image);
        }
        foreach ($project->resources as $resource) {
            if ($resource->file_path && Storage::disk('public')->exists($resource->file_path)) {
                Storage::disk('public')->delete($resource->file_path);
            }
        }

        $project->delete();
        activity('admin_projects')
            ->causedBy(Auth::user())
            ->withProperties([
                'project_id' => $projectId,
                'project_name' => $projectName,
                'project_slug' => $projectSlug,
                'action' => 'deleted',
            ])
            ->log('Admin deleted project');

        return redirect()
            ->route('admin.projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    /**
     * Build a unique slug for a model.
     *
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelClass
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
            if (! $query->exists()) {
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
            if (! $tag->slug) {
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

    private function syncResources(Project $project, array $validated, Request $request): void
    {
        $project->resources()->delete();

        $titles = $validated['resource_title'] ?? [];
        if (count($titles) === 0) {
            return;
        }

        $types = $validated['resource_type'] ?? [];
        $urls = $validated['resource_url'] ?? [];
        $files = $request->file('resource_file', []);
        $order = 0;

        foreach ($titles as $i => $title) {
            $title = trim((string) $title);
            if ($title === '') {
                continue;
            }
            $order++;

            $type = $types[$i] ?? 'link';
            $url = trim((string) ($urls[$i] ?? '')) ?: null;
            $filePath = null;
            if (isset($files[$i]) && $files[$i]->isValid()) {
                $filePath = $files[$i]->store('projects/resources', 'public');
            }

            $project->resources()->create([
                'title' => $title,
                'type' => $type,
                'url' => $url,
                'file_path' => $filePath,
                'sort_order' => $order,
            ]);
        }
    }

    private function syncCodes(Project $project, array $validated): void
    {
        $project->codes()->delete();

        $titles = $validated['code_title'] ?? [];
        if (count($titles) === 0) {
            return;
        }

        $languages = $validated['code_language'] ?? [];
        $bodies = $validated['code_body'] ?? [];
        $descriptions = $validated['code_description'] ?? [];
        $order = 0;

        foreach ($titles as $i => $title) {
            $title = trim((string) $title);
            $body = trim((string) ($bodies[$i] ?? ''));
            if ($title === '' && $body === '') {
                continue;
            }
            $order++;

            $project->codes()->create([
                'title' => $title ?: 'Code snippet',
                'language' => trim((string) ($languages[$i] ?? '')) ?: 'Plain',
                'code' => $body ?: ' ',
                'description' => trim((string) ($descriptions[$i] ?? '')) ?: null,
                'sort_order' => $order,
            ]);
        }
    }

    private function logProjectActivity(string $action, Project $project, array $properties = []): void
    {
        activity('admin_projects')
            ->performedOn($project)
            ->causedBy(Auth::user())
            ->withProperties(array_merge([
                'project_id' => $project->id,
                'project_name' => $project->name,
                'project_slug' => $project->slug,
                'action' => $action,
            ], $properties))
            ->log("Admin {$action} project");
    }
}
