<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ValidatesUploads;
use App\Http\Controllers\Controller;
use App\Models\Model3d;
use App\Models\SchoolClass;
use App\Services\CacheService;
use App\Support\Model3dEmbed;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class Model3dController extends Controller
{
    use ValidatesUploads;

    private const GLB_MAX_KB = 102400;

    private const THUMBNAIL_MAX_KB = 5120;

    /**
     * @return array<string, array{label: string, max_kb: int, extensions?: list<string>}>
     */
    private function uploadFieldLimits(): array
    {
        return [
            'file' => [
                'label' => 'GLB file',
                'max_kb' => self::GLB_MAX_KB,
                'extensions' => ['glb'],
            ],
            'thumbnail' => [
                'label' => 'Thumbnail',
                'max_kb' => self::THUMBNAIL_MAX_KB,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validationRules(): array
    {
        return [
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_ids' => ['required', 'array', 'min:1'],
            'class_ids.*' => ['integer', 'exists:school_classes,id'],
            'name' => ['required', 'string', 'max:255'],
            'file' => $this->glbUploadRules(self::GLB_MAX_KB),
            'embed_url' => ['nullable', 'string', 'max:5000'],
            'thumbnail' => $this->imageUploadRules(self::THUMBNAIL_MAX_KB),
            'description' => ['nullable', 'string'],
            'tags' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validationMessages(): array
    {
        return array_merge(
            $this->glbUploadMessages(self::GLB_MAX_KB),
            $this->imageUploadMessages('thumbnail', self::THUMBNAIL_MAX_KB),
            [
                'embed_url.max' => 'The embed URL is too long. Paste only the embed link or iframe snippet.',
            ]
        );
    }

    private function validateModelSource(Request $request, ?Model3d $model3d = null): ?RedirectResponse
    {
        $embedInput = $request->input('embed_url');
        $normalizedEmbed = Model3dEmbed::normalizeInput(is_string($embedInput) ? $embedInput : null);
        $hasNewFile = $request->hasFile('file');
        $hasExistingFile = $model3d?->hasGlb() ?? false;
        $willHaveFile = $hasNewFile || $hasExistingFile;
        $willHaveEmbed = filled($normalizedEmbed);

        if ($request->filled('embed_url') && $normalizedEmbed === null) {
            return back()
                ->withErrors([
                    'embed_url' => 'Enter a valid embed URL or paste the iframe snippet from Sketchfab.',
                ])
                ->withInput();
        }

        if (! $willHaveFile && ! $willHaveEmbed) {
            return back()
                ->withErrors([
                    'file' => 'Provide a GLB file and/or an embed URL.',
                    'embed_url' => 'Provide a GLB file and/or an embed URL.',
                ])
                ->withInput();
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function prepareValidatedPayload(Request $request, array $validated, ?Model3d $model3d = null): array
    {
        if ($request->filled('embed_url')) {
            $validated['embed_url'] = Model3dEmbed::normalizeInput($request->input('embed_url'));
        } else {
            $validated['embed_url'] = null;
        }

        return $validated;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $model3ds = $this->modelsIndexQuery($request)
            ->with(['schoolClasses', 'createdBy', 'updatedBy'])
            ->withCount('questions')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.models.3d.index', compact('model3ds'));
    }

    /**
     * Download 3D models list as PDF (current filters).
     */
    public function export(Request $request): Response
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:all,active,inactive'],
        ]);

        $models = $this->modelsIndexQuery($request)
            ->with(['schoolClasses', 'subject'])
            ->withCount('questions')
            ->latest()
            ->get();

        $filterSummary = $this->buildModelsExportFilterSummary($request);
        $filename = 'models_3d_'.($filterSummary ? 'filtered_' : 'all_').now()->format('Y-m-d').'.pdf';

        $pdf = Pdf::loadView('admin.downloads.models-3d-list', [
            'models' => $models,
            'title' => '3D Models',
            'generatedAt' => now(),
            'filterSummary' => $filterSummary,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    private function modelsIndexQuery(Request $request): Builder
    {
        $query = Model3d::withSubject();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhere('tags', 'like', '%'.$search.'%')
                    ->orWhereHas('subject', function ($subjectQuery) use ($search) {
                        $subjectQuery->where('subject_name', 'like', '%'.$search.'%');
                    });
            });
        }

        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        return $query;
    }

    private function buildModelsExportFilterSummary(Request $request): ?string
    {
        $parts = [];

        if ($request->filled('search')) {
            $parts[] = 'Search: '.$request->string('search');
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $parts[] = 'Status: '.ucfirst((string) $request->status);
        }

        return $parts === [] ? null : implode(' · ', $parts);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $subjects = CacheService::getActiveSubjects();
        $classes = SchoolClass::active()->ordered()->get();
        $uploadLimits = $this->uploadFieldLimits();

        return view('admin.models.3d.create', compact('subjects', 'classes', 'uploadLimits'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($response = $this->validateUploadedFiles($request, $this->uploadFieldLimits())) {
            return $response;
        }

        if ($response = $this->validateModelSource($request)) {
            return $response;
        }

        $validated = $request->validate(
            $this->validationRules(),
            $this->validationMessages()
        );

        $validated = $this->prepareValidatedPayload($request, $validated);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time().'_'.str_replace(' ', '_', $file->getClientOriginalName());
            if (! str_ends_with(strtolower($filename), '.glb')) {
                $filename .= '.glb';
            }
            $validated['file'] = $file->storeAs('models/3d', $filename, 'public');
        }

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('models/3d/thumbnails', 'public');
        }

        $validated['is_active'] = $request->has('is_active');

        $classIds = $validated['class_ids'];
        unset($validated['class_ids']);

        $model3d = Model3d::create($validated);
        $model3d->schoolClasses()->sync($classIds);

        CacheService::clearModelCaches();

        return redirect()->route('admin.models.3d.index')
            ->with('success', '3D model created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Model3d $model3d): View
    {
        $model3d->load(['subject', 'schoolClasses', 'createdBy', 'updatedBy']);

        return view('admin.models.3d.show', compact('model3d'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Model3d $model3d): View
    {
        $subjects = CacheService::getActiveSubjects();
        $classes = SchoolClass::active()->ordered()->get();
        $uploadLimits = $this->uploadFieldLimits();

        $model3d->load(['schoolClasses', 'createdBy', 'updatedBy']);

        return view('admin.models.3d.edit', compact('model3d', 'subjects', 'classes', 'uploadLimits'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Model3d $model3d): RedirectResponse
    {
        if ($response = $this->validateUploadedFiles($request, $this->uploadFieldLimits())) {
            return $response;
        }

        if ($response = $this->validateModelSource($request, $model3d)) {
            return $response;
        }

        $validated = $request->validate(
            $this->validationRules(),
            $this->validationMessages()
        );

        $validated = $this->prepareValidatedPayload($request, $validated, $model3d);

        if ($request->hasFile('file')) {
            if ($model3d->file && Storage::disk('public')->exists($model3d->file)) {
                Storage::disk('public')->delete($model3d->file);
            }
            $file = $request->file('file');
            $filename = time().'_'.str_replace(' ', '_', $file->getClientOriginalName());
            if (! str_ends_with(strtolower($filename), '.glb')) {
                $filename .= '.glb';
            }
            $validated['file'] = $file->storeAs('models/3d', $filename, 'public');
        } else {
            unset($validated['file']);
        }

        if ($request->hasFile('thumbnail')) {
            if ($model3d->thumbnail && Storage::disk('public')->exists($model3d->thumbnail)) {
                Storage::disk('public')->delete($model3d->thumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')->store('models/3d/thumbnails', 'public');
        } else {
            unset($validated['thumbnail']);
        }

        $validated['is_active'] = $request->has('is_active');

        $classIds = $validated['class_ids'];
        unset($validated['class_ids']);

        $model3d->update($validated);
        $model3d->schoolClasses()->sync($classIds);

        CacheService::clearModelCaches();

        return redirect()->route('admin.models.3d.index')
            ->with('success', '3D model updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Model3d $model3d): RedirectResponse
    {
        if ($model3d->file && Storage::disk('public')->exists($model3d->file)) {
            Storage::disk('public')->delete($model3d->file);
        }

        if ($model3d->thumbnail && Storage::disk('public')->exists($model3d->thumbnail)) {
            Storage::disk('public')->delete($model3d->thumbnail);
        }

        $model3d->delete();

        CacheService::clearModelCaches();

        return redirect()->route('admin.models.3d.index')
            ->with('success', '3D model and associated files deleted successfully.');
    }
}
