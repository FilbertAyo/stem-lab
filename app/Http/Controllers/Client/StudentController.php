<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\TeacherClub;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentController extends Controller
{
    /**
     * Display student registration panel.
     */
    public function index(): View
    {
        $user = Auth::user();

        $teacherClubs = TeacherClub::where('user_id', $user->id)
            ->with(['club', 'school'])
            ->get();

        $students = Student::whereIn('teacher_club_id', $teacherClubs->pluck('id'))
            ->with(['schoolClass', 'teacherClub.club', 'teacherClub.school'])
            ->orderBy('name')
            ->get();

        // Get all active classes for dropdown
        $classes = SchoolClass::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('client.back.students.index', [
            'teacherClubs' => $teacherClubs,
            'students' => $students,
            'classes' => $classes,
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
            'age' => ['required', 'integer', 'min:1', 'max:150'],
            'gender' => ['required', 'string', 'in:male,female,other'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
        ]);

        // Verify the teacher_club belongs to the current teacher
        $teacherClub = TeacherClub::where('id', $validated['teacher_club_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        Student::create($validated);

        return redirect()->route('client.students.index')
            ->with('status', 'Student registered successfully!');
    }
}
