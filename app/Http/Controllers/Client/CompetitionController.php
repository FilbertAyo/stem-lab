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
use App\Models\Region;
use App\Models\SchoolClass;
use App\Models\TeacherClub;
use App\Services\CompetitionParticipantRegistrationService;
use App\Services\StudentCsvBulkImportService;
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

        $registrations = CompetitionRegistration::query()
            ->where('teacher_user_id', $user->id)
            ->with(['competition', 'idea'])
            ->get()
            ->keyBy('competition_id');

        return view('client.back.competitions.index', compact(
            'competitions',
            'registrations',
            'teacherClubByClubId',
            'highlightCompetitionUlid',
        ));
    }

    public function show(Competition $competition): View
    {
        $user = Auth::user();
        $teacherClub = $this->resolveTeacherClubForCompetition($user->id, $competition->club_id);
        $registration = null;
        $idea = null;
        $poolStudents = collect();

        if ($teacherClub) {
            $registration = CompetitionRegistration::query()
                ->where('competition_id', $competition->id)
                ->where('teacher_club_id', $teacherClub->id)
                ->with([
                    'representativeStudents.student.schoolClass',
                    'shortlist',
                    'region',
                    'school.regionModel',
                ])
                ->first();
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
            $allowedClassIds = $competition->classRequirements
                ->pluck('school_class_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            $poolStudents = $teacherClub->students()
                ->with('schoolClass')
                ->when(! empty($allowedClassIds), fn ($query) => $query->whereIn('school_class_id', $allowedClassIds))
                ->orderBy('name')
                ->get();
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

    public function register(Competition $competition): View|\Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        $teacherClub = $this->resolveTeacherClubForCompetition($user->id, $competition->club_id, true);

        if (! $teacherClub) {
            abort(403, 'You must join this club first before registering for competitions.');
        }

        $registration = CompetitionRegistration::where('teacher_club_id', $teacherClub->id)
            ->where('competition_id', $competition->id)
            ->first();

        if ($registration) {
            if (in_array($registration->status, ['pending', 'approved'], true)) {
                $message = $registration->status === 'pending'
                    ? 'Your registration is already submitted and is awaiting admin review.'
                    : 'Your registration has already been approved.';

                return redirect()->route('client.competitions.show', $competition)
                    ->with('status', $message);
            }
            if (! in_array($registration->status, ['draft', 'rejected'], true)) {
                return redirect()->route('client.competitions.show', $competition)
                    ->with('error', 'You cannot edit registration in its current state.');
            }
        }

        if (! $competition->isPhaseOpen('registration')) {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'Registration phase is currently closed.');
        }

        $competition->loadMissing(['classRequirements.schoolClass']);

        $allowedClassIds = $competition->classRequirements()->pluck('school_class_id')->all();
        $students = $teacherClub->students()
            ->with('schoolClass')
            ->when(! empty($allowedClassIds), fn ($query) => $query->whereIn('school_class_id', $allowedClassIds))
            ->orderBy('name')
            ->get();

        $classesQuery = SchoolClass::query()->where('is_active', true)->orderBy('name');
        $classes = ! empty($allowedClassIds)
            ? $classesQuery->whereIn('id', $allowedClassIds)->get()
            : $classesQuery->get();

        return view('client.back.competitions.register', compact(
            'competition',
            'teacherClub',
            'students',
            'allowedClassIds',
            'classes',
            'registration',
        ));
    }

    public function bulkImportStudents(
        Request $request,
        Competition $competition,
        StudentCsvBulkImportService $csvImport
    ): RedirectResponse {
        $user = Auth::user();
        $teacherClub = $this->resolveTeacherClubForCompetition($user->id, $competition->club_id, true);

        if (! $teacherClub) {
            return redirect()->route('client.competitions.index')
                ->with('error', 'You must join this club first.');
        }

        if (! $competition->isPhaseOpen('registration')) {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'Registration phase is currently closed.');
        }

        $validated = $request->validate([
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
            ->with('status', "Successfully imported {$result['success_count']} student(s). Refresh the eligibility panel if counts look stale.");
    }

    public function clearPoolStudents(Competition $competition): RedirectResponse
    {
        $user = Auth::user();
        $teacherClub = $this->resolveTeacherClubForCompetition($user->id, $competition->club_id, true);

        if (! $teacherClub) {
            return redirect()->route('client.competitions.index')
                ->with('error', 'You must join this club first.');
        }

        if (! $competition->isPhaseOpen('registration')) {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'Registration phase is currently closed.');
        }

        $allowedClassIds = $competition->classRequirements()->pluck('school_class_id')->all();

        $query = $teacherClub->students()
            ->when(! empty($allowedClassIds), fn ($builder) => $builder->whereIn('school_class_id', $allowedClassIds));

        $deleted = (clone $query)->count();
        $query->delete();

        return redirect()
            ->route('client.competitions.register', $competition)
            ->with('status', "Removed {$deleted} student(s) from this competition pool. You can upload a corrected CSV now.");
    }

    /**
     * Download a CSV template with headers and one example row (same format as bulk import).
     */
    public function downloadRegistrationCsvTemplate(Competition $competition): StreamedResponse|RedirectResponse
    {
        $user = Auth::user();
        $teacherClub = $this->resolveTeacherClubForCompetition($user->id, $competition->club_id, true);

        if (! $teacherClub) {
            return redirect()->route('client.competitions.index')
                ->with('error', 'You must join this club first.');
        }

        if (! $competition->isPhaseOpen('registration')) {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'Registration phase is currently closed.');
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

    public function storeRegistration(Request $request, Competition $competition): RedirectResponse
    {
        $user = Auth::user();
        $teacherClub = $this->resolveTeacherClubForCompetition($user->id, $competition->club_id, true);

        if (! $teacherClub) {
            return redirect()->route('client.competitions.index')
                ->with('error', 'You must join this club first.');
        }

        if (! $competition->isPhaseOpen('registration')) {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'Registration phase is currently closed.');
        }

        $existing = CompetitionRegistration::where('teacher_club_id', $teacherClub->id)
            ->where('competition_id', $competition->id)
            ->first();

        if ($existing && ! in_array($existing->status, ['draft', 'rejected'], true)) {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'You have already registered for this competition.');
        }

        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'school_district' => ['nullable', 'string', 'max:255'],
            'school_ward' => ['nullable', 'string', 'max:255'],
        ]);

        $allowedClassIds = $competition->classRequirements()->pluck('school_class_id')->all();
        $poolStudents = $teacherClub->students()
            ->when(! empty($allowedClassIds), fn ($query) => $query->whereIn('school_class_id', $allowedClassIds))
            ->get();

        $poolCount = $poolStudents->count();
        $poolGirlsCount = $poolStudents->where('gender', 'female')->count();
        $poolRatio = $poolCount > 0 ? ($poolGirlsCount / $poolCount) * 100 : 0;

        $poolError = $this->competitionPoolEligibilityError($competition, $poolCount, $poolRatio);
        if ($poolError !== null) {
            return back()->withErrors(['eligibility' => $poolError])->withInput();
        }

        DB::transaction(function () use ($validated, $competition, $teacherClub, $user, $poolCount, $poolGirlsCount, $poolRatio, $existing): void {
            if ($existing) {
                $existing->representativeStudents()->delete();
                $existing->update([
                    'status' => 'pending',
                    'school_id' => $teacherClub->school_id,
                    'region_id' => $teacherClub->school?->region_id,
                    'teacher_name' => $user->name,
                    'teacher_email' => $user->email,
                    'teacher_phone' => $user->phone,
                    'school_name' => $validated['school_name'],
                    'school_district' => $validated['school_district'] ?? $teacherClub->school?->district,
                    'school_ward' => $validated['school_ward'] ?? $teacherClub->school?->ward,
                    'student_count_snapshot' => $poolCount,
                    'girls_count_snapshot' => $poolGirlsCount,
                    'girls_ratio_snapshot' => round($poolRatio, 2),
                    'eligibility_met' => true,
                    'submitted_at' => now(),
                    'rejection_reason' => null,
                ]);

                return;
            }

            CompetitionRegistration::create([
                'competition_id' => $competition->id,
                'teacher_club_id' => $teacherClub->id,
                'teacher_user_id' => $user->id,
                'school_id' => $teacherClub->school_id,
                'region_id' => $teacherClub->school?->region_id,
                'status' => 'pending',
                'teacher_name' => $user->name,
                'teacher_email' => $user->email,
                'teacher_phone' => $user->phone,
                'school_name' => $validated['school_name'],
                'school_district' => $validated['school_district'] ?? $teacherClub->school?->district,
                'school_ward' => $validated['school_ward'] ?? $teacherClub->school?->ward,
                'student_count_snapshot' => $poolCount,
                'girls_count_snapshot' => $poolGirlsCount,
                'girls_ratio_snapshot' => round($poolRatio, 2),
                'eligibility_met' => true,
                'submitted_at' => now(),
            ]);
        });

        return redirect()
            ->route('client.competitions.show', $competition)
            ->with('status', 'Registration submitted successfully. Wait for Judges to review your registration and you will be notified.');
    }

    public function registerRepresentatives(Competition $competition): View|\Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        $teacherClub = $this->resolveTeacherClubForCompetition($user->id, $competition->club_id, true);

        if (! $teacherClub) {
            abort(403, 'You must join this club first before registering for competitions.');
        }

        $registration = CompetitionRegistration::where('teacher_club_id', $teacherClub->id)
            ->where('competition_id', $competition->id)
            ->first();

        if (! $registration || $registration->status !== 'draft') {
            return redirect()->route('client.competitions.register', $competition)
                ->with('error', 'Save your participant list and school details first, or your registration is already submitted.');
        }

        if (! $competition->isPhaseOpen('registration')) {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'Registration phase is currently closed.');
        }

        $allowedClassIds = $competition->classRequirements()->pluck('school_class_id')->all();
        $students = $teacherClub->students()
            ->with('schoolClass')
            ->when(! empty($allowedClassIds), fn ($query) => $query->whereIn('school_class_id', $allowedClassIds))
            ->orderBy('name')
            ->get();

        return view('client.back.competitions.register-representatives', compact(
            'competition',
            'teacherClub',
            'registration',
            'students',
            'allowedClassIds',
        ));
    }

    public function storeRegistrationRepresentatives(Request $request, Competition $competition): RedirectResponse
    {
        $user = Auth::user();
        $teacherClub = $this->resolveTeacherClubForCompetition($user->id, $competition->club_id, true);

        if (! $teacherClub) {
            return redirect()->route('client.competitions.index')
                ->with('error', 'You must join this club first.');
        }

        if (! $competition->isPhaseOpen('registration')) {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'Registration phase is currently closed.');
        }

        $registration = CompetitionRegistration::where('teacher_club_id', $teacherClub->id)
            ->where('competition_id', $competition->id)
            ->first();

        if (! $registration || $registration->status !== 'draft') {
            return redirect()->route('client.competitions.show', $competition)
                ->with('error', 'No draft registration found to finalize.');
        }

        $repCount = $this->requiredRepresentativeCount($competition);

        $validated = $request->validate([
            'representative_student_ids' => ['required', 'array', 'size:'.$repCount],
            'representative_student_ids.*' => ['exists:students,id'],
            'student_roles' => ['nullable', 'array'],
        ]);

        $allowedClassIds = $competition->classRequirements()->pluck('school_class_id')->all();
        $poolStudents = $teacherClub->students()
            ->when(! empty($allowedClassIds), fn ($query) => $query->whereIn('school_class_id', $allowedClassIds))
            ->get();

        $poolCount = $poolStudents->count();
        $poolGirlsCount = $poolStudents->where('gender', 'female')->count();
        $poolRatio = $poolCount > 0 ? ($poolGirlsCount / $poolCount) * 100 : 0;

        $poolError = $this->competitionPoolEligibilityError($competition, $poolCount, $poolRatio);
        if ($poolError !== null) {
            return back()->withErrors(['representative_student_ids' => $poolError])->withInput();
        }

        $representatives = $teacherClub->students()
            ->when(! empty($allowedClassIds), fn ($query) => $query->whereIn('school_class_id', $allowedClassIds))
            ->whereIn('id', $validated['representative_student_ids'])
            ->get();

        if ($representatives->count() !== $repCount) {
            return back()->withErrors(['representative_student_ids' => "Please select exactly {$repCount} valid representatives."])->withInput();
        }

        $repGirlsRatio = ($representatives->where('gender', 'female')->count() / $repCount) * 100;
        if ($repGirlsRatio < 70) {
            return back()->withErrors([
                'representative_student_ids' => "At least 70% of the {$repCount} selected representatives must be girls.",
            ])->withInput();
        }

        DB::transaction(function () use ($registration, $representatives, $request, $poolCount, $poolGirlsCount, $poolRatio): void {
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
        $registration = CompetitionRegistration::query()
            ->where('teacher_club_id', $teacherClub?->id)
            ->where('competition_id', $competition->id)
            ->first();

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
        $registration = CompetitionRegistration::query()
            ->where('teacher_club_id', $teacherClub?->id)
            ->where('competition_id', $competition->id)
            ->first();

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
            $allowedClassIds = $competition->classRequirements()->pluck('school_class_id')->all();
            $repCount = $this->requiredRepresentativeCount($competition);

            $poolStudents = $teacherClub->students()
                ->when(! empty($allowedClassIds), fn ($query) => $query->whereIn('school_class_id', $allowedClassIds))
                ->get();

            $poolCount = $poolStudents->count();
            $poolGirlsCount = $poolStudents->where('gender', 'female')->count();
            $poolRatio = $poolCount > 0 ? ($poolGirlsCount / $poolCount) * 100 : 0;

            $poolError = $this->competitionPoolEligibilityError($competition, $poolCount, $poolRatio);
            if ($poolError !== null) {
                return back()->withErrors(['representative_student_ids' => $poolError])->withInput();
            }

            $representatives = $teacherClub->students()
                ->when(! empty($allowedClassIds), fn ($query) => $query->whereIn('school_class_id', $allowedClassIds))
                ->whereIn('id', $validated['representative_student_ids'])
                ->get();

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
            $allowedClassIds = $competition->classRequirements()->pluck('school_class_id')->all();

            $ids = array_values(array_unique(array_map(
                'intval',
                (array) ($validated['representative_student_ids'] ?? []),
            )));

            if (count($ids) > $repCount) {
                return back()->withErrors([
                    'representative_student_ids' => "You can select at most {$repCount} representatives for this competition.",
                ])->withInput();
            }

            $studentsById = $teacherClub->students()
                ->when(! empty($allowedClassIds), fn ($query) => $query->whereIn('school_class_id', $allowedClassIds))
                ->whereIn('id', $ids)
                ->get()
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
        $registration = CompetitionRegistration::query()
            ->where('teacher_club_id', $teacherClub?->id)
            ->where('competition_id', $competition->id)
            ->first();

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
        $registration = CompetitionRegistration::query()
            ->where('teacher_club_id', $teacherClub?->id)
            ->where('competition_id', $competition->id)
            ->first();

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
        $registration = CompetitionRegistration::query()
            ->where('teacher_club_id', $teacherClub?->id)
            ->where('competition_id', $competition->id)
            ->first();

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
        $registration = CompetitionRegistration::query()
            ->where('teacher_club_id', $teacherClub?->id)
            ->where('competition_id', $competition->id)
            ->first();

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
        return [
            'min' => max(1, (int) $competition->min_members),
            'max' => $competition->max_members !== null ? (int) $competition->max_members : null,
        ];
    }

    private function competitionPoolEligibilityError(
        Competition $competition,
        int $poolCount,
        float $poolRatio
    ): ?string {
        $bounds = $this->competitionPoolBounds($competition);
        if ($poolCount < $bounds['min']) {
            return "Eligibility gate failed. You need at least {$bounds['min']} students in the competition class pool (set by the competition Min members field). Upload members via CSV if needed.";
        }
        if ($bounds['max'] !== null && $poolCount > $bounds['max']) {
            return "Eligibility gate failed. The pool may not exceed {$bounds['max']} students (competition Max members).";
        }
        if ($poolRatio < 70) {
            return 'Eligibility gate failed. At least 70% of the pool must be girls.';
        }

        return null;
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
