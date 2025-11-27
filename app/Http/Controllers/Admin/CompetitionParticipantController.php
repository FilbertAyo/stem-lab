<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\CompetitionParticipant;
use App\Models\CompetitionAward;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompetitionParticipantController extends Controller
{
    /**
     * Display all participants across all competitions.
     */
    public function allParticipants(Request $request): View
    {
        $query = CompetitionParticipant::with([
            'competition.club',
            'teacherClub.user',
            'teacherClub.school',
            'participantStudents.student',
            'award',
            'submissions',
        ]);

        // Filter by competition
        if ($request->filled('competition_id')) {
            $query->where('competition_id', $request->competition_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('team_name', 'like', "%{$search}%")
                  ->orWhereHas('teacherClub.user', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('teacherClub.school', fn($q2) => $q2->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('competition', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        $participants = $query->latest('registered_at')->paginate(20)->withQueryString();

        // Get stats
        $stats = [
            'total' => CompetitionParticipant::count(),
            'pending' => CompetitionParticipant::where('status', 'pending')->count(),
            'approved' => CompetitionParticipant::where('status', 'approved')->count(),
            'rejected' => CompetitionParticipant::where('status', 'rejected')->count(),
        ];

        // Get competitions for filter dropdown
        $competitions = Competition::orderBy('name')->get();

        return view('admin.competitions.participants.all', compact('participants', 'stats', 'competitions'));
    }

    /**
     * Display participants for a competition.
     */
    public function index(Request $request, Competition $competition): View
    {
        $query = $competition->participants()
            ->with(['teacherClub.user', 'teacherClub.school', 'participantStudents.student', 'award', 'submissions']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by team name or teacher
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('team_name', 'like', "%{$search}%")
                  ->orWhereHas('teacherClub.user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('teacherClub.school', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $participants = $query->latest('registered_at')->paginate(20)->withQueryString();

        // Get stats
        $stats = [
            'total' => $competition->participants()->count(),
            'pending' => $competition->participants()->where('status', 'pending')->count(),
            'approved' => $competition->participants()->where('status', 'approved')->count(),
            'rejected' => $competition->participants()->where('status', 'rejected')->count(),
        ];

        return view('admin.competitions.participants.index', compact('competition', 'participants', 'stats'));
    }

    /**
     * Show participant details.
     */
    public function show(Competition $competition, CompetitionParticipant $participant): View
    {
        $participant->load([
            'teacherClub.user',
            'teacherClub.school',
            'teacherClub.club',
            'participantStudents.student.schoolClass',
            'submissions.attachments',
            'award',
        ]);

        return view('admin.competitions.participants.show', compact('competition', 'participant'));
    }

    /**
     * Approve a participant.
     */
    public function approve(Competition $competition, CompetitionParticipant $participant): RedirectResponse
    {
        // Validate requirements
        $studentCount = $participant->participantStudents()->count();
        $errors = [];

        if ($studentCount < $competition->min_members) {
            $errors[] = "Team has only {$studentCount} members, minimum required is {$competition->min_members}.";
        }

        if ($competition->max_members && $studentCount > $competition->max_members) {
            $errors[] = "Team has {$studentCount} members, maximum allowed is {$competition->max_members}.";
        }

        // Check age requirements
        if ($competition->min_age || $competition->max_age) {
            foreach ($participant->participantStudents as $ps) {
                $age = $ps->student->age;
                if ($competition->min_age && $age < $competition->min_age) {
                    $errors[] = "Student {$ps->student->name} is {$age} years old, minimum age is {$competition->min_age}.";
                }
                if ($competition->max_age && $age > $competition->max_age) {
                    $errors[] = "Student {$ps->student->name} is {$age} years old, maximum age is {$competition->max_age}.";
                }
            }
        }

        if (!empty($errors)) {
            return redirect()->back()->with('error', implode(' ', $errors));
        }

        $participant->update([
            'status' => 'approved',
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        return redirect()->back()->with('status', 'Participant approved successfully.');
    }

    /**
     * Reject a participant.
     */
    public function reject(Request $request, Competition $competition, CompetitionParticipant $participant): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $participant->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return redirect()->back()->with('status', 'Participant rejected.');
    }

    /**
     * Disqualify a participant.
     */
    public function disqualify(Request $request, Competition $competition, CompetitionParticipant $participant): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $participant->update([
            'status' => 'disqualified',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return redirect()->back()->with('status', 'Participant has been disqualified.');
    }

    /**
     * Update participant score.
     */
    public function updateScore(Request $request, Competition $competition, CompetitionParticipant $participant): RedirectResponse
    {
        $validated = $request->validate([
            'final_score' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $participant->update($validated);

        // Update ranks for all participants in this competition
        $this->updateRanks($competition);

        return redirect()->back()->with('status', 'Score updated successfully.');
    }

    /**
     * Assign award to participant.
     */
    public function assignAward(Request $request, Competition $competition, CompetitionParticipant $participant): RedirectResponse
    {
        $validated = $request->validate([
            'award_id' => ['nullable', 'exists:competition_awards,id'],
        ]);

        // Remove award from any other participant first
        if ($validated['award_id']) {
            CompetitionParticipant::where('competition_id', $competition->id)
                ->where('award_id', $validated['award_id'])
                ->update(['award_id' => null]);
        }

        $participant->update(['award_id' => $validated['award_id']]);

        return redirect()->back()->with('status', 'Award assigned successfully.');
    }

    /**
     * Update ranks based on scores.
     */
    private function updateRanks(Competition $competition): void
    {
        $participants = $competition->participants()
            ->whereNotNull('final_score')
            ->orderByDesc('final_score')
            ->get();

        $rank = 1;
        foreach ($participants as $participant) {
            $participant->update(['rank' => $rank++]);
        }
    }

    /**
     * Bulk approve participants.
     */
    public function bulkApprove(Request $request, Competition $competition): RedirectResponse
    {
        $validated = $request->validate([
            'participant_ids' => ['required', 'array'],
            'participant_ids.*' => ['exists:competition_participants,id'],
        ]);

        $approved = 0;
        $failed = 0;

        foreach ($validated['participant_ids'] as $id) {
            $participant = CompetitionParticipant::find($id);
            if ($participant && $participant->status === 'pending') {
                $studentCount = $participant->participantStudents()->count();
                
                // Basic validation
                if ($studentCount >= $competition->min_members) {
                    $participant->update([
                        'status' => 'approved',
                        'approved_at' => now(),
                    ]);
                    $approved++;
                } else {
                    $failed++;
                }
            }
        }

        $message = "{$approved} participant(s) approved.";
        if ($failed > 0) {
            $message .= " {$failed} failed due to requirements.";
        }

        return redirect()->back()->with('status', $message);
    }

    /**
     * Auto-assign awards based on ranks.
     */
    public function autoAssignAwards(Competition $competition): RedirectResponse
    {
        $awards = $competition->awards()->orderBy('position')->get();
        $participants = $competition->participants()
            ->where('status', 'approved')
            ->whereNotNull('final_score')
            ->orderByDesc('final_score')
            ->limit($awards->count())
            ->get();

        // Clear existing award assignments
        $competition->participants()->update(['award_id' => null]);

        // Assign awards based on rank
        foreach ($participants as $index => $participant) {
            if (isset($awards[$index])) {
                $participant->update(['award_id' => $awards[$index]->id]);
            }
        }

        return redirect()->back()->with('status', 'Awards auto-assigned based on scores.');
    }
}

