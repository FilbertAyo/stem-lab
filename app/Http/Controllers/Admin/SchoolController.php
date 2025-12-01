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
    public function index(Request $request): View
    {
        $query = School::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('address', 'like', '%' . $search . '%')
                  ->orWhere('district', 'like', '%' . $search . '%')
                  ->orWhere('region', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter by region
        if ($request->filled('region')) {
            $query->where('region', $request->region);
        }

        // Filter by district
        if ($request->filled('district')) {
            $query->where('district', $request->district);
        }

        $schools = $query->ordered()->paginate(15)->withQueryString();

        // Get unique regions and districts for filter dropdowns
        $regions = School::whereNotNull('region')->distinct()->orderBy('region')->pluck('region');
        $districts = School::whereNotNull('district')->distinct()->orderBy('district')->pluck('district');

        return view('admin.schools.index', compact('schools', 'regions', 'districts'));
    }

    public function show(School $school): View
    {
        // Load teachers with their clubs and students
        $school->load([
            'teacherClubs.user',
            'teacherClubs.club',
            'teacherClubs.students.schoolClass',
        ]);

        // Get unique teachers for this school (using database query is more efficient)
        $teacherIds = $school->teacherClubs()->pluck('user_id')->unique()->toArray();
        $teachers = !empty($teacherIds) 
            ? \App\Models\User::whereIn('id', $teacherIds)->get()->unique('id')->values()
            : collect();

        // Get all students for this school (using database query)
        $students = \App\Models\Student::whereHas('teacherClub', function ($query) use ($school) {
            $query->where('school_id', $school->id);
        })->with('schoolClass')->get()->unique('id')->values();

        return view('admin.schools.show', compact('school', 'teachers', 'students'));
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
