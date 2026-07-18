<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClassController extends Controller
{
    public function index(): View
    {
        $classes = SchoolClass::orderBy('name')->get();

        return view('admin.classes.index', compact('classes'));
    }

    public function export(): Response
    {
        $classes = SchoolClass::orderBy('name')->get();

        $pdf = Pdf::loadView('admin.downloads.classes-list', [
            'classes' => $classes,
            'title' => 'Classes',
            'generatedAt' => now(),
            'filterSummary' => null,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('classes_'.now()->format('Y-m-d').'.pdf');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:school_classes,name'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        SchoolClass::create($validated + ['is_active' => true]);

        return redirect()->route('admin.classes.index')->with('status', 'Class created successfully.');
    }

    public function update(Request $request, SchoolClass $schoolClass): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('school_classes', 'name')->ignore($schoolClass->id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
        ]);

        $schoolClass->update($validated);

        return redirect()->route('admin.classes.index')->with('status', 'Class updated successfully.');
    }

    public function toggleStatus(SchoolClass $schoolClass): RedirectResponse
    {
        $schoolClass->update([
            'is_active' => ! $schoolClass->is_active,
        ]);

        $message = $schoolClass->is_active ? 'Class enabled successfully.' : 'Class disabled successfully.';

        return redirect()->route('admin.classes.index')->with('status', $message);
    }
}
