<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\School;
use App\Models\Student;
use App\Models\TeacherClub;
use App\Services\StudentCsvBulkImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SchoolController extends Controller
{
    public function index(Request $request): View
    {
        $query = School::query()->with('regionModel');
        $allowedPerPage = [10, 15, 25, 50, 100];
        $perPage = (int) $request->query('per_page', 15);

        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 15;
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('address', 'like', '%' . $search . '%')
                  ->orWhere('district', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%')
                  ->orWhereHas('regionModel', function ($regionQuery) use ($search) {
                      $regionQuery->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter by region
        if ($request->filled('region_id')) {
            $query->where('region_id', $request->integer('region_id'));
        }

        // Filter by district
        if ($request->filled('district')) {
            $query->where('district', $request->district);
        }

        $schools = $query->ordered()->paginate($perPage)->withQueryString();

        // Get regions and districts for filter dropdowns
        $regions = Region::query()->orderBy('name')->get(['id', 'name']);
        $districts = School::whereNotNull('district')->distinct()->orderBy('district')->pluck('district');

        return view('admin.schools.index', compact('schools', 'regions', 'districts'));
    }

    public function show(School $school): View
    {
        $school->load('regionModel');

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

        $bulkUploadTeacherClubs = $school->teacherClubs()
            ->approved()
            ->with(['user:id,name', 'club:id,name'])
            ->orderBy('club_id')
            ->get(['id', 'user_id', 'club_id', 'school_id']);

        return view('admin.schools.show', compact('school', 'teachers', 'students', 'bulkUploadTeacherClubs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:schools,name'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'ward' => ['nullable', 'string', 'max:255'],
            'region_id' => ['nullable', 'exists:regions,id'],
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
            'ward' => ['nullable', 'string', 'max:255'],
            'region_id' => ['nullable', 'exists:regions,id'],
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

    public function destroy(School $school): RedirectResponse
    {
        if ($school->teacherClubs()->exists()) {
            return redirect()
                ->route('admin.schools.index')
                ->with('error', 'Cannot delete school with linked teacher clubs. Remove linked teacher-club records first.');
        }

        $schoolName = $school->name;
        $school->delete();

        return redirect()
            ->route('admin.schools.index')
            ->with('status', "School {$schoolName} deleted successfully.");
    }

    public function removeTeacherFromClub(TeacherClub $teacherClub): RedirectResponse
    {
        $school = $teacherClub->school;
        $teacherName = $teacherClub->user->name;
        $clubName = $teacherClub->club->name;

        // Check if the teacher has any students in this club
        $studentsCount = $teacherClub->students()->count();

        if ($studentsCount > 0) {
            return redirect()
                ->route('admin.schools.show', $school)
                ->with('error', "Cannot remove {$teacherName} from {$clubName} because they have {$studentsCount} registered student(s) in this club.");
        }

        // Delete the teacher-club relationship
        $teacherClub->delete();

        return redirect()
            ->route('admin.schools.show', $school)
            ->with('status', "{$teacherName} has been removed from {$clubName} successfully.");
    }

    public function updateClubDay(Request $request, School $school, TeacherClub $teacherClub): RedirectResponse
    {
        $belongsToSchool = (int) $teacherClub->school_id === (int) $school->id;

        if (! $belongsToSchool) {
            abort(404);
        }

        $validated = $request->validate([
            'club_day' => ['nullable', Rule::in(['monday', 'tuesday', 'wednesday', 'thursday', 'friday'])],
        ]);

        $teacherClub->update([
            'club_day' => $validated['club_day'] ?? null,
        ]);

        $teacherName = $teacherClub->user?->name ?? 'Teacher';
        $clubName = $teacherClub->club?->name ?? 'club';

        return redirect()
            ->route('admin.schools.show', $school)
            ->with('status', "Dedicated club day updated for {$teacherName} ({$clubName}).");
    }

    public function removeStudent(School $school, Student $student): RedirectResponse
    {
        $belongsToSchool = (int) $student->teacherClub?->school_id === (int) $school->id;

        if (! $belongsToSchool) {
            abort(404);
        }

        $studentName = $student->name;
        $student->delete();

        return redirect()
            ->route('admin.schools.show', $school)
            ->with('status', "{$studentName} removed successfully.");
    }

    public function bulkImportStudents(
        Request $request,
        School $school,
        StudentCsvBulkImportService $csvImport,
    ): RedirectResponse {
        $validated = $request->validate([
            'teacher_club_id' => ['required', 'integer'],
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $teacherClub = $school->teacherClubs()
            ->approved()
            ->where('id', (int) $validated['teacher_club_id'])
            ->first();

        if (! $teacherClub) {
            return redirect()
                ->route('admin.schools.show', $school)
                ->withErrors(['teacher_club_id' => 'Select a valid approved teacher/club for this school.'])
                ->withInput();
        }

        $result = $csvImport->import($teacherClub, $request->file('csv_file'));

        if ($result['errors'] !== []) {
            return redirect()
                ->route('admin.schools.show', $school)
                ->with('error', $csvImport->formatErrorsForFlash($result['errors']))
                ->withInput();
        }

        return redirect()
            ->route('admin.schools.show', $school)
            ->with('status', "Successfully imported {$result['success_count']} student(s).");
    }
}
