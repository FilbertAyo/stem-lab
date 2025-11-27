<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\CompetitionParticipant;
use App\Models\CompetitionSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompetitionSubmissionController extends Controller
{
    /**
     * Display all submissions for a competition.
     */
    public function index(Request $request, Competition $competition): View
    {
        $query = CompetitionSubmission::whereHas('participant', function ($q) use ($competition) {
            $q->where('competition_id', $competition->id);
        })->with([
            'participant.teacherClub.user',
            'participant.teacherClub.school',
            'attachments',
            'judge',
        ]);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by judged status
        if ($request->filled('judged')) {
            if ($request->judged === 'yes') {
                $query->whereNotNull('score');
            } else {
                $query->whereNull('score');
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $submissions = $query->latest('submitted_at')->paginate(20)->withQueryString();

        // Stats
        $stats = [
            'total' => CompetitionSubmission::whereHas('participant', fn($q) => $q->where('competition_id', $competition->id))->count(),
            'pending' => CompetitionSubmission::whereHas('participant', fn($q) => $q->where('competition_id', $competition->id))->where('status', 'submitted')->count(),
            'reviewed' => CompetitionSubmission::whereHas('participant', fn($q) => $q->where('competition_id', $competition->id))->where('status', 'reviewed')->count(),
        ];

        return view('admin.competitions.submissions.index', compact('competition', 'submissions', 'stats'));
    }

    /**
     * Show submission details.
     */
    public function show(Competition $competition, CompetitionSubmission $submission): View
    {
        $submission->load([
            'participant.teacherClub.user',
            'participant.teacherClub.school',
            'participant.participantStudents.student',
            'attachments',
            'judge',
        ]);

        return view('admin.competitions.submissions.show', compact('competition', 'submission'));
    }

    /**
     * Judge/review a submission.
     */
    public function judge(Request $request, Competition $competition, CompetitionSubmission $submission): RedirectResponse
    {
        $validated = $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
            'judge_feedback' => ['nullable', 'string', 'max:5000'],
        ]);

        $submission->update([
            'score' => $validated['score'],
            'judge_feedback' => $validated['judge_feedback'],
            'judged_by' => auth()->id(),
            'judged_at' => now(),
            'status' => 'reviewed',
        ]);

        // Update participant's final score (average of all submissions or use latest)
        $participant = $submission->participant;
        $avgScore = $participant->submissions()->whereNotNull('score')->avg('score');
        $participant->update(['final_score' => $avgScore]);

        // Update ranks
        $this->updateRanks($competition);

        return redirect()->back()->with('status', 'Submission scored successfully.');
    }

    /**
     * Request revision for a submission.
     */
    public function requestRevision(Request $request, Competition $competition, CompetitionSubmission $submission): RedirectResponse
    {
        $validated = $request->validate([
            'judge_feedback' => ['required', 'string', 'max:5000'],
        ]);

        $submission->update([
            'judge_feedback' => $validated['judge_feedback'],
            'judged_by' => auth()->id(),
            'judged_at' => now(),
            'status' => 'revision_requested',
        ]);

        return redirect()->back()->with('status', 'Revision requested. Participant will be notified.');
    }

    /**
     * Mark submission as under review.
     */
    public function markUnderReview(Competition $competition, CompetitionSubmission $submission): RedirectResponse
    {
        $submission->update(['status' => 'under_review']);

        return redirect()->back()->with('status', 'Submission marked as under review.');
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
     * Download attachment.
     */
    public function downloadAttachment(Competition $competition, CompetitionSubmission $submission, $attachmentId)
    {
        $attachment = $submission->attachments()->findOrFail($attachmentId);

        return response()->download(
            storage_path('app/public/' . $attachment->file_path),
            $attachment->original_filename
        );
    }
}

