<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\CompetitionParticipant;
use App\Models\CompetitionParticipantStudent;
use App\Models\CompetitionSubmission;
use App\Models\SubmissionAttachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CompetitionController extends Controller
{
    /**
     * Display competitions available for the teacher's clubs.
     */
    public function index(): View
    {
        $user = Auth::user();
        
        // Get the clubs the teacher has joined
        $teacherClubs = $user->teacherClubs()->with('club')->get();
        $clubIds = $teacherClubs->pluck('club_id')->toArray();

        // Get competitions for those clubs
        $competitions = Competition::whereIn('club_id', $clubIds)
            ->where('is_active', true)
            ->whereIn('status', ['published', 'ongoing'])
            ->with(['club', 'awards'])
            ->withCount('participants')
            ->orderBy('competition_start', 'desc')
            ->get();

        // Get competitions the teacher has already joined
        $joinedCompetitionIds = CompetitionParticipant::whereIn('teacher_club_id', $teacherClubs->pluck('id'))
            ->pluck('competition_id')
            ->toArray();

        return view('client.back.competitions.index', compact('competitions', 'teacherClubs', 'joinedCompetitionIds'));
    }

    /**
     * Show competition details.
     */
    public function show(Competition $competition): View
    {
        $user = Auth::user();
        $teacherClubs = $user->teacherClubs()->where('club_id', $competition->club_id)->with(['school', 'students'])->get();
        
        // Check if teacher is already registered
        $existingParticipant = CompetitionParticipant::whereIn('teacher_club_id', $teacherClubs->pluck('id'))
            ->where('competition_id', $competition->id)
            ->with(['participantStudents.student', 'submissions.attachments', 'award'])
            ->first();

        $competition->load(['club', 'awards']);

        return view('client.back.competitions.show', compact('competition', 'teacherClubs', 'existingParticipant'));
    }

    /**
     * Show registration form for a competition.
     */
    public function register(Competition $competition): View
    {
        $user = Auth::user();
        
        // Get teacher's club membership for this competition's club
        $teacherClub = $user->teacherClubs()
            ->where('club_id', $competition->club_id)
            ->with(['school', 'students.schoolClass'])
            ->first();

        if (!$teacherClub) {
            abort(403, 'You must join this club first before registering for competitions.');
        }

        // Check if already registered
        $existing = CompetitionParticipant::where('teacher_club_id', $teacherClub->id)
            ->where('competition_id', $competition->id)
            ->exists();

        if ($existing) {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'You have already registered for this competition.');
        }

        // Check if registration is open
        if (!$competition->isRegistrationOpen()) {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'Registration for this competition is not open.');
        }

        // Filter students by age requirements
        $students = $teacherClub->students;
        if ($competition->min_age || $competition->max_age) {
            $students = $students->filter(function ($student) use ($competition) {
                if ($competition->min_age && $student->age < $competition->min_age) {
                    return false;
                }
                if ($competition->max_age && $student->age > $competition->max_age) {
                    return false;
                }
                return true;
            });
        }

        return view('client.back.competitions.register', compact('competition', 'teacherClub', 'students'));
    }

    /**
     * Process competition registration.
     */
    public function storeRegistration(Request $request, Competition $competition): RedirectResponse
    {
        $user = Auth::user();
        
        $teacherClub = $user->teacherClubs()
            ->where('club_id', $competition->club_id)
            ->first();

        if (!$teacherClub) {
            return redirect()->route('client.competitions.index')
                ->with('error', 'You must join this club first.');
        }

        // Check if already registered
        if (CompetitionParticipant::where('teacher_club_id', $teacherClub->id)->where('competition_id', $competition->id)->exists()) {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'You have already registered for this competition.');
        }

        // Validate
        $validated = $request->validate([
            'team_name' => ['nullable', 'string', 'max:255'],
            'team_description' => ['nullable', 'string', 'max:1000'],
            'student_ids' => ['required', 'array', 'min:' . $competition->min_members],
            'student_ids.*' => ['exists:students,id'],
            'student_roles' => ['nullable', 'array'],
        ]);

        // Validate max members
        if ($competition->max_members && count($validated['student_ids']) > $competition->max_members) {
            return back()->withErrors(['student_ids' => "Maximum {$competition->max_members} students allowed."])->withInput();
        }

        // Verify students belong to teacher's club and meet age requirements
        $students = $teacherClub->students()->whereIn('id', $validated['student_ids'])->get();
        
        if ($students->count() !== count($validated['student_ids'])) {
            return back()->withErrors(['student_ids' => 'Some selected students are not registered under your club.'])->withInput();
        }

        // Verify age requirements
        foreach ($students as $student) {
            if ($competition->min_age && $student->age < $competition->min_age) {
                return back()->withErrors(['student_ids' => "{$student->name} does not meet the minimum age requirement."])->withInput();
            }
            if ($competition->max_age && $student->age > $competition->max_age) {
                return back()->withErrors(['student_ids' => "{$student->name} exceeds the maximum age requirement."])->withInput();
            }
        }

        DB::transaction(function () use ($validated, $competition, $teacherClub, $request) {
            // Create participant record
            $participant = CompetitionParticipant::create([
                'competition_id' => $competition->id,
                'teacher_club_id' => $teacherClub->id,
                'team_name' => $validated['team_name'],
                'team_description' => $validated['team_description'],
                'status' => 'pending',
                'registered_at' => now(),
            ]);

            // Add students
            foreach ($validated['student_ids'] as $studentId) {
                CompetitionParticipantStudent::create([
                    'competition_participant_id' => $participant->id,
                    'student_id' => $studentId,
                    'role' => $request->input("student_roles.{$studentId}"),
                ]);
            }
        });

        return redirect()->route('client.competitions.show', $competition)
            ->with('status', 'Successfully registered for the competition! Your registration is pending approval.');
    }

    /**
     * Show submission form.
     */
    public function createSubmission(Competition $competition): View
    {
        $user = Auth::user();
        $teacherClub = $user->teacherClubs()->where('club_id', $competition->club_id)->first();
        
        $participant = CompetitionParticipant::where('teacher_club_id', $teacherClub?->id)
            ->where('competition_id', $competition->id)
            ->first();

        if (!$participant) {
            abort(403, 'You must register for this competition first.');
        }

        if ($participant->status !== 'approved') {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'Your registration must be approved before you can submit.');
        }

        // Check for existing draft submission
        $existingSubmission = $participant->submissions()->where('status', 'draft')->first();

        return view('client.back.competitions.submit', compact('competition', 'participant', 'existingSubmission'));
    }

    /**
     * Store or update submission.
     */
    public function storeSubmission(Request $request, Competition $competition): RedirectResponse
    {
        $user = Auth::user();
        $teacherClub = $user->teacherClubs()->where('club_id', $competition->club_id)->first();
        
        $participant = CompetitionParticipant::where('teacher_club_id', $teacherClub?->id)
            ->where('competition_id', $competition->id)
            ->first();

        if (!$participant || $participant->status !== 'approved') {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'You are not authorized to submit.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'methodology' => ['nullable', 'string'],
            'results' => ['nullable', 'string'],
            'conclusion' => ['nullable', 'string'],
            'materials_used' => ['nullable', 'string', 'max:2000'],
            'challenges_faced' => ['nullable', 'string', 'max:2000'],
            'learning_outcomes' => ['nullable', 'string', 'max:2000'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:20480'], // 20MB max per file
            'attachment_captions' => ['nullable', 'array'],
            'attachment_categories' => ['nullable', 'array'],
            'submit_final' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($validated, $participant, $request) {
            // Find or create submission
            $submission = $participant->submissions()->where('status', 'draft')->first();
            
            if (!$submission) {
                $submission = new CompetitionSubmission([
                    'competition_participant_id' => $participant->id,
                    'status' => 'draft',
                    'version' => $participant->submissions()->count() + 1,
                ]);
            }

            $submission->fill([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'methodology' => $validated['methodology'],
                'results' => $validated['results'],
                'conclusion' => $validated['conclusion'],
                'materials_used' => $validated['materials_used'],
                'challenges_faced' => $validated['challenges_faced'],
                'learning_outcomes' => $validated['learning_outcomes'],
            ]);

            // Check if final submission
            if ($request->boolean('submit_final')) {
                $submission->status = 'submitted';
                $submission->submitted_at = now();
            }

            $submission->save();

            // Handle file uploads
            if ($request->hasFile('attachments')) {
                $order = $submission->attachments()->max('order') ?? 0;
                
                foreach ($request->file('attachments') as $index => $file) {
                    $path = $file->store('competition-submissions/' . $submission->id, 'public');
                    $mimeType = $file->getMimeType();
                    
                    // Determine type
                    $type = 'other';
                    if (str_starts_with($mimeType, 'image/')) {
                        $type = 'image';
                    } elseif ($mimeType === 'application/pdf') {
                        $type = 'pdf';
                    } elseif (str_contains($mimeType, 'document') || str_contains($mimeType, 'word') || str_contains($mimeType, 'text')) {
                        $type = 'document';
                    } elseif (str_starts_with($mimeType, 'video/')) {
                        $type = 'video';
                    }

                    SubmissionAttachment::create([
                        'competition_submission_id' => $submission->id,
                        'type' => $type,
                        'file_path' => $path,
                        'original_filename' => $file->getClientOriginalName(),
                        'mime_type' => $mimeType,
                        'file_size' => $file->getSize(),
                        'caption' => $request->input("attachment_captions.{$index}"),
                        'category' => $request->input("attachment_categories.{$index}"),
                        'order' => ++$order,
                    ]);
                }
            }
        });

        $message = $request->boolean('submit_final') 
            ? 'Submission sent successfully! It will be reviewed by the judges.'
            : 'Draft saved successfully.';

        return redirect()->route('client.competitions.show', $competition)->with('status', $message);
    }

    /**
     * View my submissions.
     */
    public function mySubmissions(): View
    {
        $user = Auth::user();
        $teacherClubIds = $user->teacherClubs()->pluck('id');

        $participants = CompetitionParticipant::whereIn('teacher_club_id', $teacherClubIds)
            ->with([
                'competition.club',
                'submissions.attachments',
                'participantStudents.student',
                'award',
            ])
            ->latest('registered_at')
            ->get();

        return view('client.back.competitions.my-submissions', compact('participants'));
    }

    /**
     * Delete an attachment.
     */
    public function deleteAttachment(Competition $competition, SubmissionAttachment $attachment): RedirectResponse
    {
        $user = Auth::user();
        $submission = $attachment->submission;
        $participant = $submission->participant;
        
        // Verify ownership
        $teacherClubIds = $user->teacherClubs()->pluck('id')->toArray();
        if (!in_array($participant->teacher_club_id, $teacherClubIds)) {
            abort(403);
        }

        // Can only delete from draft submissions
        if ($submission->status !== 'draft') {
            return back()->with('error', 'Cannot delete attachments from submitted entries.');
        }

        // Delete file
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        return back()->with('status', 'Attachment deleted.');
    }
}

