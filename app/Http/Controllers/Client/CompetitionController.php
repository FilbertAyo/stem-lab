<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\CompetitionIdea;
use App\Models\CompetitionRegistration;
use App\Models\CompetitionRegistrationStudent;
use App\Models\CompetitionPrototypeSubmission;
use App\Models\CompetitionPrototypeModification;
use App\Models\CompetitionPhaseEvidenceForm;
use App\Models\District;
use App\Models\Region;
use App\Models\SchoolClass;
use App\Models\TeacherClub;
use App\Services\CompetitionEligibilityPoolService;
use App\Services\CompetitionParticipantRegistrationService;
use App\Support\SchoolLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CompetitionController extends Controller
{
    public function index(CompetitionParticipantRegistrationService $participantRegistrationService): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $highlightCompetitionUlid = session()->pull('onboarding_competition_ulid');

        $teacherClubByClubId = $user->teacherClubs()->with(['user', 'school'])->get()->keyBy('club_id');
        $clubIds = $teacherClubByClubId->keys()->filter()->values()->all();

        $competitions = collect();
        if ($clubIds !== []) {
            $competitions = Competition::active()
                ->published()
                ->whereIn('club_id', $clubIds)
                ->with(['club', 'phases'])
                ->withCount(['registrations', 'ideas'])
                ->orderByDesc('competition_start')
                ->get();
        }

        if ($highlightCompetitionUlid) {
            $extra = Competition::active()
                ->published()
                ->where('ulid', $highlightCompetitionUlid)
                ->with(['club', 'phases'])
                ->withCount(['registrations', 'ideas'])
                ->first();
            if ($extra && ! $competitions->contains(fn (Competition $c): bool => $c->id === $extra->id)) {
                $competitions = $competitions->prepend($extra);
            }
        }

        foreach ($competitions as $competition) {
            $teacherClub = $teacherClubByClubId->get($competition->club_id);
            if ($teacherClub) {
                $participantRegistrationService->ensureDraftRegistration($teacherClub, $competition);
            }
        }

        $registrations = $participantRegistrationService->registrationsForTeacher(
            ...$teacherClubByClubId->values()->all()
        );

        return view('client.back.competitions.index', compact(
            'competitions',
            'registrations',
            'teacherClubByClubId',
            'highlightCompetitionUlid',
        ));
    }

    public function show(
        Competition $competition,
        CompetitionParticipantRegistrationService $participantRegistrationService,
        CompetitionEligibilityPoolService $eligibilityPool,
    ): View {
        $user = Auth::user();
        $teacherClub = $this->resolveTeacherClubForCompetition($user->id, $competition->club_id);
        $registration = null;
        $idea = null;
        $poolStudents = collect();

        if ($teacherClub) {
            $registration = $participantRegistrationService->findForTeacherClub($competition, $teacherClub);
            if ($registration) {
                $registration->load([
                    'representativeStudents.student.schoolClass',
                    'shortlist',
                    'region',
                    'school.regionModel',
                ]);
            }
            $idea = $registration?->idea()->with([
                'scores',
                'shortlist',
                'communityRegion',
                'prototypeSubmission.prototypeShortlist.mentors',
                'prototypeSubmission.modification.modificationShortlist',
                'prototypeModification.modificationShortlist',
            ])->first();
        }

        $competition->load([
            'club',
            'phases',
            'classRequirements.schoolClass',
            'shortlists.idea.registration.school',
        ]);

        $prototypeEvidenceFormUrl = null;
        $prototypePhaseId = $competition->phases->firstWhere('phase_key', 'prototype_development')?->id;
        if ($prototypePhaseId) {
            $prototypeEvidenceFormUrl = CompetitionPhaseEvidenceForm::query()
                ->where('competition_phase_id', $prototypePhaseId)
                ->value('google_form_url');
        }

        $prototypeModificationEvidenceFormUrl = null;
        $prototypeModificationPhaseId = $competition->phases->firstWhere('phase_key', 'prototype_modification')?->id;
        if ($prototypeModificationPhaseId) {
            $prototypeModificationEvidenceFormUrl = CompetitionPhaseEvidenceForm::query()
                ->where('competition_phase_id', $prototypeModificationPhaseId)
                ->value('google_form_url');
        }

        if ($teacherClub) {
            $poolStudents = $eligibilityPool->computePool($competition, $teacherClub)['students'];
        }

        $regions = Region::query()->orderBy('name')->get(['id', 'name']);

        return view('client.back.competitions.show', compact(
            'competition',
            'teacherClub',
            'registration',
            'idea',
            'poolStudents',
            'regions',
            'prototypeEvidenceFormUrl',
            'prototypeModificationEvidenceFormUrl',
        ));
    }

    public function register(
        Competition $competition,
        CompetitionParticipantRegistrationService $participantRegistrationService,
        CompetitionEligibilityPoolService $eligibilityPool,
    ): View|\Illuminate\Http\RedirectResponse {
        $user = Auth::user();
        $teacherClub = $this->resolveTeacherClubForCompetition($user->id, $competition->club_id, true);

        if (! $teacherClub) {
            abort(403, 'You must join this club first before registering for competitions.');
        }

        $registration = $participantRegistrationService->findForTeacherClub($competition, $teacherClub);

        if ($registration) {
            if (in_array($registration->status, ['pending', 'approved'], true)) {
                $message = $registration->status === 'pending'
                    ? 'Your school is already registered and is awaiting admin review.'
                    : 'Your school registration has already been approved.';

                return redirect()->route('client.competitions.show', $competition)
                    ->with('status', $message);
            }
            if (! in_array($registration->status, ['draft', 'rejected'], true)) {
                return redirect()->route('client.competitions.show', $competition)
                    ->with('error', 'You cannot edit registration in its current state.');
            }
            if (! $participantRegistrationService->canEditRegistration($registration, $teacherClub)) {
                return redirect()->route('client.competitions.show', $competition)
                    ->with('status', 'Your school already has a competition registration. You can view the details here.');
            }
        }

        if ($redirect = $this->registrationNotAllowedRedirect($competition, $registration)) {
            return $redirect;
        }

        $competition->loadMissing(['classRequirements.schoolClass']);

        $pool = $eligibilityPool->computePool($competition, $teacherClub);
        $students = $pool['students'];
        $allowedClassIds = $pool['allowed_class_ids'];

        $classesQuery = SchoolClass::query()->where('is_active', true)->orderBy('name');
        $classes = ! empty($allowedClassIds)
            ? $classesQuery->whereIn('id', $allowedClassIds)->get()
            : $classesQuery->get();

        $teacherClub->loadMissing('school');
        $schoolRegionId = $teacherClub->school?->region_id;
        $regionDistricts = $schoolRegionId
            ? District::query()
                ->where('region_id', $schoolRegionId)
                ->orderBy('name')
                ->get(['id', 'name'])
            : collect();

        return view('client.back.competitions.register', compact(
            'competition',
            'teacherClub',
            'students',
            'allowedClassIds',
            'classes',
            'registration',
            'regionDistricts',
            'schoolRegionId',
        ));
    }

    public function bulkImportStudents(
        Request $request,
        Competition $competition,
        StudentCsvBulkImportService $csvImport,
        CompetitionParticipantRegistrationService $participantRegistrationService,
    ): RedirectResponse {
        $user = Auth::user();
        $teacherClub = $this->resolveTeacherClubForCompetition($user->id, $competition->club_id, true);

        if (! $teacherClub) {
            return redirect()->route('client.competitions.index')
                ->with('error', 'You must join this club first.');
        }

        $existingRegistration = $participantRegistrationService->findForTeacherClub($competition, $teacherClub);

        if ($existingRegistration && ! $participantRegistrationService->canEditRegistration($existingRegistration, $teacherClub)) {
            return redirect()->route('client.competitions.show', $competition)
                ->with('status', 'Your school is already registered for this competition.');
        }

        if ($redirect = $this->registrationNotAllowedRedirect($competition, $existingRegistration)) {
            return $redirect;
        }

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $result = $csvImport->import($teacherClub, $request->file('csv_file'), $competition);

        if ($result['errors'] !== []) {
            return redirect()
                ->route('client.competitions.register', $competition)
                ->with('error', $csvImport->formatErrorsForFlash($result['errors']))
                ->withInput();
        }

        return redirect()
            ->route('client.competitions.register', $competition)
            ->with('status', "Successfully imported {$result['success_count']} student(s).");
    }

    public function clearPoolStudents(
        Competition $competition,
        CompetitionParticipantRegistrationService $participantRegistrationService,
        CompetitionEligibilityPoolService $eligibilityPool,
    ): RedirectResponse {
        $user = Auth::user();
        $teacherClub = $this->resolveTeacherClubForCompetition($user->id, $competition->club_id, true);

        if (! $teacherClub) {
            return redirect()->route('client.competitions.index')
                ->with('error', 'You must join this club first.');
        }

        $existingRegistration = $participantRegistrationService->findForTeacherClub($competition, $teacherClub);

        if ($existingRegistration && ! $participantRegistrationService->canEditRegistration($existingRegistration, $teacherClub)) {
            return redirect()->route('client.competitions.show', $competition)
                ->with('status', 'Your school is already registered for this competition.');
        }

        if ($redirect = $this->registrationNotAllowedRedirect($competition, $existingRegistration)) {
            return $redirect;
        }

        $pool = $eligibilityPool->computePool($competition, $teacherClub);
        $studentIds = $pool['students']->pluck('id')->all();
        $deleted = count($studentIds);
        if ($studentIds !== []) {
            \App\Models\Student::query()->whereIn('id', $studentIds)->delete();
        }

        $eligibilityPool->refreshOpenRegistrationSnapshots($teacherClub);

        return redirect()
            ->route('client.competitions.register', $competition)
            ->with('status', "Removed {$deleted} student(s) from this competition pool. You can upload a corrected CSV now.");
    }

    /**
     * Download a CSV template with headers and one example row (same format as bulk import).
     */
    public function downloadRegistrationCsvTemplate(
        Competition $competition,
        CompetitionParticipantRegistrationService $participantRegistrationService,
    ): StreamedResponse|RedirectResponse {
        $user = Auth::user();
        $teacherClub = $this->resolveTeacherClubForCompetition($user->id, $competition->club_id, true);

        if (! $teacherClub) {
            return redirect()->route('client.competitions.index')
                ->with('error', 'You must join this club first.');
        }

        $existingRegistration = $participantRegistrationService->findForTeacherClub($competition, $teacherClub);

        if ($existingRegistration && ! $participantRegistrationService->canEditRegistration($existingRegistration, $teacherClub)) {
            return redirect()->route('client.competitions.show', $competition)
                ->with('status', 'Your school is already registered for this competition.');
        }

        if ($redirect = $this->registrationNotAllowedRedirect($competition, $existingRegistration)) {
            return $redirect;
        }

        $filename = 'competition-participants-template.csv';

        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['name', 'gender', 'class_name', 'date_of_birth']);
            fputcsv($out, ['Example Student', 'female', 'Form 1', '15-05-2010']);
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function storeRegistration(
        Request $request,
        Competition $competition,
        CompetitionParticipantRegistrationService $participantRegistrationService,
        CompetitionEligibilityPoolService $eligibilityPool,
    ): RedirectResponse {
        $user = Auth::user();
        $teacherClub = $this->resolveTeacherClubForCompetition($user->id, $competition->club_id, true);

        if (! $teacherClub) {
            return redirect()->route('client.competitions.index')
                ->with('error', 'You must join this club first.');
        }

        $existing = $participantRegistrationService->findForTeacherClub($competition, $teacherClub);

        if ($redirect = $this->registrationNotAllowedRedirect($competition, $existing)) {
            return $redirect;
        }

        if ($existing && ! in_array($existing->status, ['draft', 'rejected'], true)) {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'Your school has already registered for this competition.');
        }

        if ($existing && ! $participantRegistrationService->canEditRegistration($existing, $teacherClub)) {
            return redirect()->route('client.competitions.show', $competition)
                ->with('status', 'Your school already has a competition registration.');
        }

        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'school_district_id' => ['nullable', 'exists:districts,id'],
            'school_ward' => ['nullable', 'string', 'max:255'],
        ]);

        $location = SchoolLocation::normalizeSchoolAttributes([
            'district_id' => $validated['school_district_id'] ?? null,
            'region_id' => $teacherClub->school?->region_id,
        ]);
        $schoolDistrict = $location['district']
            ?? $teacherClub->school?->districtName();

        $pool = $eligibilityPool->computePool($competition, $teacherClub);
        $poolCount = $pool['count'];
        $poolGirlsCount = $pool['girls_count'];
        $poolRatio = $pool['girls_ratio'];

        $poolError = $this->competitionPoolEligibilityError($competition, $poolCount, (float) $poolRatio);
        if ($poolError !== null) {
            return back()->withErrors(['eligibility' => $poolError])->withInput();
        }

        DB::transaction(function () use ($validated, $competition, $teacherClub, $user, $poolCount, $poolGirlsCount, $poolRatio, $existing, $participantRegistrationService, $location, $schoolDistrict): void {
            if ($existing) {
                $existing->representativeStudents()->delete();
                $existing->update([
                    'status' => 'pending',
                    'teacher_club_id' => $teacherClub->id,
                    'teacher_user_id' => $user->id,
                    'school_id' => $teacherClub->school_id,
                    'region_id' => $location['region_id'] ?? $teacherClub->school?->region_id,
                    'district_id' => $location['district_id'] ?? $teacherClub->school?->district_id,
                    'teacher_name' => $user->name,
                    'teacher_email' => $user->email,
                    'teacher_phone' => $user->phone,
                    'school_name' => $validated['school_name'],
                    'school_district' => $schoolDistrict,
                    'school_ward' => $validated['school_ward'] ?? $teacherClub->school?->ward,
                    'student_count_snapshot' => $poolCount,
                    'girls_count_snapshot' => $poolGirlsCount,
                    'girls_ratio_snapshot' => round((float) $poolRatio, 2),
                    'eligibility_met' => true,
                    'submitted_at' => now(),
                    'rejection_reason' => null,
                ]);

                return;
            }

            // Ensure school-scoped create path even if ensureDraft was never called.
            $created = $participantRegistrationService->ensureDraftRegistration($teacherClub, $competition);
            $created->update([
                'status' => 'pending',
                'teacher_club_id' => $teacherClub->id,
                'teacher_user_id' => $user->id,
                'school_id' => $teacherClub->school_id,
                'region_id' => $location['region_id'] ?? $teacherClub->school?->region_id,
                'district_id' => $location['district_id'] ?? $teacherClub->school?->district_id,
                'teacher_name' => $user->name,
                'teacher_email' => $user->email,
                'teacher_phone' => $user->phone,
                'school_name' => $validated['school_name'],
                'school_district' => $schoolDistrict,
                'school_ward' => $validated['school_ward'] ?? $teacherClub->school?->ward,
                'student_count_snapshot' => $poolCount,
                'girls_count_snapshot' => $poolGirlsCount,
                'girls_ratio_snapshot' => round((float) $poolRatio, 2),
                'eligibility_met' => true,
                'submitted_at' => now(),
                'rejection_reason' => null,
            ]);
        });

        return redirect()
            ->route('client.competitions.show', $competition)
            ->with('status', 'Registration submitted successfully. Wait for Judges to review your registration and you will be notified.');
    }

    public function registerRepresentatives(
        Competition $competition,
        CompetitionParticipantRegistrationService $participantRegistrationService,
        CompetitionEligibilityPoolService $eligibilityPool,
    ): View|\Illuminate\Http\RedirectResponse {
        $user = Auth::user();
        $teacherClub = $this->resolveTeacherClubForCompetition($user->id, $competition->club_id, true);

        if (! $teacherClub) {
            abort(403, 'You must join this club first before registering for competitions.');
        }

        $registration = $participantRegistrationService->findForTeacherClub($competition, $teacherClub);

        if (! $registration || $registration->status !== 'draft') {
            return redirect()->route('client.competitions.register', $competition)
                ->with('error', 'Save your participant list and school details first, or your registration is already submitted.');
        }

        if (! $participantRegistrationService->canEditRegistration($registration, $teacherClub)) {
            return redirect()->route('client.competitions.show', $competition)
                ->with('status', 'Your school already has a competition registration.');
        }

        if ($redirect = $this->registrationNotAllowedRedirect($competition, $registration)) {
            return $redirect;
        }

        $pool = $eligibilityPool->computePool($competition, $teacherClub);
        $students = $pool['students'];
        $allowedClassIds = $pool['allowed_class_ids'];

        return view('client.back.competitions.register-representatives', compact(
            'competition',
            'teacherClub',
            'registration',
            'students',
            'allowedClassIds',
        ));
    }

    public function storeRegistrationRepresentatives(
        Request $request,
        Competition $competition,
        CompetitionParticipantRegistrationService $participantRegistrationService,
        CompetitionEligibilityPoolService $eligibilityPool,
    ): RedirectResponse {
        $user = Auth::user();
        $teacherClub = $this->resolveTeacherClubForCompetition($user->id, $competition->club_id, true);

        if (! $teacherClub) {
            return redirect()->route('client.competitions.index')
                ->with('error', 'You must join this club first.');
        }

        $registration = $participantRegistrationService->findForTeacherClub($competition, $teacherClub);

        if ($redirect = $this->registrationNotAllowedRedirect($competition, $registration)) {
            return $redirect;
        }

        if (! $registration || $registration->status !== 'draft') {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'No draft registration found to finalize.');
        }

        if (! $participantRegistrationService->canEditRegistration($registration, $teacherClub)) {
            return redirect()->route('client.competitions.show', $competition)
                ->with('status', 'Your school already has a competition registration.');
        }

        $repCount = $this->requiredRepresentativeCount($competition);

        $validated = $request->validate([
            'representative_student_ids' => ['required', 'array', 'size:'.$repCount],
            'representative_student_ids.*' => ['exists:students,id'],
            'student_roles' => ['nullable', 'array'],
        ]);

        $pool = $eligibilityPool->computePool($competition, $teacherClub);
        $poolStudents = $pool['students'];
        $poolCount = $pool['count'];
        $poolGirlsCount = $pool['girls_count'];
        $poolRatio = (float) $pool['girls_ratio'];

        $poolError = $this->competitionPoolEligibilityError($competition, $poolCount, $poolRatio);
        if ($poolError !== null) {
            return back()->withErrors(['representative_student_ids' => $poolError])->withInput();
        }

        $representatives = $poolStudents
            ->whereIn('id', $validated['representative_student_ids'])
            ->values();

        if ($representatives->count() !== $repCount) {
            return back()->withErrors(['representative_student_ids' => "Please select exactly {$repCount} valid representatives."])->withInput();
        }

        $repGirlsRatio = ($representatives->where('gender', 'female')->count() / $repCount) * 100;
        if ($repGirlsRatio < 70) {
            return back()->withErrors([
                'representative_student_ids' => "At least 70% of the {$repCount} selected representatives must be girls.",
            ])->withInput();
        }

        DB::transaction(function () use ($registration, $representatives, $request, $poolCount, $poolGirlsCount, $poolRatio, $teacherClub, $user): void {
            $registration->representativeStudents()->delete();

            foreach ($representatives as $student) {
                CompetitionRegistrationStudent::create([
                    'competition_registration_id' => $registration->id,
                    'student_id' => $student->id,
                    'role' => $request->input("student_roles.{$student->id}"),
                    'is_representative' => true,
                ]);
            }

            $registration->update([
                'status' => 'pending',
                'teacher_club_id' => $teacherClub->id,
                'teacher_user_id' => $user->id,
                'student_count_snapshot' => $poolCount,
                'girls_count_snapshot' => $poolGirlsCount,
                'girls_ratio_snapshot' => round($poolRatio, 2),
                'eligibility_met' => true,
                'submitted_at' => now(),
            ]);
        });

        return redirect()->route('client.competitions.show', $competition)
            ->with('status', 'Registration submitted successfully. It is now pending admin approval.');
    }

    public function createIdea(Competition $competition): View|\Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        $teacherClub = $this->resolveTeacherClubForCompetition($user->id, $competition->club_id, true);
        $registration = $this->resolveSchoolRegistration($competition, $teacherClub);

        if (! $registration) {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'You must finish registration first.');
        }

        if ($registration->status === 'draft') {
            return redirect()->route('client.competitions.register', $competition)
                ->with('error', 'Submit your participant registration for admin approval before submitting an idea.');
        }

        if ($registration->status !== 'approved') {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'Registration must be approved before idea submission.');
        }

        if (! $competition->isPhaseOpen('idea_submission')) {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'Idea submission phase is currently closed.');
        }

        return redirect()
            ->route('client.competitions.show', $competition)
            ->withFragment('idea-submission');
    }

    public function storeIdea(Request $request, Competition $competition): RedirectResponse
    {
        $user = Auth::user();
        $teacherClub = $this->resolveTeacherClubForCompetition($user->id, $competition->club_id, true);
        $registration = $this->resolveSchoolRegistration($competition, $teacherClub);

        if (! $registration || $registration->status === 'draft') {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'Complete and submit your competition registration before submitting an idea.');
        }

        if ($registration->status !== 'approved') {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'You are not authorized to submit an idea for this competition.');
        }

        if (! $competition->isPhaseOpen('idea_submission')) {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'Idea submission phase is currently closed.');
        }

        $rules = [
            'problem_title' => ['required', 'string', 'max:255'],
            'problem_statement' => ['required', 'string'],
            'proposed_solution' => ['required', 'string'],
            'stem_category' => ['required', 'string', 'max:120'],
            'stem_category_other' => ['nullable', 'string', 'max:120'],
            'community_region_id' => ['nullable', 'exists:regions,id'],
            'community_location' => ['nullable', 'string', 'max:255'],
            'supporting_notes' => ['nullable', 'string'],
            'submit_final' => ['nullable', 'boolean'],
        ];

        if ($request->boolean('submit_final')) {
            $repCount = $this->requiredRepresentativeCount($competition);
            $rules['representative_student_ids'] = ['required', 'array', 'size:'.$repCount];
            $rules['representative_student_ids.*'] = ['exists:students,id'];
            $rules['student_roles'] = ['nullable', 'array'];
        } else {
            $rules['sync_representatives'] = ['nullable', 'in:1'];
            $rules['student_roles'] = ['nullable', 'array'];
            $rules['representative_student_ids'] = ['nullable', 'array'];
            $rules['representative_student_ids.*'] = ['integer', 'exists:students,id'];
        }

        $validated = $request->validate($rules);

        $stemCategory = trim((string) $validated['stem_category']);
        if ($stemCategory === 'Other') {
            $stemCategory = trim((string) ($validated['stem_category_other'] ?? ''));
            if ($stemCategory === '') {
                return back()->withErrors([
                    'stem_category_other' => 'Please enter the STEM discipline when selecting Other.',
                ])->withInput();
            }
        }

        $idea = CompetitionIdea::firstOrNew([
            'competition_registration_id' => $registration->id,
        ]);

        $idea->fill([
            'competition_id' => $competition->id,
            'problem_title' => $validated['problem_title'],
            'problem_statement' => $validated['problem_statement'],
            'proposed_solution' => $validated['proposed_solution'],
            'stem_category' => $stemCategory,
            'community_region_id' => $validated['community_region_id'] ?? null,
            'community_location' => $validated['community_location'] ?? null,
            'supporting_notes' => $validated['supporting_notes'] ?? null,
        ]);

        if ($request->boolean('submit_final')) {
            $eligibilityPool = app(CompetitionEligibilityPoolService::class);
            $pool = $eligibilityPool->computePool($competition, $teacherClub);
            $repCount = $this->requiredRepresentativeCount($competition);

            $poolStudents = $pool['students'];
            $poolCount = $pool['count'];
            $poolGirlsCount = $pool['girls_count'];
            $poolRatio = (float) $pool['girls_ratio'];

            $poolError = $this->competitionPoolEligibilityError($competition, $poolCount, $poolRatio);
            if ($poolError !== null) {
                return back()->withErrors(['representative_student_ids' => $poolError])->withInput();
            }

            $representatives = $poolStudents
                ->whereIn('id', $validated['representative_student_ids'])
                ->values();

            if ($representatives->count() !== $repCount) {
                return back()->withErrors([
                    'representative_student_ids' => "Please select exactly {$repCount} valid representatives from your class pool.",
                ])->withInput();
            }

            $repGirlsRatio = ($representatives->where('gender', 'female')->count() / $repCount) * 100;
            if ($repGirlsRatio < 70) {
                return back()->withErrors([
                    'representative_student_ids' => "At least 70% of the {$repCount} selected representatives must be girls.",
                ])->withInput();
            }

            DB::transaction(function () use ($idea, $registration, $representatives, $request, $validated): void {
                $idea->status = 'submitted';
                $idea->submitted_at = now();
                $idea->save();

                $this->replaceIdeaRepresentatives(
                    $registration,
                    $request,
                    $representatives->keyBy('id'),
                    $validated['representative_student_ids'],
                );
            });

            return redirect()
                ->route('client.competitions.show', $competition)
                ->withFragment('idea-submission')
                ->with('status', 'Idea submitted successfully. Your school now waits for regional shortlisting.');
        }

        if (! $idea->exists) {
            $idea->status = 'draft';
        }

        if ($request->input('sync_representatives') === '1') {
            $repCount = $this->requiredRepresentativeCount($competition);
            $poolStudents = app(CompetitionEligibilityPoolService::class)
                ->computePool($competition, $teacherClub)['students'];

            $ids = array_values(array_unique(array_map(
                'intval',
                (array) ($validated['representative_student_ids'] ?? []),
            )));

            if (count($ids) > $repCount) {
                return back()->withErrors([
                    'representative_student_ids' => "You can select at most {$repCount} representatives for this competition.",
                ])->withInput();
            }

            $studentsById = $poolStudents
                ->whereIn('id', $ids)
                ->keyBy('id');

            if ($studentsById->count() !== count($ids)) {
                return back()->withErrors([
                    'representative_student_ids' => 'One or more selected students are not in your class pool.',
                ])->withInput();
            }

            DB::transaction(function () use ($idea, $registration, $request, $studentsById, $ids): void {
                $idea->save();
                $this->replaceIdeaRepresentatives($registration, $request, $studentsById, $ids);
            });
        } else {
            $idea->save();
        }

        return redirect()
            ->route('client.competitions.show', $competition)
            ->withFragment('idea-submission')
            ->with('status', 'Draft saved successfully.');
    }

    public function createPrototype(Competition $competition): View|\Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        $teacherClub = $this->resolveTeacherClubForCompetition($user->id, $competition->club_id, true);
        $registration = $this->resolveSchoolRegistration($competition, $teacherClub);

        if (! $registration) {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'You must finish registration first.');
        }

        if ($registration->status === 'draft') {
            return redirect()->route('client.competitions.register', $competition)
                ->with('error', 'Submit your participant registration for admin approval before submitting a prototype.');
        }

        if ($registration->status !== 'approved') {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'Registration must be approved before prototype development.');
        }

        if (! $competition->isPhaseOpen('prototype_development')) {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'Prototype development phase is currently closed.');
        }

        $idea = $registration->idea()->with('shortlist')->first();

        if (! $idea || $idea->status === 'draft') {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'You must submit an idea before developing a prototype.');
        }

        $decisionStatus = $idea->shortlist?->status;
        if ($decisionStatus === null && $idea->status === 'shortlisted') {
            $decisionStatus = 'shortlisted';
        }

        if ($decisionStatus !== 'shortlisted') {
            $statusLabel = $decisionStatus ? ucfirst(str_replace('_', ' ', $decisionStatus)) : 'Not shortlisted yet';
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', "Prototype development is available only for shortlisted teams. Current status: {$statusLabel}.");
        }

        return redirect()
            ->route('client.competitions.show', $competition)
            ->withFragment('prototype-development');
    }

    public function storePrototype(Request $request, Competition $competition): RedirectResponse
    {
        $user = Auth::user();
        $teacherClub = $this->resolveTeacherClubForCompetition($user->id, $competition->club_id, true);
        $registration = $this->resolveSchoolRegistration($competition, $teacherClub);

        if (! $registration || $registration->status === 'draft') {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'Complete and submit your competition registration before developing a prototype.');
        }

        if ($registration->status !== 'approved') {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'You are not authorized to submit a prototype for this competition.');
        }

        if (! $competition->isPhaseOpen('prototype_development')) {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'Prototype development phase is currently closed.');
        }

        $idea = $registration->idea()->with('shortlist')->first();

        if (! $idea || $idea->status === 'draft') {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'You must submit an idea before developing a prototype.');
        }

        $shortlist = $idea->shortlist;
        $decisionStatus = $shortlist?->status;
        if ($decisionStatus === null && $idea->status === 'shortlisted') {
            $decisionStatus = 'shortlisted';
        }

        if ($decisionStatus !== 'shortlisted') {
            $statusLabel = $decisionStatus ? ucfirst(str_replace('_', ' ', $decisionStatus)) : 'Not shortlisted yet';
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', "Prototype development is available only for shortlisted teams. Current status: {$statusLabel}.");
        }

        $phase = $competition->phases()->where('phase_key', 'prototype_development')->first();

        $prototype = CompetitionPrototypeSubmission::firstOrNew([
            'competition_idea_id' => $idea->id,
        ]);

        $rules = [
            'prototype_title' => ['required', 'string', 'max:255'],
            'prototype_concept' => ['required', 'string'],
            'prototype_objectives' => ['required', 'string'],
            'prototype_design_description' => ['required', 'string'],
            'prototype_materials' => ['required', 'string'],
            'prototype_build_process' => ['required', 'string'],
            'prototype_challenges' => ['nullable', 'string'],
            'prototype_test_results' => ['nullable', 'string'],
            'prototype_next_steps' => ['nullable', 'string'],
            'submit_final' => ['nullable', 'boolean'],
        ];

        $validated = $request->validate($rules);

        $prototype->fill([
            'competition_id' => $competition->id,
            'competition_phase_id' => $phase?->id,
            'competition_registration_id' => $registration->id,
            'competition_shortlist_id' => $shortlist?->id,
            'prototype_title' => $validated['prototype_title'],
            'prototype_concept' => $validated['prototype_concept'],
            'prototype_objectives' => $validated['prototype_objectives'],
            'prototype_design_description' => $validated['prototype_design_description'],
            'prototype_materials' => $validated['prototype_materials'],
            'prototype_build_process' => $validated['prototype_build_process'],
            'prototype_challenges' => $validated['prototype_challenges'] ?? null,
            'prototype_test_results' => $validated['prototype_test_results'] ?? null,
            'prototype_next_steps' => $validated['prototype_next_steps'] ?? null,
        ]);

        if ($request->boolean('submit_final')) {
            $prototype->status = 'submitted';
            $prototype->submitted_at = now();
            $prototype->submitted_by = $user->id;
        } elseif (! $prototype->exists) {
            $prototype->status = 'draft';
        }

        $prototype->save();

        if ($request->boolean('submit_final')) {
            return redirect()
                ->route('client.competitions.show', $competition)
                ->withFragment('prototype-development')
                ->with('status', 'Prototype submitted successfully. Awaiting prototype shortlisting.');
        }

        return redirect()
            ->route('client.competitions.show', $competition)
            ->withFragment('prototype-development')
            ->with('status', 'Prototype draft saved successfully.');
    }

    public function createPrototypeModification(Competition $competition): View|\Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        $teacherClub = $this->resolveTeacherClubForCompetition($user->id, $competition->club_id, true);
        $registration = $this->resolveSchoolRegistration($competition, $teacherClub);

        if (! $registration) {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'You must finish registration first.');
        }

        if ($registration->status !== 'approved') {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'Registration must be approved before prototype modification.');
        }

        if (! $competition->isPhaseOpen('prototype_modification')) {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'Prototype modification phase is currently closed.');
        }

        $idea = $registration->idea()->with('prototypeSubmission.prototypeShortlist')->first();
        $prototypeShortlistStatus = $idea?->prototypeSubmission?->prototypeShortlist?->status;
        if (! $idea || ! $idea->prototypeSubmission || $prototypeShortlistStatus !== 'shortlisted') {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'Only prototype-shortlisted teams can submit modifications.');
        }

        return redirect()
            ->route('client.competitions.show', $competition)
            ->withFragment('prototype-modification');
    }

    public function storePrototypeModification(Request $request, Competition $competition): RedirectResponse
    {
        $user = Auth::user();
        $teacherClub = $this->resolveTeacherClubForCompetition($user->id, $competition->club_id, true);
        $registration = $this->resolveSchoolRegistration($competition, $teacherClub);

        if (! $registration || $registration->status !== 'approved') {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'You are not authorized to submit prototype modifications for this competition.');
        }

        if (! $competition->isPhaseOpen('prototype_modification')) {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'Prototype modification phase is currently closed.');
        }

        $idea = $registration->idea()->with('prototypeSubmission.prototypeShortlist')->first();
        $prototypeSubmission = $idea?->prototypeSubmission;
        $prototypeShortlistStatus = $prototypeSubmission?->prototypeShortlist?->status;
        if (! $idea || ! $prototypeSubmission || $prototypeShortlistStatus !== 'shortlisted') {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'Only prototype-shortlisted teams can submit modifications.');
        }

        $phase = $competition->phases()->where('phase_key', 'prototype_modification')->first();

        $modification = CompetitionPrototypeModification::firstOrNew([
            'competition_idea_id' => $idea->id,
        ]);

        $validated = $request->validate([
            'prototype_title' => ['required', 'string', 'max:255'],
            'prototype_concept' => ['required', 'string'],
            'prototype_objectives' => ['required', 'string'],
            'prototype_design_description' => ['required', 'string'],
            'prototype_materials' => ['required', 'string'],
            'prototype_build_process' => ['required', 'string'],
            'prototype_challenges' => ['nullable', 'string'],
            'prototype_test_results' => ['nullable', 'string'],
            'prototype_next_steps' => ['nullable', 'string'],
            'submit_final' => ['nullable', 'boolean'],
        ]);

        $modification->fill([
            'competition_id' => $competition->id,
            'competition_phase_id' => $phase?->id,
            'competition_registration_id' => $registration->id,
            'competition_idea_id' => $idea->id,
            'competition_prototype_submission_id' => $prototypeSubmission->id,
            'prototype_title' => $validated['prototype_title'],
            'prototype_concept' => $validated['prototype_concept'],
            'prototype_objectives' => $validated['prototype_objectives'],
            'prototype_design_description' => $validated['prototype_design_description'],
            'prototype_materials' => $validated['prototype_materials'],
            'prototype_build_process' => $validated['prototype_build_process'],
            'prototype_challenges' => $validated['prototype_challenges'] ?? null,
            'prototype_test_results' => $validated['prototype_test_results'] ?? null,
            'prototype_next_steps' => $validated['prototype_next_steps'] ?? null,
        ]);

        if ($request->boolean('submit_final')) {
            $modification->status = 'submitted';
            $modification->submitted_at = now();
            $modification->submitted_by = $user->id;
        } elseif (! $modification->exists) {
            $modification->status = 'draft';
        }

        $modification->save();

        if ($request->boolean('submit_final')) {
            return redirect()
                ->route('client.competitions.show', $competition)
                ->withFragment('prototype-modification')
                ->with('status', 'Prototype modification submitted successfully. Awaiting pitch selection.');
        }

        return redirect()
            ->route('client.competitions.show', $competition)
            ->withFragment('prototype-modification')
            ->with('status', 'Prototype modification draft saved successfully.');
    }

    /**
     * @return array{min: int, max: int|null}
     */
    private function competitionPoolBounds(Competition $competition): array
    {
        return app(CompetitionEligibilityPoolService::class)->poolBounds($competition);
    }

    private function competitionPoolEligibilityError(
        Competition $competition,
        int $poolCount,
        float $poolRatio
    ): ?string {
        return app(CompetitionEligibilityPoolService::class)
            ->eligibilityError($competition, $poolCount, $poolRatio);
    }

    private function requiredRepresentativeCount(Competition $competition): int
    {
        $n = (int) ($competition->representative_count ?? 10);

        return max(1, $n);
    }

    /**
     * @param  Collection<int, \App\Models\Student>  $studentsById
     * @param  array<int, int|string>  $orderedStudentIds
     */
    private function replaceIdeaRepresentatives(
        CompetitionRegistration $registration,
        Request $request,
        Collection $studentsById,
        array $orderedStudentIds,
    ): void {
        $registration->representativeStudents()->delete();

        foreach ($orderedStudentIds as $studentId) {
            $id = (int) $studentId;
            $student = $studentsById->get($id);
            if (! $student) {
                continue;
            }

            CompetitionRegistrationStudent::create([
                'competition_registration_id' => $registration->id,
                'student_id' => $student->id,
                'role' => $request->input("student_roles.{$student->id}"),
                'is_representative' => true,
            ]);
        }
    }

    private function resolveSchoolRegistration(
        Competition $competition,
        ?TeacherClub $teacherClub,
    ): ?CompetitionRegistration {
        if (! $teacherClub) {
            return null;
        }

        return app(CompetitionParticipantRegistrationService::class)
            ->findForTeacherClub($competition, $teacherClub);
    }

    private function registrationNotAllowedRedirect(
        Competition $competition,
        ?CompetitionRegistration $registration,
    ): ?RedirectResponse {
        if ($competition->allowsTeacherRegistration($registration)) {
            return null;
        }

        return redirect()->route('client.competitions.show', $competition)
            ->with('error', 'Registration phase is currently closed.');
    }

    private function resolveTeacherClubForCompetition(
        int $userId,
        int $competitionClubId,
        bool $approvedOnly = false
    ): ?TeacherClub {
        return TeacherClub::query()
            ->where('user_id', $userId)
            ->where('club_id', $competitionClubId)
            ->when($approvedOnly, fn ($query) => $query->approved())
            ->with(['school.regionModel', 'students.schoolClass'])
            ->first();
    }

    public function deleteAttachment(Competition $competition): RedirectResponse
    {
        return redirect()
            ->route('client.competitions.show', $competition)
            ->with('error', 'Attachment deletion is not enabled in the phase-based flow yet.');
    }
}
