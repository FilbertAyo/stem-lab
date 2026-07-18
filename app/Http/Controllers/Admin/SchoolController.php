<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\District;
use App\Models\Region;
use App\Models\School;
use App\Models\Student;
use App\Models\TeacherClub;
use App\Models\User;
use App\Services\CompetitionEligibilityPoolService;
use App\Services\CompetitionParticipantRegistrationService;
use App\Services\StudentCsvBulkImportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SchoolController extends Controller
{
    public function index(Request $request): View
    {
        $allowedPerPage = [10, 15, 25, 50, 100];
        $perPage = (int) $request->query('per_page', 15);

        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 15;
        }

        $query = $this->schoolsIndexQuery($request);
        $schools = $query->reviewQueueOrder()->paginate($perPage)->withQueryString();

        $regions = Region::query()->orderBy('name')->get(['id', 'name']);
        $districts = School::whereNotNull('district')->distinct()->orderBy('district')->pluck('district');
        $districtsByRegion = District::groupedNamesByRegionId();
        $pendingReviewCount = School::query()->needsReview()->count();

        return view('admin.schools.index', compact('schools', 'regions', 'districts', 'districtsByRegion', 'pendingReviewCount'));
    }

    /**
     * Download schools list as PDF.
     */
    public function export(Request $request): Response
    {
        $validated = $request->validate([
            'scope' => ['required', 'in:range,all'],
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,inactive'],
            'review' => ['nullable', 'in:pending,reviewed'],
            'region_id' => ['nullable', 'exists:regions,id'],
            'district' => ['nullable', 'string', 'max:255'],
        ]);

        $schools = ($validated['scope'] === 'all'
            ? School::query()
            : $this->schoolsIndexQuery($request))
            ->with('regionModel')
            ->withCount('teacherClubs')
            ->reviewQueueOrder()
            ->get();

        $scopeLabel = $validated['scope'] === 'all' ? 'all' : 'filtered';
        $filterSummary = $this->buildSchoolExportFilterSummary($request, $validated['scope']);
        $filename = 'schools_'.$scopeLabel.'_'.now()->format('Y-m-d').'.pdf';

        $pdf = Pdf::loadView('admin.schools.schools-list-pdf', [
            'schools' => $schools,
            'title' => 'Schools',
            'generatedAt' => now(),
            'filterSummary' => $filterSummary,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($filename);
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
        $teachers = ! empty($teacherIds)
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

        $clubs = Club::query()->active()->ordered()->get(['id', 'name']);

        $unassignedTeachers = User::query()
            ->where('role', 'teacher')
            ->whereDoesntHave('teacherClubs')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone']);

        return view('admin.schools.show', compact(
            'school',
            'teachers',
            'students',
            'bulkUploadTeacherClubs',
            'clubs',
            'unassignedTeachers',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:schools,name'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'ward' => ['nullable', 'string', 'max:255'],
            'region_id' => ['nullable', 'exists:regions,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['admin_reviewed_at'] = now();

        School::create($validated);

        return redirect()->route('admin.schools.index')->with('status', 'School created successfully.');
    }

    public function markReviewed(School $school): RedirectResponse
    {
        if ($school->admin_reviewed_at !== null) {
            return redirect()
                ->back()
                ->with('status', "{$school->name} is already marked as reviewed.");
        }

        $school->update(['admin_reviewed_at' => now()]);

        return redirect()
            ->back()
            ->with('status', "{$school->name} marked as reviewed.");
    }

    public function markAllReviewed(Request $request): RedirectResponse
    {
        $query = $this->schoolsIndexQuery($request)->needsReview();
        $count = (clone $query)->count();

        if ($count === 0) {
            return redirect()
                ->back()
                ->with('status', 'No schools pending review match your filters.');
        }

        $query->update(['admin_reviewed_at' => now()]);

        return redirect()
            ->back()
            ->with('status', "Marked {$count} school(s) as reviewed.");
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
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
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
        $hasTeachers = $school->teacherClubs()->exists();
        $hasStudents = $school->students()->exists();

        if ($hasTeachers || $hasStudents) {
            return redirect()
                ->route('admin.schools.show', $school)
                ->with('error', 'Cannot delete school while teachers or students are linked. Remove all linked teachers and students first.');
        }

        $schoolName = $school->name;
        $school->delete();

        return redirect()
            ->route('admin.schools.index')
            ->with('success', "School {$schoolName} deleted successfully.");
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

    public function removeStudent(
        School $school,
        Student $student,
        CompetitionEligibilityPoolService $eligibilityPool,
    ): RedirectResponse {
        $belongsToSchool = (int) $student->teacherClub?->school_id === (int) $school->id;

        if (! $belongsToSchool) {
            abort(404);
        }

        $teacherClub = $student->teacherClub;
        $studentName = $student->name;
        $student->delete();

        if ($teacherClub) {
            $eligibilityPool->refreshOpenRegistrationSnapshots($teacherClub);
        }

        return redirect()
            ->route('admin.schools.show', $school)
            ->with('status', "{$studentName} removed successfully.");
    }

    public function storeTeacher(
        Request $request,
        School $school,
        CompetitionParticipantRegistrationService $participantRegistrationService,
    ): RedirectResponse {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'club_id' => ['required', 'exists:clubs,id'],
            'club_day' => ['nullable', Rule::in(['monday', 'tuesday', 'wednesday', 'thursday', 'friday'])],
        ]);

        $user = User::query()->findOrFail((int) $validated['user_id']);

        if ($user->role !== 'teacher') {
            return redirect()
                ->route('admin.schools.show', $school)
                ->withErrors(['user_id' => 'Only teacher accounts can be assigned to a school.'])
                ->withInput();
        }

        if ($user->teacherClubs()->exists()) {
            return redirect()
                ->route('admin.schools.show', $school)
                ->withErrors(['user_id' => 'This teacher is already assigned to a school.'])
                ->withInput();
        }

        $club = Club::query()->findOrFail((int) $validated['club_id']);

        if ($school->teacherClubs()->where('club_id', $club->id)->where('user_id', $user->id)->exists()) {
            return redirect()
                ->route('admin.schools.show', $school)
                ->withErrors(['user_id' => 'This teacher is already assigned to the selected club at this school.'])
                ->withInput();
        }

        $hasApprovedTeacherFromSchool = $school->teacherClubs()
            ->where('club_id', $club->id)
            ->approved()
            ->exists();

        $teacherClub = TeacherClub::create([
            'user_id' => $user->id,
            'club_id' => $club->id,
            'school_id' => $school->id,
            'club_day' => $validated['club_day'] ?? null,
            'status' => 'approved',
            'is_leader' => ! $hasApprovedTeacherFromSchool,
        ]);

        $participantRegistrationService->ensureForClubMembership($teacherClub);

        return redirect()
            ->route('admin.schools.show', $school)
            ->with('success', "{$user->name} has been added to {$club->name} at {$school->name}.");
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

    private function schoolsIndexQuery(Request $request): Builder
    {
        $query = School::query()
            ->with('regionModel')
            ->withCount('teacherClubs');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('district', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhereHas('regionModel', function ($regionQuery) use ($search): void {
                        $regionQuery->where('name', 'like', '%'.$search.'%');
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('review')) {
            if ($request->review === 'pending') {
                $query->needsReview();
            } elseif ($request->review === 'reviewed') {
                $query->reviewed();
            }
        }

        if ($request->filled('region_id')) {
            $query->where('region_id', $request->integer('region_id'));
        }

        if ($request->filled('district')) {
            $query->where('district', $request->district);
        }

        return $query;
    }

    protected function buildSchoolExportFilterSummary(Request $request, string $scope): string
    {
        if ($scope === 'all') {
            return 'All records';
        }

        $parts = ['Filtered list'];

        if ($request->filled('search')) {
            $parts[] = 'search: '.$request->input('search');
        }

        if ($request->filled('status')) {
            $parts[] = 'status: '.$request->input('status');
        }

        if ($request->filled('review')) {
            $parts[] = 'review: '.$request->input('review');
        }

        if ($request->filled('region_id')) {
            $region = Region::query()->find($request->integer('region_id'));
            $parts[] = 'region: '.($region?->name ?? $request->input('region_id'));
        }

        if ($request->filled('district')) {
            $parts[] = 'district: '.$request->input('district');
        }

        return implode(' · ', $parts);
    }
}
