<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\TeacherClub;
use App\Services\StudentCsvBulkImportService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentController extends Controller
{
    /**
     * Display members panel (students and teachers).
     */
    public function index(): View
    {
        $user = Auth::user();

        // Get teacher's approved club memberships
        $teacherClubs = TeacherClub::where('user_id', $user->id)
            ->approved()
            ->with(['club', 'school'])
            ->get();

        // Students registered by any approved teacher at the same club + school
        $coMemberTeacherClubIds = $user->approvedCoMemberTeacherClubIds();
        $students = Student::query()
            ->whereIn('teacher_club_id', $coMemberTeacherClubIds)
            ->with(['schoolClass', 'teacherClub.club', 'teacherClub.school'])
            ->orderBy('name')
            ->get();

        // Get all active classes for dropdown
        $classes = SchoolClass::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get all clubs the user is a member of (for Teachers tab)
        // Group by club and school to show all members
        $clubsWithTeachers = collect();

        foreach ($teacherClubs as $teacherClub) {
            $clubId = $teacherClub->club_id;
            $schoolId = $teacherClub->school_id;

            // Check if we already added this club-school combination
            $exists = $clubsWithTeachers->first(function ($item) use ($clubId, $schoolId) {
                return $item['club']->id === $clubId && $item['school']->id === $schoolId;
            });

            if (! $exists) {
                // Get all approved teachers from this school in this club
                $approvedTeachers = TeacherClub::where('club_id', $clubId)
                    ->where('school_id', $schoolId)
                    ->approved()
                    ->with(['user', 'school'])
                    ->get();

                // Get pending requests from this school in this club (only if user is leader)
                $isLeader = $user->teacherClubs()
                    ->where('club_id', $clubId)
                    ->where('school_id', $schoolId)
                    ->where('is_leader', true)
                    ->approved()
                    ->exists();

                $pendingRequests = collect();
                if ($isLeader) {
                    $pendingRequests = TeacherClub::where('club_id', $clubId)
                        ->where('school_id', $schoolId)
                        ->pending()
                        ->with(['user', 'school'])
                        ->get();
                }

                $clubsWithTeachers->push([
                    'club' => $teacherClub->club,
                    'school' => $teacherClub->school,
                    'approvedTeachers' => $approvedTeachers,
                    'pendingRequests' => $pendingRequests,
                    'isLeader' => $isLeader,
                ]);
            }
        }

        return view('client.back.students.index', [
            'teacherClubs' => $teacherClubs,
            'students' => $students,
            'classes' => $classes,
            'clubsWithTeachers' => $clubsWithTeachers,
        ]);
    }

    /**
     * Store a newly registered student.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'teacher_club_id' => ['required', 'exists:teacher_clubs,id'],
            'name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'gender' => ['required', 'string', 'in:male,female,other'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
        ]);

        // Verify the teacher_club belongs to the current teacher and is approved
        $teacherClub = TeacherClub::where('id', $validated['teacher_club_id'])
            ->where('user_id', $user->id)
            ->approved()
            ->firstOrFail();

        $age = Carbon::parse($validated['date_of_birth'])->age;
        if ($age < 1 || $age > 150) {
            return redirect()->route('client.students.index')
                ->withErrors(['date_of_birth' => 'Date of birth must result in age between 1 and 150.'])
                ->withInput();
        }

        Student::create([
            'teacher_club_id' => $teacherClub->id,
            'name' => $validated['name'],
            'date_of_birth' => $validated['date_of_birth'],
            'age' => $age,
            'gender' => $validated['gender'],
            'school_class_id' => $validated['school_class_id'],
        ]);

        return redirect()->route('client.students.index')
            ->with('status', 'Student registered successfully!');
    }

    /**
     * Store multiple students from CSV file.
     */
    public function bulkStore(Request $request, StudentCsvBulkImportService $csvImport): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'teacher_club_id' => ['required', 'exists:teacher_clubs,id'],
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'], // Max 5MB
        ]);

        $teacherClub = TeacherClub::where('id', $validated['teacher_club_id'])
            ->where('user_id', $user->id)
            ->approved()
            ->firstOrFail();

        $result = $csvImport->import($teacherClub, $request->file('csv_file'));

        if ($result['errors'] !== []) {
            return redirect()->route('client.students.index')
                ->with('error', $csvImport->formatErrorsForFlash($result['errors']))
                ->withInput();
        }

        return redirect()->route('client.students.index')
            ->with('status', "Successfully imported {$result['success_count']} student(s).");
    }

    /**
     * Update a student owned by the current teacher (approved club).
     */
    public function update(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeStudentForTeacher($student);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'string', 'in:male,female,other'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'date_of_birth' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'redirect_to' => ['nullable', 'string', 'max:2048'],
        ]);

        $age = Carbon::parse($validated['date_of_birth'])->age;
        if ($age < 1 || $age > 150) {
            return back()->withErrors(['date_of_birth' => 'Date of birth must result in age between 1 and 150.'])->withInput();
        }

        $student->update([
            'name' => $validated['name'],
            'gender' => $validated['gender'],
            'school_class_id' => (int) $validated['school_class_id'],
            'date_of_birth' => $validated['date_of_birth'],
            'age' => $age,
        ]);

        return $this->redirectAfterStudentMutation(
            $request,
            'Student updated.',
        );
    }

    /**
     * Delete a student owned by the current teacher (approved club).
     */
    public function destroy(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeStudentForTeacher($student);

        $student->delete();

        return $this->redirectAfterStudentMutation(
            $request,
            'Student removed from your club roster.',
        );
    }

    /**
     * Delete multiple students owned by the current teacher (approved clubs).
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
        ]);

        $manageableTeacherClubIds = $user->approvedCoMemberTeacherClubIds();

        $query = Student::query()
            ->whereIn('id', $validated['student_ids'])
            ->whereIn('teacher_club_id', $manageableTeacherClubIds);

        $deleted = $query->delete();
        $requested = count(array_unique($validated['student_ids']));

        if ($deleted === 0) {
            return redirect()->route('client.students.index')
                ->with('error', 'No students could be removed. Refresh the page and try again.');
        }

        if ($deleted < $requested) {
            return redirect()->route('client.students.index')
                ->with('status', "Removed {$deleted} student(s). Some rows were skipped because they are no longer in your club roster.");
        }

        return redirect()->route('client.students.index')
            ->with('status', "Removed {$deleted} student(s).");
    }

    private function authorizeStudentForTeacher(Student $student): void
    {
        $user = Auth::user();
        $canManage = $user->approvedCoMemberTeacherClubIds()->contains($student->teacher_club_id);

        abort_unless($canManage, 403);
    }

    private function redirectAfterStudentMutation(Request $request, string $message): RedirectResponse
    {
        $target = $request->input('redirect_to');
        if (is_string($target) && $target !== '' && str_starts_with($target, '/') && ! str_starts_with($target, '//')) {
            return redirect($target)->with('status', $message);
        }

        return redirect()->route('client.students.index')->with('status', $message);
    }
}
