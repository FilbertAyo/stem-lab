<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\TeacherClub;
use App\Models\WeeklySession;
use App\Models\WeeklySessionReport;
use App\Models\WeeklySessionReportPhoto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class WeeklySessionController extends Controller
{
    /**
     * Display a listing of weekly sessions for the teacher's clubs.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Get teacher's approved club memberships
        $teacherClubs = $user->teacherClubs()
            ->approved()
            ->with('club')
            ->get();

        $clubIds = $teacherClubs->pluck('club_id')->toArray();

        if (empty($clubIds)) {
            // Teacher has no clubs, return empty list
            $emptySessions = new LengthAwarePaginator(
                [],
                0,
                15,
                $request->get('page', 1),
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );
            
            return view('client.back.weekly-sessions.index', [
                'sessions' => $emptySessions,
                'teacherClubs' => collect(),
                'statusFilter' => $request->get('status', 'all'),
            ]);
        }

        // Build query for sessions visible to teachers
        $query = WeeklySession::visibleToTeachers($clubIds)
            ->with(['club', 'subject', 'vrExperiment', 'classes', 'project'])
            ->orderedByDate('asc');

        // Filter by status
        $statusFilter = $request->get('status', 'all');
        if ($statusFilter !== 'all') {
            if ($statusFilter === 'upcoming') {
                $query->upcoming();
            } elseif ($statusFilter === 'active') {
                $query->withStatus('active');
            } elseif ($statusFilter === 'completed') {
                $query->where(function ($q) {
                    $q->withStatus('completed')
                      ->orWhere(function ($subQ) {
                          $subQ->past()->where('status', 'scheduled');
                      });
                });
            } elseif ($statusFilter === 'past') {
                $query->past();
            } else {
                $query->withStatus($statusFilter);
            }
        }

        // Filter by club
        if ($request->filled('club_id')) {
            $query->where('club_id', $request->club_id);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('session_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('session_date', '<=', $request->end_date);
        }

        $sessions = $query->paginate(15)->withQueryString();

        // Load reports for each session to check if teacher has submitted one
        $sessionIds = $sessions->pluck('id')->toArray();
        $teacherClubIds = $teacherClubs->pluck('id')->toArray();
        
        $reports = WeeklySessionReport::whereIn('weekly_session_id', $sessionIds)
            ->whereIn('teacher_club_id', $teacherClubIds)
            ->get()
            ->keyBy(function ($report) {
                return $report->weekly_session_id . '_' . $report->teacher_club_id;
            });

        // Attach report status to each session
        foreach ($sessions as $session) {
            $session->teacherReport = null;
            foreach ($teacherClubs as $teacherClub) {
                $key = $session->id . '_' . $teacherClub->id;
                if ($reports->has($key)) {
                    $session->teacherReport = $reports->get($key);
                    break;
                }
            }
        }

        return view('client.back.weekly-sessions.index', [
            'sessions' => $sessions,
            'teacherClubs' => $teacherClubs,
            'statusFilter' => $statusFilter,
        ]);
    }

    /**
     * Display the specified weekly session.
     */
    public function show(WeeklySession $weeklySession): View
    {
        $user = Auth::user();

        // Verify teacher belongs to the session's club
        $teacherClub = $user->teacherClubs()
            ->approved()
            ->where('club_id', $weeklySession->club_id)
            ->first();

        if (!$teacherClub) {
            abort(403, 'You do not have access to this session.');
        }

        // Verify session is visible (not draft)
        if ($weeklySession->status === 'draft') {
            abort(404);
        }

        // Load relationships
        $weeklySession->load(['club', 'subject', 'vrExperiment', 'classes', 'project']);

        // Get students assigned to this session
        // Students whose class matches session classes AND belong to teacher's teacher_club_id
        $sessionClassIds = $weeklySession->classes->pluck('id')->toArray();
        $students = collect();

        if (!empty($sessionClassIds)) {
            $students = Student::where('teacher_club_id', $teacherClub->id)
                ->whereIn('school_class_id', $sessionClassIds)
                ->with('schoolClass')
                ->orderBy('name')
                ->get();
        }

        // Get student counts by class
        $studentCountsByClass = [];
        foreach ($weeklySession->classes as $class) {
            $studentCountsByClass[$class->id] = Student::where('teacher_club_id', $teacherClub->id)
                ->where('school_class_id', $class->id)
                ->count();
        }

        // Check if teacher has submitted a report
        $report = WeeklySessionReport::where('weekly_session_id', $weeklySession->id)
            ->where('teacher_club_id', $teacherClub->id)
            ->with('photos')
            ->first();

        return view('client.back.weekly-sessions.show', [
            'weeklySession' => $weeklySession,
            'teacherClub' => $teacherClub,
            'students' => $students,
            'studentCountsByClass' => $studentCountsByClass,
            'report' => $report,
        ]);
    }

    /**
     * Show the form for creating a new report.
     */
    public function createReport(WeeklySession $weeklySession): View
    {
        $user = Auth::user();

        // Verify teacher belongs to the session's club
        $teacherClub = $user->teacherClubs()
            ->approved()
            ->where('club_id', $weeklySession->club_id)
            ->first();

        if (!$teacherClub) {
            abort(403, 'You do not have access to this session.');
        }

        // Check if report already exists
        $report = WeeklySessionReport::where('weekly_session_id', $weeklySession->id)
            ->where('teacher_club_id', $teacherClub->id)
            ->first();

        if ($report) {
            return redirect()->route('client.weekly-sessions.report.edit', $weeklySession);
        }

        $weeklySession->load(['club', 'classes', 'subject']);

        // Get total students registered for auto-fill
        $totalStudentsRegistered = Student::where('teacher_club_id', $teacherClub->id)->count();

        return view('client.back.weekly-sessions.report', [
            'weeklySession' => $weeklySession,
            'teacherClub' => $teacherClub,
            'report' => null,
            'totalStudentsRegistered' => $totalStudentsRegistered,
        ]);
    }

    /**
     * Store a newly created report.
     */
    public function storeReport(Request $request, WeeklySession $weeklySession): RedirectResponse
    {
        $user = Auth::user();

        // Verify teacher belongs to the session's club
        $teacherClub = $user->teacherClubs()
            ->approved()
            ->where('club_id', $weeklySession->club_id)
            ->first();

        if (!$teacherClub) {
            abort(403, 'You do not have access to this session.');
        }

        // Check if report already exists
        $existingReport = WeeklySessionReport::where('weekly_session_id', $weeklySession->id)
            ->where('teacher_club_id', $teacherClub->id)
            ->first();

        if ($existingReport) {
            return redirect()->route('client.weekly-sessions.report.edit', $weeklySession)
                ->with('error', 'A report already exists for this session. Please edit it instead.');
        }

        $validated = $this->validateReport($request);

        $validated['weekly_session_id'] = $weeklySession->id;
        $validated['teacher_club_id'] = $teacherClub->id;
        $validated['user_id'] = $user->id;

        if (empty($validated['conducted_at'])) {
            $validated['conducted_at'] = now();
        }

        // Handle JSON arrays
        if ($request->has('absence_reasons')) {
            $validated['absence_reasons'] = $request->input('absence_reasons', []);
        }
        if ($request->has('understanding_check_methods')) {
            $validated['understanding_check_methods'] = $request->input('understanding_check_methods', []);
        }
        if ($request->has('challenge_types')) {
            $validated['challenge_types'] = $request->input('challenge_types', []);
        }

        $report = WeeklySessionReport::create($validated);

        // Handle photo uploads
        if ($request->hasFile('photos')) {
            $this->handlePhotoUploads($request, $report);
        }

        return redirect()->route('client.weekly-sessions.show', $weeklySession)
            ->with('status', 'Report submitted successfully.');
    }

    /**
     * Show the form for editing an existing report.
     */
    public function editReport(WeeklySession $weeklySession): View
    {
        $user = Auth::user();

        // Verify teacher belongs to the session's club
        $teacherClub = $user->teacherClubs()
            ->approved()
            ->where('club_id', $weeklySession->club_id)
            ->first();

        if (!$teacherClub) {
            abort(403, 'You do not have access to this session.');
        }

        $report = WeeklySessionReport::where('weekly_session_id', $weeklySession->id)
            ->where('teacher_club_id', $teacherClub->id)
            ->with('photos')
            ->first();

        if (!$report) {
            return redirect()->route('client.weekly-sessions.report.create', $weeklySession);
        }

        $weeklySession->load(['club', 'classes', 'subject']);

        // Get total students registered for auto-fill
        $totalStudentsRegistered = Student::where('teacher_club_id', $teacherClub->id)->count();

        return view('client.back.weekly-sessions.report', [
            'weeklySession' => $weeklySession,
            'teacherClub' => $teacherClub,
            'report' => $report,
            'totalStudentsRegistered' => $totalStudentsRegistered,
        ]);
    }

    /**
     * Update an existing report.
     */
    public function updateReport(Request $request, WeeklySession $weeklySession): RedirectResponse
    {
        $user = Auth::user();

        // Verify teacher belongs to the session's club
        $teacherClub = $user->teacherClubs()
            ->approved()
            ->where('club_id', $weeklySession->club_id)
            ->first();

        if (!$teacherClub) {
            abort(403, 'You do not have access to this session.');
        }

        $report = WeeklySessionReport::where('weekly_session_id', $weeklySession->id)
            ->where('teacher_club_id', $teacherClub->id)
            ->first();

        if (!$report) {
            return redirect()->route('client.weekly-sessions.report.create', $weeklySession);
        }

        $validated = $this->validateReport($request);

        // Validate photos and deletions separately
        $request->validate([
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'delete_photos' => ['nullable', 'array'],
            'delete_photos.*' => ['nullable', 'integer', 'exists:weekly_session_report_photos,id'],
        ]);

        // Map students_present to attendance_count for backward compatibility
        if (isset($validated['students_present'])) {
            $validated['attendance_count'] = $validated['students_present'];
        }

        // Handle JSON arrays
        if ($request->has('absence_reasons')) {
            $validated['absence_reasons'] = $request->input('absence_reasons', []);
        }
        if ($request->has('understanding_check_methods')) {
            $validated['understanding_check_methods'] = $request->input('understanding_check_methods', []);
        }
        if ($request->has('challenge_types')) {
            $validated['challenge_types'] = $request->input('challenge_types', []);
        }

        $report->update($validated);

        // Handle photo deletions first (before adding new ones)
        if ($request->has('delete_photos') && !empty($request->input('delete_photos'))) {
            $this->handlePhotoDeletions($request->input('delete_photos', []));
        }

        // Handle new photo uploads
        if ($request->hasFile('photos')) {
            $this->handlePhotoUploads($request, $report);
        }

        return redirect()->route('client.weekly-sessions.show', $weeklySession)
            ->with('status', 'Report updated successfully.');
    }

    /**
     * Validate report data.
     */
    private function validateReport(Request $request): array
    {
        return $request->validate([
            // Attendance
            'total_students_registered' => ['nullable', 'integer', 'min:0'],
            'attendance_count' => ['nullable', 'integer', 'min:0'],
            'students_present' => ['nullable', 'integer', 'min:0'],
            'girls_present' => ['nullable', 'integer', 'min:0'],
            'had_absent_students' => ['nullable', 'boolean'],
            'absence_reasons' => ['nullable', 'array'],
            'absence_reasons.*' => ['nullable', 'string', 'in:sickness,family_reasons,school_activities,weather_transport,no_reason,other'],
            'absence_reason_other' => ['nullable', 'string', 'max:255'],
            
            // Session Details
            'activity_type' => ['nullable', 'string', 'in:hands_on_experiment,project_work,group_discussion,competition_challenge,guest_speaker,video_demonstration'],
            'activity_completed' => ['nullable', 'string', 'in:fully_completed,partially_completed,not_completed'],
            'incompletion_reason' => ['nullable', 'string', 'max:2000'],
            
            // Student Engagement
            'engagement_level' => ['nullable', 'string', 'in:very_engaged,moderately_engaged,low_engagement'],
            'questions_participation' => ['nullable', 'string', 'in:most_students_75pct,about_half_50_75pct,some_students_25_50pct,very_few_25pct'],
            'girls_participation' => ['nullable', 'string', 'in:very_active,moderately,not_much,no_girls_present'],
            'students_special_interest' => ['nullable', 'boolean'],
            'special_interest_details' => ['nullable', 'string', 'max:2000'],
            
            // Learning Outcomes
            'learning_understanding' => ['nullable', 'string', 'in:all_understood,most_understood,some_understood,struggled'],
            'understanding_check_methods' => ['nullable', 'array'],
            'understanding_check_methods.*' => ['nullable', 'string', 'in:questions,observed_work,quiz_test,presentations,group_discussions,other'],
            'understanding_check_other' => ['nullable', 'string', 'max:255'],
            'students_learned' => ['nullable', 'string', 'max:2000'],
            
            // Challenges
            'had_challenges' => ['nullable', 'boolean'],
            'challenge_types' => ['nullable', 'array'],
            'challenge_types.*' => ['nullable', 'string', 'in:not_enough_materials,equipment_not_working,too_difficult,too_easy,classroom_management,not_enough_time,internet_electricity,other'],
            'challenge_other' => ['nullable', 'string', 'max:255'],
            'challenge_description' => ['nullable', 'string', 'max:2000'],
            
            // Resources
            'resources_used' => ['nullable', 'string', 'max:2000'],
            'needs_additional_materials' => ['nullable', 'boolean'],
            'additional_materials_needed' => ['nullable', 'string', 'max:2000'],
            
            // Student Confidence & Attitudes
            'student_confidence' => ['nullable', 'string', 'in:very_confident,somewhat_confident,not_very_confident,hesitant_afraid'],
            'teamwork_quality' => ['nullable', 'string', 'in:excellent_teamwork,good_teamwork,teamwork_issues,preferred_alone,not_applicable'],
            'stem_attitudes' => ['nullable', 'string', 'in:excited_positive,interested_neutral,bored_uninterested'],
            
            // Projects & Competitions
            'has_active_projects' => ['nullable', 'boolean'],
            'project_description' => ['nullable', 'string', 'max:2000'],
            'project_completion_status' => ['nullable', 'string', 'in:just_started_0_25,in_progress_25_75,almost_done_75_100,completed,not_applicable'],
            'competition_preparation' => ['nullable', 'boolean'],
            'competition_name' => ['nullable', 'string', 'max:255'],
            
            // Highlights
            'session_highlight' => ['nullable', 'string', 'max:2000'],
            'interesting_questions' => ['nullable', 'string', 'max:2000'],
            
            // Additional Comments
            'additional_comments' => ['nullable', 'string', 'max:2000'],
            'needs_support' => ['nullable', 'boolean'],
            'support_needed' => ['nullable', 'string', 'max:2000'],
            
            // Teacher Reflection
            'session_satisfaction' => ['nullable', 'string', 'in:very_satisfied,satisfied,neutral,not_satisfied'],
            'would_do_differently' => ['nullable', 'string', 'max:2000'],
            'what_worked_well' => ['nullable', 'string', 'max:2000'],
            
            // Legacy fields (for backward compatibility)
            'feedback' => ['nullable', 'string', 'max:2000'],
            'engagement_notes' => ['nullable', 'string', 'max:2000'],
            'conducted_at' => ['nullable', 'date'],
        ]);
    }

    /**
     * Handle photo uploads for a report.
     */
    private function handlePhotoUploads(Request $request, WeeklySessionReport $report): void
    {
        $existingPhotoCount = $report->photos()->count();
        $maxPhotos = 5;
        $remainingSlots = $maxPhotos - $existingPhotoCount;

        if ($remainingSlots <= 0) {
            return;
        }

        $photos = $request->file('photos', []);
        $photosToUpload = array_slice($photos, 0, $remainingSlots);

        $order = $report->photos()->max('order') ?? 0;

        foreach ($photosToUpload as $photo) {
            if ($photo && $photo->isValid()) {
                $path = $photo->store('weekly-session-reports/photos', 'public');
                
                WeeklySessionReportPhoto::create([
                    'weekly_session_report_id' => $report->id,
                    'file_path' => $path,
                    'original_filename' => $photo->getClientOriginalName(),
                    'order' => ++$order,
                ]);
            }
        }
    }

    /**
     * Handle photo deletions.
     */
    private function handlePhotoDeletions(array $photoIds): void
    {
        $photos = WeeklySessionReportPhoto::whereIn('id', $photoIds)->get();

        foreach ($photos as $photo) {
            // Delete file from storage
            if (Storage::disk('public')->exists($photo->file_path)) {
                Storage::disk('public')->delete($photo->file_path);
            }

            // Delete record
            $photo->delete();
        }
    }

    /**
     * Upload a PDF report for a weekly session.
     */
    public function uploadPdfReport(Request $request, WeeklySession $weeklySession): RedirectResponse
    {
        $user = Auth::user();

        // Verify teacher belongs to the session's club
        $teacherClub = $user->teacherClubs()
            ->approved()
            ->where('club_id', $weeklySession->club_id)
            ->first();

        if (!$teacherClub) {
            abort(403, 'You do not have access to this session.');
        }

        // Validate PDF file
        $validated = $request->validate([
            'pdf_file' => ['required', 'file', 'mimes:pdf', 'max:10240'], // Max 10MB
        ]);

        // Check if report already exists
        $report = WeeklySessionReport::where('weekly_session_id', $weeklySession->id)
            ->where('teacher_club_id', $teacherClub->id)
            ->first();

        // Handle PDF upload
        $pdfPath = $request->file('pdf_file')->store('weekly-session-reports/pdfs', 'public');

        if ($report) {
            // Delete old PDF if exists
            if ($report->pdf_file && Storage::disk('public')->exists($report->pdf_file)) {
                Storage::disk('public')->delete($report->pdf_file);
            }
            // Update existing report with PDF
            $report->update([
                'pdf_file' => $pdfPath,
                'submitted_at' => now(),
            ]);
        } else {
            // Create new report with PDF
            WeeklySessionReport::create([
                'weekly_session_id' => $weeklySession->id,
                'teacher_club_id' => $teacherClub->id,
                'user_id' => $user->id,
                'pdf_file' => $pdfPath,
                'submitted_at' => now(),
            ]);
        }

        return redirect()->route('client.weekly-sessions.show', $weeklySession)
            ->with('status', 'PDF report uploaded successfully.');
    }
}
