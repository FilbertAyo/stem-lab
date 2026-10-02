<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\District;
use App\Models\Region;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\TeacherClub;
use App\Models\User;
use App\Services\CompetitionEligibilityPoolService;
use App\Services\CompetitionParticipantRegistrationService;
use App\Services\CompetitionRegistrationSchoolSyncService;
use App\Services\StudentAccountProvisionService;
use App\Services\StudentCsvBulkImportService;
use App\Services\TeacherClubSharedContextService;
use App\Support\SchoolLocation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
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
        $districts = District::query()->orderBy('name')->get(['id', 'name', 'region_id']);
        $districtsByRegion = District::groupedByRegionId();
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
            'district_id' => ['nullable', 'exists:districts,id'],
        ]);

        $schools = ($validated['scope'] === 'all'
            ? School::query()
            : $this->schoolsIndexQuery($request))
            ->with(['regionModel', 'districtModel'])
            ->withCount(['teacherClubs', 'students'])
            ->reviewQueueOrder()
            ->get();

        $scopeLabel = $validated['scope'] === 'all' ? 'all' : 'filtered';
        $filterSummary = $this->buildSchoolExportFilterSummary($request, $validated['scope']);
        $filename = 'schools_'.$scopeLabel.'_'.now()->format('Y-m-d').'.pdf';

        $pdf = Pdf::loadView('admin.downloads.schools-list', [
            'schools' => $schools,
            'title' => 'Schools',
            'generatedAt' => now(),
            'filterSummary' => $filterSummary,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    /**
     * One school profile: details, map, each club's teachers and students, and competitions.
     */
    public function exportProfile(School $school): Response
    {
        $school->load([
            'regionModel',
            'districtModel',
            'teacherClubs.user',
            'teacherClubs.club',
            'teacherClubs.students.schoolClass',
            'competitionRegistrations.competition',
        ]);

        $clubGroups = $school->teacherClubs
            ->groupBy('club_id')
            ->map(function ($memberships) {
                $club = $memberships->first()?->club;
                $leader = $memberships->firstWhere('is_leader', true) ?? $memberships->sortBy('id')->first();
                $day = $leader?->club_day
                    ?? $memberships->first(fn ($membership) => filled($membership->club_day))?->club_day;

                $students = $memberships
                    ->flatMap(fn (TeacherClub $membership) => $membership->students)
                    ->unique('id')
                    ->sortBy(fn (Student $student) => mb_strtolower($student->name))
                    ->values();

                return [
                    'name' => $club?->name ?? 'Club',
                    'day' => filled($day) ? ucfirst((string) $day) : '—',
                    'leader' => $leader?->user?->name ?? '—',
                    'memberships' => $memberships->sortBy(fn (TeacherClub $membership) => mb_strtolower((string) $membership->user?->name))->values(),
                    'students' => $students,
                ];
            })
            ->sortBy(fn (array $group) => mb_strtolower($group['name']))
            ->values();

        $teacherCount = $school->teacherClubs->pluck('user_id')->unique()->count();
        $studentCount = $clubGroups->sum(fn (array $group) => $group['students']->count());
        $registrations = $school->competitionRegistrations
            ->sortByDesc(fn ($registration) => $registration->submitted_at ?? $registration->created_at)
            ->values();

        $slug = Str::slug($school->name) ?: 'school';
        $filename = 'school_profile_'.$slug.'_'.now()->format('Y-m-d').'.pdf';

        $pdf = Pdf::loadView('admin.downloads.school-profile', [
            'school' => $school,
            'clubGroups' => $clubGroups,
            'registrations' => $registrations,
            'teacherCount' => $teacherCount,
            'studentCount' => $studentCount,
            'mapImage' => $this->schoolMapImage($school),
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    public function show(Request $request, School $school): View
    {
        $school->load([
            'regionModel',
            'districtModel',
            'createdBy',
            'updatedBy',
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
        })->with(['schoolClass', 'teacherClub.club', 'user'])->get()->unique('id')->values();

        // One option per club: students always attach to the school club leader (shared roster).
        $bulkUploadTeacherClubs = $school->teacherClubs()
            ->approved()
            ->where('is_leader', true)
            ->with(['user:id,name', 'club:id,name'])
            ->orderBy('club_id')
            ->get(['id', 'user_id', 'club_id', 'school_id', 'is_leader']);

        $clubs = Club::query()->active()->ordered()->get(['id', 'name']);

        $sharedClubDaysByClubId = $school->teacherClubs
            ->where('status', 'approved')
            ->groupBy('club_id')
            ->map(function ($memberships) {
                $leader = $memberships->firstWhere('is_leader', true) ?? $memberships->sortBy('id')->first();

                return $leader?->club_day
                    ?? $memberships->first(fn ($m) => filled($m->club_day))?->club_day;
            });

        $unassignedTeachers = User::query()
            ->where('role', 'teacher')
            ->whereDoesntHave('teacherClubs')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone']);

        $schoolClasses = SchoolClass::query()->active()->ordered()->get(['id', 'name']);

        $requestedTab = $request->query('tab');
        $activeTab = is_string($requestedTab) ? $requestedTab : '';
        if (! in_array($activeTab, ['teachers', 'students'], true)) {
            $activeTab = 'students';
        }

        return view('admin.schools.show', compact(
            'school',
            'teachers',
            'students',
            'bulkUploadTeacherClubs',
            'clubs',
            'unassignedTeachers',
            'sharedClubDaysByClubId',
            'schoolClasses',
            'activeTab',
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
            'district_id' => ['nullable', 'exists:districts,id'],
            'ward' => ['nullable', 'string', 'max:255'],
            'region_id' => ['nullable', 'exists:regions,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated = SchoolLocation::normalizeSchoolAttributes($validated);
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

        $query->update([
            'admin_reviewed_at' => now(),
            'updated_by' => $request->user()?->id,
        ]);

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
            'district_id' => ['nullable', 'exists:districts,id'],
            'ward' => ['nullable', 'string', 'max:255'],
            'region_id' => ['nullable', 'exists:regions,id'],
            'is_active' => ['required', 'boolean'],
        ]);

        $validated = SchoolLocation::normalizeSchoolAttributes($validated);

        $school->update($validated);

        app(CompetitionRegistrationSchoolSyncService::class)->syncFromSchool($school->fresh(['districtModel']));

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
            return $this->redirectToSchoolShow($school, $hasTeachers ? 'teachers' : 'students')
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
            return $this->redirectToSchoolShow($school, 'teachers')
                ->with('error', "Cannot remove {$teacherName} from {$clubName} because they have {$studentsCount} registered student(s) in this club.");
        }

        // Delete the teacher-club relationship
        $teacherClub->delete();

        return $this->redirectToSchoolShow($school, 'teachers')
            ->with('status', "{$teacherName} has been removed from {$clubName} successfully.");
    }

    public function updateClubDay(
        Request $request,
        School $school,
        TeacherClub $teacherClub,
        TeacherClubSharedContextService $sharedContext,
    ): RedirectResponse {
        $belongsToSchool = (int) $teacherClub->school_id === (int) $school->id;

        if (! $belongsToSchool) {
            abort(404);
        }

        $validated = $request->validate([
            'club_day' => ['nullable', Rule::in(['monday', 'tuesday', 'wednesday', 'thursday', 'friday'])],
        ]);

        $sharedContext->syncClubDay($teacherClub, $validated['club_day'] ?? null);

        $clubName = $teacherClub->club?->name ?? 'club';

        return $this->redirectToSchoolShow($school, 'teachers')
            ->with('status', "Dedicated club day updated for all teachers in {$clubName} at this school.");
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

        return $this->redirectToSchoolShow($school, 'students')
            ->with('status', "{$studentName} removed successfully.");
    }

    public function bulkRemoveStudents(
        Request $request,
        School $school,
        CompetitionEligibilityPoolService $eligibilityPool,
    ): RedirectResponse {
        $validated = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
        ]);

        $students = Student::query()
            ->whereIn('id', $validated['student_ids'])
            ->whereHas('teacherClub', function ($query) use ($school): void {
                $query->where('school_id', $school->id);
            })
            ->with('teacherClub')
            ->get();

        if ($students->isEmpty()) {
            return $this->redirectToSchoolShow($school, 'students')
                ->with('error', 'No matching students were found for this school.');
        }

        $teacherClubIds = $students->pluck('teacher_club_id')->unique()->filter();
        $deleted = $students->count();

        Student::query()->whereIn('id', $students->pluck('id'))->delete();

        $teacherClubIds->each(function ($teacherClubId) use ($eligibilityPool): void {
            $teacherClub = TeacherClub::query()->find($teacherClubId);
            if ($teacherClub) {
                $eligibilityPool->refreshOpenRegistrationSnapshots($teacherClub);
            }
        });

        return $this->redirectToSchoolShow($school, 'students')
            ->with('status', "{$deleted} student(s) removed successfully.");
    }

    public function provisionStudent(
        School $school,
        Student $student,
        StudentAccountProvisionService $provision,
    ): RedirectResponse {
        $this->assertStudentBelongsToSchool($school, $student);

        $credential = $provision->provision($student);

        return $this->redirectToSchoolShow($school, 'students')
            ->with('status', 'Login created for '.$credential->student->name.'. Write down the PIN; it will not be shown again.')
            ->with('provisioned_credentials', $provision->sessionPayload([$credential]));
    }

    public function resetStudentPin(
        School $school,
        Student $student,
        StudentAccountProvisionService $provision,
    ): RedirectResponse {
        $this->assertStudentBelongsToSchool($school, $student);

        $credential = $provision->resetPin($student);

        return $this->redirectToSchoolShow($school, 'students')
            ->with('status', 'PIN reset for '.$credential->student->name.'. Write down the new PIN; it will not be shown again.')
            ->with('provisioned_credentials', $provision->sessionPayload([$credential]));
    }

    public function bulkProvisionStudents(
        Request $request,
        School $school,
        StudentAccountProvisionService $provision,
    ): RedirectResponse {
        $validated = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
        ]);

        $students = Student::query()
            ->whereIn('id', $validated['student_ids'])
            ->whereHas('teacherClub', function ($query) use ($school): void {
                $query->where('school_id', $school->id);
            })
            ->get();

        if ($students->isEmpty()) {
            return $this->redirectToSchoolShow($school, 'students')
                ->with('error', 'No matching students were found for this school.');
        }

        $result = $provision->provisionMany($students);

        if ($result['credentials'] === []) {
            return $this->redirectToSchoolShow($school, 'students')
                ->with('error', 'No logins were created. Selected students already have accounts.');
        }

        $created = count($result['credentials']);
        $message = "Created logins for {$created} student(s). Write down the PINs; they will not be shown again.";
        if ($result['skipped'] > 0) {
            $message .= " {$result['skipped']} already had an account.";
        }

        return $this->redirectToSchoolShow($school, 'students')
            ->with('status', $message)
            ->with('provisioned_credentials', $provision->sessionPayload($result['credentials']));
    }

    public function storeTeacher(
        Request $request,
        School $school,
        CompetitionParticipantRegistrationService $participantRegistrationService,
        TeacherClubSharedContextService $sharedContext,
    ): RedirectResponse {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'club_id' => ['required', 'exists:clubs,id'],
            'club_day' => ['nullable', Rule::in(['monday', 'tuesday', 'wednesday', 'thursday', 'friday'])],
        ]);

        $user = User::query()->findOrFail((int) $validated['user_id']);

        if ($user->role !== 'teacher') {
            return $this->redirectToSchoolShow($school, 'teachers')
                ->withErrors(['user_id' => 'Only teacher accounts can be assigned to a school.'])
                ->withInput();
        }

        if ($user->teacherClubs()->exists()) {
            return $this->redirectToSchoolShow($school, 'teachers')
                ->withErrors(['user_id' => 'This teacher is already assigned to a school.'])
                ->withInput();
        }

        $club = Club::query()->findOrFail((int) $validated['club_id']);

        if ($school->teacherClubs()->where('club_id', $club->id)->where('user_id', $user->id)->exists()) {
            return $this->redirectToSchoolShow($school, 'teachers')
                ->withErrors(['user_id' => 'This teacher is already assigned to the selected club at this school.'])
                ->withInput();
        }

        $hasApprovedTeacherFromSchool = $school->teacherClubs()
            ->where('club_id', $club->id)
            ->approved()
            ->exists();

        $clubDay = $validated['club_day']
            ?? $sharedContext->existingClubDayForSchoolClub($club->id, $school->id);

        $teacherClub = TeacherClub::create([
            'user_id' => $user->id,
            'club_id' => $club->id,
            'school_id' => $school->id,
            'club_day' => $clubDay,
            'status' => 'approved',
            'is_leader' => ! $hasApprovedTeacherFromSchool,
        ]);

        if ($hasApprovedTeacherFromSchool && filled($clubDay)) {
            $sharedContext->syncClubDay($teacherClub, $clubDay);
        }

        $participantRegistrationService->ensureForClubMembership($teacherClub);

        return $this->redirectToSchoolShow($school, 'teachers')
            ->with('success', "{$user->name} has been added to {$club->name} at {$school->name}.");
    }

    public function storeStudent(
        Request $request,
        School $school,
        StudentCsvBulkImportService $csvImport,
        TeacherClubSharedContextService $sharedContext,
        CompetitionEligibilityPoolService $eligibilityPool,
    ): RedirectResponse {
        $validated = $request->validate([
            'teacher_club_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'string', 'max:32'],
            'gender' => ['required', 'string', Rule::in(['male', 'female', 'other'])],
            'school_class_id' => ['required', 'exists:school_classes,id'],
        ]);

        $teacherClub = $school->teacherClubs()
            ->approved()
            ->where('id', (int) $validated['teacher_club_id'])
            ->first();

        if (! $teacherClub) {
            return $this->redirectToSchoolShow($school, 'students')
                ->withErrors(['teacher_club_id' => 'Select a valid approved teacher/club for this school.'])
                ->withInput();
        }

        $parsedDob = $csvImport->parseDateOfBirth($validated['date_of_birth']);
        if ($parsedDob === null) {
            return $this->redirectToSchoolShow($school, 'students')
                ->withErrors(['date_of_birth' => 'Invalid date of birth. Use DD-MM-YYYY, DD/MM/YYYY, or YYYY-MM-DD.'])
                ->withInput();
        }

        if ($parsedDob->isFuture()) {
            return $this->redirectToSchoolShow($school, 'students')
                ->withErrors(['date_of_birth' => 'Date of birth cannot be in the future.'])
                ->withInput();
        }

        $age = $parsedDob->age;
        if ($age < 1 || $age > 150) {
            return $this->redirectToSchoolShow($school, 'students')
                ->withErrors(['date_of_birth' => 'Date of birth must result in age between 1 and 150.'])
                ->withInput();
        }

        $rosterOwner = $sharedContext->resolveRosterOwner($teacherClub);

        Student::create([
            'teacher_club_id' => $rosterOwner->id,
            'name' => $validated['name'],
            'date_of_birth' => $parsedDob->format('Y-m-d'),
            'age' => $age,
            'gender' => $validated['gender'],
            'school_class_id' => $validated['school_class_id'],
        ]);

        $eligibilityPool->refreshOpenRegistrationSnapshots($rosterOwner);

        $clubName = $rosterOwner->club?->name ?? 'club';

        return $this->redirectToSchoolShow($school, 'students')
            ->with('status', "{$validated['name']} has been added to {$clubName} at {$school->name}.");
    }

    public function bulkImportStudents(
        Request $request,
        School $school,
        StudentCsvBulkImportService $csvImport,
        TeacherClubSharedContextService $sharedContext,
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
            return $this->redirectToSchoolShow($school, 'students')
                ->withErrors(['teacher_club_id' => 'Select a valid approved teacher/club for this school.'])
                ->withInput();
        }

        $rosterOwner = $sharedContext->resolveRosterOwner($teacherClub);
        $result = $csvImport->import($rosterOwner, $request->file('csv_file'));

        if ($result['errors'] !== []) {
            return $this->redirectToSchoolShow($school, 'students')
                ->with('error', $csvImport->formatErrorsForFlash($result['errors']))
                ->withInput();
        }

        $clubName = $rosterOwner->club?->name ?? 'club';
        $leaderName = $rosterOwner->user?->name ?? 'the club leader';

        return $this->redirectToSchoolShow($school, 'students')
            ->with(
                'status',
                "Successfully imported {$result['success_count']} student(s) to {$clubName} (shared roster under {$leaderName})."
            );
    }

    private function redirectToSchoolShow(School $school, string $tab): RedirectResponse
    {
        return redirect()->route('admin.schools.show', [
            'school' => $school,
            'tab' => $tab,
        ]);
    }

    private function assertStudentBelongsToSchool(School $school, Student $student): void
    {
        $student->loadMissing('teacherClub');

        $belongsToSchool = (int) $student->teacherClub?->school_id === (int) $school->id;

        if (! $belongsToSchool) {
            abort(404);
        }
    }

    private function schoolMapImage(School $school): ?string
    {
        if ($school->latitude === null || $school->longitude === null) {
            return null;
        }

        $lat = $school->latitude;
        $lng = $school->longitude;
        $url = 'https://staticmap.openstreetmap.de/staticmap.php'
            .'?center='.$lat.','.$lng
            .'&zoom=15&size=640x280&maptype=mapnik'
            .'&markers='.$lat.','.$lng.',red-pushpin';

        try {
            $response = Http::timeout(8)->get($url);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->successful() || $response->body() === '') {
            return null;
        }

        $mime = strtolower(trim(strtok((string) $response->header('Content-Type'), ';') ?: ''));
        if (! str_starts_with($mime, 'image/')) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($response->body());
    }

    private function schoolsIndexQuery(Request $request): Builder
    {
        $query = School::query()
            ->with(['regionModel', 'districtModel', 'createdBy', 'updatedBy'])
            ->withCount(['teacherClubs', 'students']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('district', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhereHas('regionModel', function ($regionQuery) use ($search): void {
                        $regionQuery->where('name', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('districtModel', function ($districtQuery) use ($search): void {
                        $districtQuery->where('name', 'like', '%'.$search.'%');
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

        if ($request->filled('district_id')) {
            $query->where('district_id', $request->integer('district_id'));
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

        if ($request->filled('district_id')) {
            $district = District::query()->find($request->integer('district_id'));
            $parts[] = 'district: '.($district?->name ?? $request->input('district_id'));
        }

        return implode(' · ', $parts);
    }
}
