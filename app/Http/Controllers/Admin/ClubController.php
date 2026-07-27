<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\TeacherClub;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClubController extends Controller
{
    public function index(): View
    {
        $clubs = Club::ordered()->get();

        return view('admin.clubs.index', compact('clubs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:clubs,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('clubs', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        unset($validated['image']);

        Club::create($validated);

        // Clear clubs cache
        Cache::forget('active_clubs');

        return redirect()->route('admin.clubs.index')->with('status', 'Club created successfully.');
    }

    public function update(Request $request, Club $club): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('clubs', 'name')->ignore($club->id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            if ($club->image_path && Storage::disk('public')->exists($club->image_path)) {
                Storage::disk('public')->delete($club->image_path);
            }

            $validated['image_path'] = $request->file('image')->store('clubs', 'public');
        }

        unset($validated['image']);

        $club->update($validated);

        // Clear clubs cache
        Cache::forget('active_clubs');

        return redirect()->route('admin.clubs.index')->with('status', 'Club updated successfully.');
    }

    public function toggleStatus(Club $club): RedirectResponse
    {
        $club->update([
            'is_active' => ! $club->is_active,
        ]);

        // Clear clubs cache
        Cache::forget('active_clubs');

        $message = $club->is_active ? 'Club enabled successfully.' : 'Club disabled successfully.';

        return redirect()->route('admin.clubs.index')->with('status', $message);
    }

    /**
     * Display the specified club with members and pending requests.
     */
    public function show(Request $request, Club $club): View
    {
        $leadersSearch = trim((string) $request->input('leaders_search', ''));
        $membersSearch = trim((string) $request->input('members_search', ''));
        $requestsSearch = trim((string) $request->input('requests_search', ''));

        // Get all leaders (one per school)
        $leadersQuery = $club->teacherClubs()
            ->approved()
            ->leaders()
            ->with(['user', 'school'])
            ->orderByDesc('created_at');
        $this->applyTeacherClubSearch($leadersQuery, $leadersSearch);

        $leaders = $leadersQuery
            ->paginate(10, ['*'], 'leaders_page')
            ->withQueryString();

        // Get approved members
        $approvedMembersQuery = $club->teacherClubs()
            ->approved()
            ->with(['user', 'school'])
            ->orderByDesc('created_at');
        $this->applyTeacherClubSearch($approvedMembersQuery, $membersSearch);

        $approvedMembers = $approvedMembersQuery
            ->paginate(10, ['*'], 'members_page')
            ->withQueryString();

        // Get pending requests
        $pendingRequestsQuery = $club->pendingRequests()
            ->with(['user', 'school'])
            ->orderByDesc('created_at');
        $this->applyTeacherClubSearch($pendingRequestsQuery, $requestsSearch);

        $pendingRequests = $pendingRequestsQuery
            ->paginate(10, ['*'], 'requests_page')
            ->withQueryString();

        $leaders->appends(['tab' => 'leaders']);
        $approvedMembers->appends(['tab' => 'members']);
        $pendingRequests->appends(['tab' => 'requests']);

        // Get all approved members grouped by school for transfer leadership modal.
        $transferMembers = $club->teacherClubs()
            ->approved()
            ->with(['user', 'school'])
            ->get();

        $membersBySchoolJson = $transferMembers->groupBy('school_id')->map(function ($members, $schoolId) {
            return [
                'school_id' => (string) $schoolId,
                'members' => $members->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->user->name,
                        'email' => $member->user->email,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        $activeTab = match (true) {
            $request->input('tab') === 'requests' || $request->has('requests_page') => 'requests',
            $request->input('tab') === 'members' || $request->has('members_page') => 'members',
            $request->input('tab') === 'leaders' || $request->has('leaders_page') => 'leaders',
            $request->filled('requests_search') => 'requests',
            $request->filled('members_search') => 'members',
            default => 'leaders',
        };

        if (! in_array($activeTab, ['leaders', 'members', 'requests'], true)) {
            $activeTab = 'leaders';
        }

        return view('admin.clubs.show', compact(
            'club',
            'approvedMembers',
            'pendingRequests',
            'leaders',
            'membersBySchoolJson',
            'activeTab',
            'leadersSearch',
            'membersSearch',
            'requestsSearch',
        ));
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\TeacherClub>|\Illuminate\Database\Eloquent\Relations\Relation  $query
     */
    private function applyTeacherClubSearch($query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $query->where(function ($q) use ($search): void {
            $q->where('club_code', 'like', '%'.$search.'%')
                ->orWhereHas('user', function ($userQuery) use ($search): void {
                    $userQuery->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                })
                ->orWhereHas('school', function ($schoolQuery) use ($search): void {
                    $schoolQuery->where('name', 'like', '%'.$search.'%');
                });
        });
    }

    /**
     * Download approved club members as PDF.
     */
    public function exportLeaders(Request $request, Club $club): Response
    {
        $search = trim((string) $request->input('leaders_search', ''));

        $rowsQuery = $club->teacherClubs()
            ->approved()
            ->leaders()
            ->with(['user', 'school'])
            ->orderByDesc('created_at');
        $this->applyTeacherClubSearch($rowsQuery, $search);

        return $this->downloadClubTeacherClubsPdf(
            $club,
            $rowsQuery->get(),
            $club->name.' - School Leaders',
            Str::slug($club->name).'_school_leaders_'.now()->format('Y-m-d').'.pdf',
            $search !== '' ? 'No school leaders match your search.' : 'No school leaders found.',
            $search !== '' ? 'Club: '.$club->name.' · search: '.$search : 'Club: '.$club->name,
        );
    }

    public function exportApprovedMembers(Request $request, Club $club): Response
    {
        $search = trim((string) $request->input('members_search', ''));

        $rowsQuery = $club->teacherClubs()
            ->approved()
            ->with(['user', 'school'])
            ->orderByDesc('created_at');
        $this->applyTeacherClubSearch($rowsQuery, $search);

        return $this->downloadClubTeacherClubsPdf(
            $club,
            $rowsQuery->get(),
            $club->name.' - Approved Members',
            Str::slug($club->name).'_approved_members_'.now()->format('Y-m-d').'.pdf',
            $search !== '' ? 'No approved members match your search.' : 'No approved members found.',
            $search !== '' ? 'Club: '.$club->name.' · search: '.$search : 'Club: '.$club->name,
        );
    }

    public function exportPendingRequests(Request $request, Club $club): Response
    {
        $search = trim((string) $request->input('requests_search', ''));

        $rowsQuery = $club->pendingRequests()
            ->with(['user', 'school'])
            ->orderByDesc('created_at');
        $this->applyTeacherClubSearch($rowsQuery, $search);

        return $this->downloadClubTeacherClubsPdf(
            $club,
            $rowsQuery->get(),
            $club->name.' - Pending Requests',
            Str::slug($club->name).'_pending_requests_'.now()->format('Y-m-d').'.pdf',
            $search !== '' ? 'No pending requests match your search.' : 'No pending requests found.',
            $search !== '' ? 'Club: '.$club->name.' · search: '.$search : 'Club: '.$club->name,
        );
    }

    private function downloadClubTeacherClubsPdf(
        Club $club,
        $rows,
        string $title,
        string $filename,
        string $emptyMessage,
        ?string $filterSummary = null,
    ): Response {
        $pdf = Pdf::loadView('admin.downloads.club-teacher-clubs-list', [
            'rows' => $rows,
            'title' => $title,
            'generatedAt' => now(),
            'filterSummary' => $filterSummary ?? ('Club: '.$club->name),
            'emptyMessage' => $emptyMessage,
        ])->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    /**
     * Transfer leadership to another approved teacher (school-specific).
     */
    public function transferLeadership(Request $request, Club $club): RedirectResponse
    {
        $validated = $request->validate([
            'teacher_club_id' => ['required', 'exists:teacher_clubs,id'],
            'school_id' => ['required', 'exists:schools,id'],
        ]);

        $newLeaderTeacherClub = TeacherClub::findOrFail($validated['teacher_club_id']);

        // Verify the teacher belongs to this club
        if ($newLeaderTeacherClub->club_id !== $club->id) {
            return redirect()->route('admin.clubs.show', $club)
                ->with('error', 'The selected teacher does not belong to this club.');
        }

        // Verify the teacher belongs to the specified school
        if ($newLeaderTeacherClub->school_id != $validated['school_id']) {
            return redirect()->route('admin.clubs.show', $club)
                ->with('error', 'The selected teacher does not belong to the specified school.');
        }

        // Verify the teacher is approved
        if ($newLeaderTeacherClub->status !== 'approved') {
            return redirect()->route('admin.clubs.show', $club)
                ->with('error', 'Only approved members can become school leaders.');
        }

        // Remove leadership from current leader for this school (if exists)
        TeacherClub::where('club_id', $club->id)
            ->where('school_id', $validated['school_id'])
            ->where('is_leader', true)
            ->update(['is_leader' => false]);

        // Set new leader
        $newLeaderTeacherClub->update(['is_leader' => true]);

        return redirect()->route('admin.clubs.show', ['club' => $club, 'tab' => 'leaders'])
            ->with('status', "Leadership for the school has been transferred to {$newLeaderTeacherClub->user->name}.");
    }

    /**
     * Approve a pending join request (admin).
     */
    public function approveRequest(TeacherClub $teacherClub): RedirectResponse
    {
        // Verify that the request is pending
        if ($teacherClub->status !== 'pending') {
            return redirect()->route('admin.clubs.show', $teacherClub->club)
                ->with('error', 'This request has already been processed.');
        }

        $teacherClub->approve();

        return redirect()->route('admin.clubs.show', ['club' => $teacherClub->club, 'tab' => 'requests'])
            ->with('status', "Successfully approved {$teacherClub->user->name}'s request to join {$teacherClub->club->name}.");
    }

    /**
     * Reject a pending join request (admin).
     */
    public function rejectRequest(TeacherClub $teacherClub): RedirectResponse
    {
        // Verify that the request is pending
        if ($teacherClub->status !== 'pending') {
            return redirect()->route('admin.clubs.show', $teacherClub->club)
                ->with('error', 'This request has already been processed.');
        }

        $teacherClub->reject();

        return redirect()->route('admin.clubs.show', ['club' => $teacherClub->club, 'tab' => 'requests'])
            ->with('status', "Successfully rejected {$teacherClub->user->name}'s request to join {$teacherClub->club->name}.");
    }
}

