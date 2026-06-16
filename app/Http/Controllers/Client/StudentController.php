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

class StudentController extends Controller
{
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
            'redirect_to' => ['nullable', 'string', 'max:2048'],
        ]);

        $teacherClub = TeacherClub::where('id', $validated['teacher_club_id'])
            ->where('user_id', $user->id)
            ->approved()
            ->firstOrFail();

        $age = Carbon::parse($validated['date_of_birth'])->age;
        if ($age < 1 || $age > 150) {
            return $this->redirectAfterStudentMutation($request, $teacherClub)
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

        return $this->redirectAfterStudentMutation($request, $teacherClub, 'Student registered successfully!');
    }

    /**
     * Store multiple students from CSV file.
     */
    public function bulkStore(Request $request, StudentCsvBulkImportService $csvImport): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'teacher_club_id' => ['required', 'exists:teacher_clubs,id'],
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
            'redirect_to' => ['nullable', 'string', 'max:2048'],
        ]);

        $teacherClub = TeacherClub::where('id', $validated['teacher_club_id'])
            ->where('user_id', $user->id)
            ->approved()
            ->firstOrFail();

        $result = $csvImport->import($teacherClub, $request->file('csv_file'));

        if ($result['errors'] !== []) {
            return $this->redirectAfterStudentMutation($request, $teacherClub)
                ->with('error', $csvImport->formatErrorsForFlash($result['errors']))
                ->withInput();
        }

        return $this->redirectAfterStudentMutation(
            $request,
            $teacherClub,
            "Successfully imported {$result['success_count']} student(s).",
        );
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
            $student->teacherClub,
            'Student updated.',
        );
    }

    /**
     * Delete a student owned by the current teacher (approved club).
     */
    public function destroy(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeStudentForTeacher($student);

        $teacherClub = $student->teacherClub;
        $student->delete();

        return $this->redirectAfterStudentMutation(
            $request,
            $teacherClub,
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
            'redirect_to' => ['nullable', 'string', 'max:2048'],
            'teacher_club_id' => ['nullable', 'exists:teacher_clubs,id'],
        ]);

        $manageableTeacherClubIds = $user->approvedCoMemberTeacherClubIds();

        $students = Student::query()
            ->whereIn('id', $validated['student_ids'])
            ->whereIn('teacher_club_id', $manageableTeacherClubIds)
            ->get();

        $deleted = $students->count();
        Student::query()->whereIn('id', $students->pluck('id'))->delete();

        $requested = count(array_unique($validated['student_ids']));

        $teacherClub = null;
        if (! empty($validated['teacher_club_id'])) {
            $teacherClub = TeacherClub::query()->find($validated['teacher_club_id']);
        }
        if ($teacherClub === null && $students->isNotEmpty()) {
            $teacherClub = $students->first()->teacherClub;
        }

        if ($deleted === 0) {
            return $this->redirectAfterStudentMutation($request, $teacherClub)
                ->with('error', 'No students could be removed. Refresh the page and try again.');
        }

        if ($deleted < $requested) {
            return $this->redirectAfterStudentMutation(
                $request,
                $teacherClub,
                "Removed {$deleted} student(s). Some rows were skipped because they are no longer in your club roster.",
            );
        }

        return $this->redirectAfterStudentMutation(
            $request,
            $teacherClub,
            "Removed {$deleted} student(s).",
        );
    }

    private function authorizeStudentForTeacher(Student $student): void
    {
        $user = Auth::user();
        $canManage = $user->approvedCoMemberTeacherClubIds()->contains($student->teacher_club_id);

        abort_unless($canManage, 403);
    }

    private function redirectAfterStudentMutation(
        Request $request,
        ?TeacherClub $teacherClub = null,
        ?string $message = null,
    ): RedirectResponse {
        $target = $request->input('redirect_to');
        if (is_string($target) && $target !== '' && str_starts_with($target, '/') && ! str_starts_with($target, '//')) {
            $redirect = redirect($target);
            if ($message !== null) {
                $redirect->with('status', $message);
            }

            return $redirect;
        }

        if ($teacherClub !== null) {
            $redirect = redirect()->route('client.clubs.show', $teacherClub);
            if ($message !== null) {
                $redirect->with('status', $message);
            }

            return $redirect;
        }

        $redirect = redirect()->route('client.clubs.index');
        if ($message !== null) {
            $redirect->with('status', $message);
        }

        return $redirect;
    }
}
