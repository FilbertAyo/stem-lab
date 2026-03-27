<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\TeacherClub;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        // Get all students
        $students = Student::whereIn('teacher_club_id', $teacherClubs->pluck('id'))
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
            
            if (!$exists) {
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
    public function bulkStore(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'teacher_club_id' => ['required', 'exists:teacher_clubs,id'],
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'], // Max 5MB
        ]);

        // Verify the teacher_club belongs to the current teacher and is approved
        $teacherClub = TeacherClub::where('id', $validated['teacher_club_id'])
            ->where('user_id', $user->id)
            ->approved()
            ->firstOrFail();

        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        
        // Get all active classes for name matching
        $classes = SchoolClass::where('is_active', true)
            ->pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id]);

        $errors = [];
        $successCount = 0;
        $lineNumber = 0;
        $tempPath = null;
        $handle = null;

        try {
            // Read file content and handle BOM
            $content = file_get_contents($path);
            
            // Remove BOM if present (UTF-8 BOM: EF BB BF)
            if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
                $content = substr($content, 3);
            }
            
            // Write back to temp file without BOM
            $tempPath = sys_get_temp_dir() . '/csv_upload_' . uniqid() . '.csv';
            file_put_contents($tempPath, $content);
            
            $handle = fopen($tempPath, 'r');
            
            if ($handle === false) {
                @unlink($tempPath);
                return redirect()->route('client.students.index')
                    ->with('error', 'Failed to read the CSV file.');
            }

            // Read header row
            $header = fgetcsv($handle);
            $lineNumber++;

            if ($header === false || empty($header)) {
                fclose($handle);
                @unlink($tempPath);
                return redirect()->route('client.students.index')
                    ->with('error', 'The CSV file is empty.');
            }

            // Normalize header (trim whitespace and lowercase)
            // fgetcsv() already handles CSV quote removal automatically
            $header = array_map(fn($h) => strtolower(trim($h)), $header);
            
            // Validate required columns. Support new template (date_of_birth)
            // and old template (age) for backward compatibility.
            $baseRequiredColumns = ['name', 'gender', 'class_name'];
            $missingBaseColumns = array_diff($baseRequiredColumns, $header);
            $hasDobColumn = in_array('date_of_birth', $header, true);
            $hasAgeColumn = in_array('age', $header, true);

            if (!empty($missingBaseColumns) || (!$hasDobColumn && !$hasAgeColumn)) {
                $missingColumns = $missingBaseColumns;
                if (!$hasDobColumn && !$hasAgeColumn) {
                    $missingColumns[] = 'date_of_birth (or age)';
                }

                fclose($handle);
                @unlink($tempPath);
                return redirect()->route('client.students.index')
                    ->with('error', 'CSV file is missing required columns: ' . implode(', ', $missingColumns) . '. Found columns: ' . implode(', ', $header));
            }

            // Get column indices
            $nameIndex = array_search('name', $header);
            $ageIndex = array_search('age', $header);
            $dobIndex = array_search('date_of_birth', $header);
            $genderIndex = array_search('gender', $header);
            $classIndex = array_search('class_name', $header);

            // Start database transaction
            DB::beginTransaction();

            // Process each row
            while (($row = fgetcsv($handle)) !== false) {
                $lineNumber++;

                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Validate row has enough columns
                if (count($row) < count($header)) {
                    $errors[] = "Line {$lineNumber}: Row has insufficient columns.";
                    continue;
                }

                $name = trim($row[$nameIndex] ?? '');
                $age = null;
                $dob = null;
                $gender = strtolower(trim($row[$genderIndex] ?? ''));
                $className = trim($row[$classIndex] ?? '');

                if ($dobIndex !== false) {
                    $dob = trim($row[$dobIndex] ?? '');
                }
                if ($ageIndex !== false) {
                    $age = trim($row[$ageIndex] ?? '');
                }

                // Validate name
                if (empty($name)) {
                    $errors[] = "Line {$lineNumber}: Student name is required.";
                    continue;
                }

                if (strlen($name) > 255) {
                    $errors[] = "Line {$lineNumber}: Student name exceeds 255 characters.";
                    continue;
                }

                // Validate date_of_birth first when present; otherwise fallback to age.
                if (!empty($dob)) {
                    try {
                        $parsedDob = Carbon::createFromFormat('Y-m-d', $dob);

                        if ($parsedDob->format('Y-m-d') !== $dob) {
                            throw new \Exception('Invalid date format');
                        }

                        if ($parsedDob->isFuture()) {
                            $errors[] = "Line {$lineNumber}: Date of birth cannot be in the future.";
                            continue;
                        }
                        $age = $parsedDob->age;
                    } catch (\Exception $e) {
                        $errors[] = "Line {$lineNumber}: Invalid date_of_birth format. Use YYYY-MM-DD.";
                        continue;
                    }
                } elseif (!empty($age) && is_numeric($age)) {
                    $age = (int) $age;
                } else {
                    $errors[] = "Line {$lineNumber}: Provide date_of_birth (preferred) or a valid age.";
                    continue;
                }

                if ($age < 1 || $age > 150) {
                    $errors[] = "Line {$lineNumber}: Computed age must be between 1 and 150.";
                    continue;
                }

                // Validate gender
                if (empty($gender) || !in_array($gender, ['male', 'female', 'other'])) {
                    $errors[] = "Line {$lineNumber}: Gender must be 'male', 'female', or 'other'.";
                    continue;
                }

                // Validate and find class
                if (empty($className)) {
                    $errors[] = "Line {$lineNumber}: Class name is required.";
                    continue;
                }

                $classKey = strtolower($className);
                $schoolClassId = $classes[$classKey] ?? null;

                if (!$schoolClassId) {
                    $errors[] = "Line {$lineNumber}: Class '{$className}' not found. Please ensure the class exists and is active.";
                    continue;
                }

                // Create student
                try {
                    Student::create([
                        'teacher_club_id' => $teacherClub->id,
                        'name' => $name,
                        'age' => $age,
                        'gender' => $gender,
                        'school_class_id' => $schoolClassId,
                    ]);
                    $successCount++;
                } catch (\Exception $e) {
                    Log::error("Failed to create student on line {$lineNumber}: " . $e->getMessage());
                    $errors[] = "Line {$lineNumber}: Failed to create student - " . $e->getMessage();
                }
            }

            fclose($handle);
            @unlink($tempPath);

            // If there are errors, rollback and return with errors
            if (!empty($errors)) {
                DB::rollBack();
                
                $errorMessage = "Bulk upload completed with errors:\n" . implode("\n", array_slice($errors, 0, 10));
                if (count($errors) > 10) {
                    $errorMessage .= "\n... and " . (count($errors) - 10) . " more errors.";
                }

                return redirect()->route('client.students.index')
                    ->with('error', $errorMessage)
                    ->withInput();
            }

            DB::commit();

            $message = "Successfully imported {$successCount} student(s).";
            return redirect()->route('client.students.index')
                ->with('status', $message);

        } catch (\Exception $e) {
            if (isset($handle) && $handle !== false) {
                fclose($handle);
            }
            if (isset($tempPath) && file_exists($tempPath)) {
                @unlink($tempPath);
            }
            DB::rollBack();
            Log::error("Bulk upload error: " . $e->getMessage());

            return redirect()->route('client.students.index')
                ->with('error', 'An error occurred during bulk upload: ' . $e->getMessage())
                ->withInput();
        }
    }
}
