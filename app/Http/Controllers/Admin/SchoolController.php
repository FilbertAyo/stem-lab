<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SchoolController extends Controller
{
    public function index(): View
    {
        $schools = School::orderBy('name')->get();

        return view('admin.schools.index', compact('schools'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:schools,name'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        School::create($validated);

        return redirect()->route('admin.schools.index')->with('status', 'School created successfully.');
    }

    public function update(Request $request, School $school): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('schools', 'name')->ignore($school->id),
            ],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);

        $school->update($validated);

        return redirect()->route('admin.schools.index')->with('status', 'School updated successfully.');
    }

    public function toggleStatus(School $school): RedirectResponse
    {
        $school->update([
            'is_active' => ! $school->is_active,
        ]);

        $message = $school->is_active ? 'School enabled successfully.' : 'School disabled successfully.';

        return redirect()->route('admin.schools.index')->with('status', $message);
    }
}
