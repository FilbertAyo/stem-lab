<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Mail\CompetitionIdeaSubmittedMail;
use App\Models\Competition;
use App\Models\CompetitionIdea;
use App\Models\CompetitionPhaseEvidenceForm;
use App\Models\CompetitionPrototypeModification;
use App\Models\CompetitionPrototypeSubmission;
use App\Models\CompetitionRegistration;
use App\Models\CompetitionRegistrationStudent;
use App\Models\District;
use App\Models\Region;
use App\Models\SchoolClass;
use App\Models\TeacherClub;
use App\Services\AdminActivityNotifier;
use App\Services\CompetitionEligibilityPoolService;
use App\Services\CompetitionParticipantRegistrationService;
use App\Services\Sms\CompetitionSmsTemplates;
use App\Services\Sms\SmsNotifier;
use App\Services\StudentCsvBulkImportService;
use App\Services\TeacherClubSharedContextService;
use App\Support\AdminUrl;
use App\Support\ClientUrl;
use App\Support\CompetitionParticipantPhaseNavigator;
use App\Support\SchoolLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
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
            $registration = $participantRegistrationService->ensureDraftRegistration($teacherClub, $competition);
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
                'communityDistrict',
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
        $districtsByRegion = District::groupedByRegionId();

        $registrationApproved = $registration && $registration->status === 'approved';
        $ideaSubmitted = $idea && $idea->status !== 'draft';
        $ideaDecisionStatus = $idea?->shortlist?->status;
        if ($ideaDecisionStatus === null && $idea && $idea->status === 'shortlisted') {
            $ideaDecisionStatus = 'shortlisted';
        }
        $ideaShortlisted = $ideaDecisionStatus === 'shortlisted';
        $prototypeSubmitted = $idea?->prototypeSubmission && $idea->prototypeSubmission->status === 'submitted';
        $prototypeShortlisted = $idea?->prototypeSubmission?->prototypeShortlist?->status === 'shortlisted';
        $modificationSubmitted = $idea?->prototypeModification && $idea->prototypeModification->status === 'submitted';

        $requestedPhase = CompetitionParticipantPhaseNavigator::normalizeRequestedPhaseKey(
            request()->query('phase') ?? request()->query('view_phase')
        );

        $participantPhaseView = CompetitionParticipantPhaseNavigator::resolve(
            $competition,
            $registrationApproved,
            $ideaSubmitted,
            $ideaShortlisted,
            $prototypeSubmitted,
            $prototypeShortlisted,
            $modificationSubmitted,
            $requestedPhase,
        );

        return view('client.back.competitions.show', compact(
            'competition',
            'teacherClub',
            'registration',
            'idea',
            'poolStudents',
            'regions',
            'districtsByRegion',
            'prototypeEvidenceFormUrl',
            'prototypeModificationEvidenceFormUrl',
            'participantPhaseView',
            'registrationApproved',
            'ideaSubmitted',
            'ideaShortlisted',
            'prototypeSubmitted',
            'prototypeShortlisted',
            'modificationSubmitted',
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

        $rosterOwner = app(TeacherClubSharedContextService::class)
            ->resolveRosterOwner($teacherClub);

        $result = $csvImport->import($rosterOwner, $request->file('csv_file'), $competition);

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

        $linkedSchool = $teacherClub->school;

        $validated = $request->validate([
            'school_name' => [$linkedSchool ? 'nullable' : 'required', 'string', 'max:255'],
            'school_district_id' => ['nullable', 'exists:districts,id'],
            'school_ward' => ['nullable', 'string', 'max:255'],
        ]);

        if ($linkedSchool) {
            $schoolName = $linkedSchool->name;
            $schoolWard = $linkedSchool->ward;
            $snapshot = SchoolLocation::registrationSnapshotFromSchool($linkedSchool);

            if (! empty($validated['school_district_id'])) {
                $normalized = SchoolLocation::normalizeSchoolAttributes([
                    'district_id' => $validated['school_district_id'],
                    'region_id' => $linkedSchool->region_id,
                ]);
                $snapshot['district_id'] = $normalized['district_id'] ?? $snapshot['district_id'];
                $snapshot['region_id'] = $normalized['region_id'] ?? $snapshot['region_id'];
                $snapshot['school_district'] = $normalized['district'] ?? $snapshot['school_district'];
            }

            $location = $snapshot;
            $schoolDistrict = $snapshot['school_district'] ?? $linkedSchool->districtName();
        } else {
            $schoolName = $validated['school_name'];
            $schoolWard = $validated['school_ward'] ?? null;
            $normalized = SchoolLocation::normalizeSchoolAttributes([
                'district_id' => $validated['school_district_id'] ?? null,
                'region_id' => null,
            ]);
            $location = [
                'region_id' => $normalized['region_id'] ?? null,
                'district_id' => $normalized['district_id'] ?? null,
                'school_district' => $normalized['district'] ?? null,
            ];
            $schoolDistrict = $location['school_district'];
        }

        $pool = $eligibilityPool->computePool($competition, $teacherClub);
        $poolCount = $pool['count'];
        $poolGirlsCount = $pool['girls_count'];
        $poolRatio = $pool['girls_ratio'];

        $poolError = $this->competitionPoolEligibilityError($competition, $poolCount, (float) $poolRatio);
        if ($poolError !== null) {
            return back()->withErrors(['eligibility' => $poolError])->withInput();
        }

        DB::transaction(function () use ($schoolName, $schoolWard, $competition, $teacherClub, $user, $poolCount, $poolGirlsCount, $poolRatio, $existing, $participantRegistrationService, $location, $schoolDistrict): void {
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
                    'school_name' => $schoolName,
                    'school_district' => $schoolDistrict,
                    'school_ward' => $schoolWard,
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
                'school_name' => $schoolName,
                'school_district' => $schoolDistrict,
                'school_ward' => $schoolWard,
                'student_count_snapshot' => $poolCount,
                'girls_count_snapshot' => $poolGirlsCount,
                'girls_ratio_snapshot' => round((float) $poolRatio, 2),
                'eligibility_met' => true,
                'submitted_at' => now(),
                'rejection_reason' => null,
            ]);
        });

        $submitted = $participantRegistrationService->findForTeacherClub($competition, $teacherClub);
        if ($submitted && $submitted->status === 'pending') {
            $this->notifyAdminsOfCompetitionSubmission(
                'registration',
                $competition,
                $submitted,
                $competition->name,
                'admin.competitions.registrations.show',
                ['competition' => $competition, 'registration' => $submitted],
            );
        }

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

        $registration->refresh();
        $this->notifyAdminsOfCompetitionSubmission(
            'registration',
            $competition,
            $registration,
            $competition->name,
            'admin.competitions.registrations.show',
            ['competition' => $competition, 'registration' => $registration],
        );

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

        $existingIdea = $registration->idea;
        if ($existingIdea && $existingIdea->status === 'submitted') {
            return redirect()->route('client.competitions.show', $competition)
                ->withFragment('idea-submission')
                ->with('error', 'Your idea has already been submitted and can no longer be edited.');
        }

        $this->normalizeSubmitFinal($request);

        $rules = [
            'problem_title' => ['required', 'string', 'max:255'],
            'problem_statement' => ['required', 'string'],
            'proposed_solution' => ['required', 'string'],
            'stem_category' => ['required', 'string', 'max:120'],
            'stem_category_other' => ['nullable', 'string', 'max:120'],
            'community_region_id' => ['nullable', 'exists:regions,id'],
            'community_district_id' => [
                'nullable',
                'exists:districts,id',
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    if ($value === null || $value === '') {
                        return;
                    }

                    $regionId = $request->input('community_region_id');
                    if (! $regionId) {
                        $fail('Select a community region before choosing a district.');

                        return;
                    }

                    $belongsToRegion = District::query()
                        ->whereKey($value)
                        ->where('region_id', $regionId)
                        ->exists();

                    if (! $belongsToRegion) {
                        $fail('The selected district does not belong to the chosen region.');
                    }
                },
            ],
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

        $communityDistrictId = isset($validated['community_district_id'])
            ? (int) $validated['community_district_id']
            : null;
        $communityLocation = $communityDistrictId
            ? District::query()->whereKey($communityDistrictId)->value('name')
            : null;

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
            'community_district_id' => $communityDistrictId,
            'community_location' => $communityLocation,
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

            // Registration-phase pool eligibility already decided (or admin-overridden).
            // Do not re-block idea submission for schools that passed via admin override.
            if (! $registration->passedViaEligibilityOverride()) {
                $poolError = $this->competitionPoolEligibilityError($competition, $poolCount, $poolRatio);
                if ($poolError !== null) {
                    return back()->withErrors(['representative_student_ids' => $poolError])->withInput();
                }
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
                $idea->submitted_at = $idea->submitted_at ?? now();
                $idea->save();

                $this->replaceIdeaRepresentatives(
                    $registration,
                    $request,
                    $representatives->keyBy('id'),
                    $validated['representative_student_ids'],
                );
            });

            $this->sendIdeaSubmittedEmail($competition, $registration, $idea);
            $this->notifyAdminsOfCompetitionSubmission(
                'idea',
                $competition,
                $registration,
                (string) $idea->problem_title,
                'admin.competitions.ideas.show',
                ['competition' => $competition, 'idea' => $idea],
            );

            return redirect()
                ->route('client.competitions.show', $competition)
                ->withFragment('idea-submission')
                ->with('status', 'Idea submitted successfully. Check your email for confirmation — your school now waits for regional shortlisting approval.');
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

        $this->normalizeSubmitFinal($request);
        $submitting = $request->boolean('submit_final');
        $requiredText = $submitting ? ['required', 'string'] : ['nullable', 'string'];

        $validator = Validator::make($request->all(), [
            'prototype_title' => $submitting
                ? ['required', 'string', 'max:255']
                : ['nullable', 'string', 'max:255'],
            'prototype_concept' => $requiredText,
            'prototype_objectives' => $requiredText,
            'prototype_design_description' => $requiredText,
            'prototype_materials' => $requiredText,
            'prototype_build_process' => $requiredText,
            'prototype_challenges' => ['nullable', 'string'],
            'prototype_test_results' => ['nullable', 'string'],
            'prototype_next_steps' => ['nullable', 'string'],
            'submit_final' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('client.competitions.show', $competition)
                ->withFragment('prototype-development')
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();
        $wasSubmitted = $prototype->exists && $prototype->status === 'submitted';

        $attributes = [
            'competition_id' => $competition->id,
            'competition_phase_id' => $phase?->id,
            'competition_registration_id' => $registration->id,
            'competition_shortlist_id' => $shortlist?->id,
        ];

        foreach ([
            'prototype_title',
            'prototype_concept',
            'prototype_objectives',
            'prototype_design_description',
            'prototype_materials',
            'prototype_build_process',
            'prototype_challenges',
            'prototype_test_results',
            'prototype_next_steps',
        ] as $field) {
            if (! array_key_exists($field, $validated)) {
                continue;
            }

            $value = $validated[$field];
            if (is_string($value)) {
                $value = trim($value);
                $value = $value === '' ? null : $value;
            }

            $attributes[$field] = $value;
        }

        $prototype->fill($attributes);

        if ($submitting) {
            $prototype->status = 'submitted';
            $prototype->submitted_at = now();
            $prototype->submitted_by = $user->id;
        } elseif (! $wasSubmitted) {
            $prototype->status = 'draft';
        }

        $prototype->save();

        if ($submitting) {
            $this->notifyAdminsOfCompetitionSubmission(
                'prototype',
                $competition,
                $registration,
                (string) $prototype->prototype_title,
                'admin.competitions.prototype-shortlisting.show',
                ['competition' => $competition, 'submission' => $prototype],
            );

            return redirect()
                ->route('client.competitions.show', $competition)
                ->withFragment('prototype-development')
                ->with('status', 'Prototype submitted successfully. Awaiting prototype shortlisting.');
        }

        $draftStatus = $prototype->status === 'submitted'
            ? 'Prototype answers saved. It remains submitted.'
            : 'Prototype draft saved. It is not submitted yet.';

        return redirect()
            ->route('client.competitions.show', $competition)
            ->withFragment('prototype-development')
            ->with('status', $draftStatus);
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

        $this->normalizeSubmitFinal($request);

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
            $this->notifyAdminsOfCompetitionSubmission(
                'prototype_modification',
                $competition,
                $registration,
                (string) $modification->prototype_title,
                'admin.competitions.prototype-modification.show',
                ['competition' => $competition, 'modification' => $modification],
            );

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

    private function notifyAdminsOfCompetitionSubmission(
        string $kind,
        Competition $competition,
        CompetitionRegistration $registration,
        string $itemTitle,
        string $routeName,
        array $routeParameters,
    ): void {
        app(AdminActivityNotifier::class)->competitionSubmitted(
            $kind,
            $competition,
            $registration,
            $itemTitle,
            AdminUrl::route($routeName, $routeParameters),
        );
    }

    private function sendIdeaSubmittedEmail(
        Competition $competition,
        CompetitionRegistration $registration,
        CompetitionIdea $idea,
    ): void {
        $competitionUrl = ClientUrl::route('client.competitions.show', [
            'competition' => $competition,
        ]).'?phase=idea_submission#idea-submission';

        $teacherEmail = $registration->resolvedTeacherEmail();
        if (filled($teacherEmail)) {
            try {
                Mail::to($teacherEmail)->send(
                    new CompetitionIdeaSubmittedMail($competition, $registration, $idea, $competitionUrl)
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $this->sendIdeaSubmittedSms($competition, $registration, $idea);
    }

    private function sendIdeaSubmittedSms(
        Competition $competition,
        CompetitionRegistration $registration,
        CompetitionIdea $idea,
    ): void {
        $smsUrl = $competition->smsUrl();
        $message = CompetitionSmsTemplates::render(
            CompetitionSmsTemplates::template(CompetitionSmsTemplates::KEY_IDEA_SUBMITTED),
            [
                'teacher_name' => $registration->resolvedTeacherName() ?: 'Teacher',
                'school_name' => $registration->resolvedSchoolName(),
                'competition_name' => $competition->name,
                'url' => $smsUrl,
                'short_url' => $smsUrl,
            ]
        );

        app(SmsNotifier::class)->send(
            $registration->resolvedTeacherPhone(),
            $message,
            CompetitionSmsTemplates::defaultSenderId(),
            'idea-submitted-'.$idea->getKey()
        );
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

    /**
     * Disabled submit buttons are omitted from POST; some browsers also send both
     * Save draft and Submit values. Normalize to a single boolean flag.
     */
    private function normalizeSubmitFinal(Request $request): void
    {
        $request->merge([
            'submit_final' => $this->wantsFinalSubmit($request) ? '1' : '0',
        ]);
    }

    private function wantsFinalSubmit(Request $request): bool
    {
        $value = $request->input('submit_final');
        if (is_array($value)) {
            $value = end($value);
        }
        if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            return true;
        }

        $intent = $request->input('submit_intent');
        if (is_array($intent)) {
            $intent = end($intent);
        }

        return $intent === 'submit';
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
