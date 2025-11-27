<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\VrExperiment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VrExperimentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $vrExperiments = VrExperiment::with(['schoolClass', 'subject'])->latest()->get();
        return view('admin.vr-experiments.index', compact('vrExperiments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();
        $subjects = Subject::where('is_active', true)->orderBy('subject_name')->get();
        return view('admin.vr-experiments.create', compact('classes', 'subjects'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'external_url' => ['required', 'url', 'max:2048'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'], // Max 5MB
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'is_active' => ['boolean'],
        ]);

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('vr-experiments/thumbnails', 'public');
            $validated['thumbnail'] = $thumbnailPath;
        }

        // Set is_active default
        $validated['is_active'] = $request->has('is_active');

        VrExperiment::create($validated);

        return redirect()->route('admin.vr-experiments.index')
            ->with('success', 'VR Experiment created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VrExperiment $vrExperiment): View
    {
        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();
        $subjects = Subject::where('is_active', true)->orderBy('subject_name')->get();
        return view('admin.vr-experiments.edit', compact('vrExperiment', 'classes', 'subjects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VrExperiment $vrExperiment): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'external_url' => ['required', 'url', 'max:2048'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'], // Max 5MB
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'is_active' => ['boolean'],
        ]);

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail if exists
            if ($vrExperiment->thumbnail && Storage::disk('public')->exists($vrExperiment->thumbnail)) {
                Storage::disk('public')->delete($vrExperiment->thumbnail);
            }
            $thumbnailPath = $request->file('thumbnail')->store('vr-experiments/thumbnails', 'public');
            $validated['thumbnail'] = $thumbnailPath;
        } else {
            // Keep existing thumbnail if not updating
            unset($validated['thumbnail']);
        }

        // Set is_active
        $validated['is_active'] = $request->has('is_active');

        $vrExperiment->update($validated);

        return redirect()->route('admin.vr-experiments.index')
            ->with('success', 'VR Experiment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VrExperiment $vrExperiment): RedirectResponse
    {
        // Delete thumbnail if exists
        if ($vrExperiment->thumbnail && Storage::disk('public')->exists($vrExperiment->thumbnail)) {
            Storage::disk('public')->delete($vrExperiment->thumbnail);
        }

        // Delete the experiment record
        $vrExperiment->delete();

        return redirect()->route('admin.vr-experiments.index')
            ->with('success', 'VR Experiment deleted successfully.');
    }
}

