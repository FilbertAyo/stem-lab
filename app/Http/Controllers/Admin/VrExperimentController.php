<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ValidatesUploads;
use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\VrExperiment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VrExperimentController extends Controller
{
    use ValidatesUploads;

    private const THUMBNAIL_MAX_KB = 5120;

    /**
     * @return array<string, mixed>
     */
    private function validationRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'external_url' => ['required', 'url', 'max:2048'],
            'thumbnail' => $this->imageUploadRules(self::THUMBNAIL_MAX_KB),
            'class_ids' => ['required', 'array', 'min:1'],
            'class_ids.*' => ['integer', 'exists:school_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validationMessages(): array
    {
        return $this->imageUploadMessages('thumbnail', self::THUMBNAIL_MAX_KB);
    }

    /**
     * @return array<string, array{label: string, max_kb: int}>
     */
    private function uploadFieldLimits(): array
    {
        return [
            'thumbnail' => [
                'label' => 'Thumbnail',
                'max_kb' => self::THUMBNAIL_MAX_KB,
            ],
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = VrExperiment::with(['schoolClasses', 'subject'])->withSubject();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                  ->orWhere('description', 'like', '%'.$search.'%')
                  ->orWhere('external_url', 'like', '%'.$search.'%')
                  ->orWhereHas('subject', function ($subjectQuery) use ($search) {
                      $subjectQuery->where('subject_name', 'like', '%'.$search.'%');
                  })
                  ->orWhereHas('schoolClasses', function ($classQuery) use ($search) {
                      $classQuery->where('name', 'like', '%'.$search.'%');
                  });
            });
        }

        $vrExperiments = $query->latest()->paginate(15)->withQueryString();

        return view('admin.vr-experiments.index', compact('vrExperiments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $classes = SchoolClass::active()->ordered()->get();
        $subjects = \App\Services\CacheService::getActiveSubjects();
        $uploadLimits = $this->uploadFieldLimits();

        return view('admin.vr-experiments.create', compact('classes', 'subjects', 'uploadLimits'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($response = $this->validateUploadedFiles($request, $this->uploadFieldLimits())) {
            return $response;
        }

        $validated = $request->validate(
            $this->validationRules(),
            $this->validationMessages()
        );

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('vr-experiments/thumbnails', 'public');
        }

        $validated['is_active'] = $request->has('is_active');

        $classIds = $validated['class_ids'];
        unset($validated['class_ids']);

        $vrExperiment = VrExperiment::create($validated);
        $vrExperiment->schoolClasses()->sync($classIds);

        return redirect()->route('admin.vr-experiments.index')
            ->with('success', 'VR Experiment created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VrExperiment $vrExperiment): View
    {
        $vrExperiment->load('schoolClasses');
        $classes = SchoolClass::active()->ordered()->get();
        $subjects = \App\Services\CacheService::getActiveSubjects();
        $uploadLimits = $this->uploadFieldLimits();

        return view('admin.vr-experiments.edit', compact('vrExperiment', 'classes', 'subjects', 'uploadLimits'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VrExperiment $vrExperiment): RedirectResponse
    {
        if ($response = $this->validateUploadedFiles($request, $this->uploadFieldLimits())) {
            return $response;
        }

        $validated = $request->validate(
            $this->validationRules(),
            $this->validationMessages()
        );

        if ($request->hasFile('thumbnail')) {
            if ($vrExperiment->thumbnail && Storage::disk('public')->exists($vrExperiment->thumbnail)) {
                Storage::disk('public')->delete($vrExperiment->thumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')->store('vr-experiments/thumbnails', 'public');
        } else {
            unset($validated['thumbnail']);
        }

        $validated['is_active'] = $request->has('is_active');

        $classIds = $validated['class_ids'];
        unset($validated['class_ids']);

        $vrExperiment->update($validated);
        $vrExperiment->schoolClasses()->sync($classIds);

        return redirect()->route('admin.vr-experiments.index')
            ->with('success', 'VR Experiment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VrExperiment $vrExperiment): RedirectResponse
    {
        if ($vrExperiment->thumbnail && Storage::disk('public')->exists($vrExperiment->thumbnail)) {
            Storage::disk('public')->delete($vrExperiment->thumbnail);
        }

        $vrExperiment->delete();

        return redirect()->route('admin.vr-experiments.index')
            ->with('success', 'VR Experiment deleted successfully.');
    }
}
