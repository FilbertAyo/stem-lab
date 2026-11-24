<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CompetitionEligibilityReminderMail;
use App\Mail\CompetitionIdeaShortlistedMail;
use App\Mail\CompetitionRegistrationApprovedMail;
use App\Models\Club;
use App\Models\Competition;
use App\Models\CompetitionClassRequirement;
use App\Models\CompetitionIdea;
use App\Models\CompetitionIdeaScore;
use App\Models\CompetitionPhase;
use App\Models\CompetitionPrototypeModification;
use App\Models\CompetitionPrototypeModificationShortlist;
use App\Models\CompetitionPrototypeShortlist;
use App\Models\CompetitionPrototypeShortlistMentor;
use App\Models\CompetitionPrototypeSubmission;
use App\Models\CompetitionRegistration;
use App\Models\CompetitionShortlist;
use App\Models\CompetitionStageEvent;
use App\Models\District;
use App\Models\Region;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\TeacherClub;
use App\Services\CompetitionEligibilityPoolService;
use App\Services\CompetitionParticipantRegistrationService;
use App\Services\Sms\CompetitionSmsTemplates;
use App\Services\Sms\SmsNotifier;
use App\Support\ClientUrl;
use App\Support\TanzaniaPhone;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompetitionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Competition::query()->with([
            'club',
            'creator',
            'phases' => fn ($phases) => $phases->orderBy('sequence'),
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('club_id')) {
            $query->where('club_id', (int) $request->input('club_id'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where('name', 'like', '%'.$search.'%');
        }

        $competitions = $query->latest()->paginate(15)->withQueryString();
        $clubs = Club::active()->ordered()->get();

        return view('admin.competitions.index', compact('competitions', 'clubs'));
    }

    public function create(): View
    {
        $clubs = Club::active()->ordered()->get();
        $classes = SchoolClass::active()->ordered()->get();
        $oldPhases = old('phases', []);

        return view('admin.competitions.create', compact('clubs', 'classes', 'oldPhases'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'terms_and_conditions' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
            'club_id' => ['required', 'exists:clubs,id'],
            'representative_count' => ['required', 'integer', 'min:1'],
            'min_members' => ['required', 'integer', 'min:1', 'gte:representative_count'],
            'max_members' => ['nullable', 'integer', 'min:1', 'gte:min_members'],
            'min_age' => ['nullable', 'integer', 'min:1', 'max:100'],
            'max_age' => ['nullable', 'integer', 'min:1', 'max:100', 'gte:min_age'],
            'registration_start' => ['nullable', 'date'],
            'registration_deadline' => ['nullable', 'date', 'after_or_equal:registration_start'],
            'competition_start' => ['required', 'date'],
            'competition_end' => ['required', 'date', 'after:competition_start'],
            'results_announcement_date' => ['nullable', 'date', 'after_or_equal:competition_end'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'is_active' => ['nullable', 'boolean'],
            'class_ids' => ['nullable', 'array'],
            'class_ids.*' => ['exists:school_classes,id'],
            'phases' => ['nullable', 'array', 'min:1'],
            'phases.*.name' => ['required_with:phases', 'string', 'max:120'],
            'phases.*.start_at' => ['nullable', 'date'],
            'phases.*.deadline_at' => ['nullable', 'date'],
            'phases.*.description' => ['nullable', 'string'],
            'phases.*.status' => ['required_with:phases', Rule::in(['upcoming', 'open', 'closed', 'results_pending'])],
        ]);

        $validated['slug'] = $this->generateUniqueSlug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['created_by'] = $request->user()?->getKey();

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('competitions', 'public');
        }

        $competition = Competition::create($validated);
        $this->syncCompetitionClasses($competition, $request->input('class_ids', []));
        $this->syncCompetitionPhases($competition, $request->input('phases', []));
        $this->logStageEvent($competition, 'competition_created', 'Competition created and phase journey initialized.');

        return redirect()
            ->route('admin.competitions.show', $competition)
            ->with('status', 'Competition created successfully.');
    }

    public function show(Competition $competition): View
    {
        $competition->load([
            'club',
            'creator',
            'phases',
            'classRequirements.schoolClass',
        ]);

        return view('admin.competitions.show', compact('competition'));
    }

    public function prototypeShortlistingIndex(Competition $competition): View|RedirectResponse
    {
        $phaseId = $competition->phases()
            ->where('phase_key', 'prototype_shortlisting')
            ->value('id');

        if (! $phaseId) {
            return redirect()
                ->route('admin.competitions.show', $competition)
                ->with('error', 'Prototype shortlisting phase is not configured for this competition.');
        }

        $judgeUserId = request()->user()?->getKey();
        if (! $judgeUserId) {
            abort(403);
        }

        $assignments = \App\Models\CompetitionJudgeAssignment::query()
            ->where('competition_id', $competition->id)
            ->where('competition_phase_id', $phaseId)
            ->where('judge_user_id', $judgeUserId)
            ->where('is_active', true)
            ->get();

        $regionIds = $assignments
            ->pluck('region_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $submissionsQuery = CompetitionPrototypeSubmission::query()
            ->where('competition_id', $competition->id)
            ->where('status', 'submitted')
            ->whereHas('idea.shortlist', function ($q): void {
                $q->where('status', 'shortlisted');
            })
            ->with([
                'idea',
                'registration.region',
                'registration.school',
                'submittedBy',
                'competition',
                'prototypeShortlist',
            ])
            ->orderByDesc('submitted_at');

        if ($regionIds !== []) {
            $submissionsQuery->whereHas('registration', function ($q) use ($regionIds): void {
                $q->whereIn('region_id', $regionIds);
            });
        }

        $submissions = $submissionsQuery->get();

        return view('admin.competitions.prototype_shortlisting.index', compact(
            'competition',
            'phaseId',
            'assignments',
            'regionIds',
            'submissions'
        ));
    }

    public function prototypeShortlistingShow(
        Competition $competition,
        CompetitionPrototypeSubmission $submission
    ): View|RedirectResponse {
        if ($submission->competition_id !== $competition->id) {
            abort(404);
        }

        $phaseId = $competition->phases()
            ->where('phase_key', 'prototype_shortlisting')
            ->value('id');

        if (! $phaseId) {
            return redirect()
                ->route('admin.competitions.prototype-shortlisting.index', $competition)
                ->with('error', 'Prototype shortlisting phase is not configured for this competition.');
        }

        $judgeUserId = request()->user()?->getKey();
        if (! $judgeUserId) {
            abort(403);
        }

        // Admins should be able to review everything without being explicitly assigned as a judge.
        $isAdmin = request()->user()?->role === 'admin';
        if (! $isAdmin) {
            $regionId = $submission->registration?->region_id;
            $isAssigned = \App\Models\CompetitionJudgeAssignment::query()
                ->where('competition_id', $competition->id)
                ->where('competition_phase_id', $phaseId)
                ->where('judge_user_id', $judgeUserId)
                ->where('is_active', true)
                ->when($regionId !== null, fn ($q) => $q->where('region_id', $regionId))
                ->exists();

            if (! $isAssigned) {
                abort(403);
            }
        }

        $submission->load([
            'idea',
            'registration.region',
            'registration.school',
            'submittedBy',
            'images',
            'prototypeShortlist',
            'prototypeShortlist.mentors',
        ]);

        return view('admin.competitions.prototype_shortlisting.show', compact(
            'competition',
            'submission',
            'phaseId'
        ));
    }

    public function storePrototypeMentor(
        Request $request,
        Competition $competition,
        CompetitionPrototypeSubmission $submission
    ): RedirectResponse {
        if ($submission->competition_id !== $competition->id) {
            abort(404);
        }

        $phaseId = $competition->phases()
            ->where('phase_key', 'mentor_onboarding')
            ->value('id');

        if (! $phaseId) {
            return back()->with('error', 'Mentor onboarding phase is not configured for this competition.');
        }

        $user = request()->user();
        if (! $user) {
            abort(403);
        }

        // Only allow creating mentors when the prototype has been shortlisted.
        $shortlist = CompetitionPrototypeShortlist::query()
            ->where('competition_prototype_submission_id', $submission->id)
            ->first();

        if (! $shortlist || $shortlist->status !== 'shortlisted') {
            return back()->with('error', 'Mentors can only be added after the prototype is shortlisted.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'picture_url' => ['nullable', 'string', 'max:2048'],
        ]);

        CompetitionPrototypeShortlistMentor::create([
            'competition_id' => $competition->id,
            'competition_prototype_shortlist_id' => $shortlist->id,
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'picture_url' => $validated['picture_url'] ?? null,
            'created_by' => $user->getKey(),
        ]);

        return back()->with('status', 'Mentor added successfully.');
    }

    public function shortlistPrototype(
        Request $request,
        Competition $competition,
        CompetitionPrototypeSubmission $submission
    ): RedirectResponse {
        if ($submission->competition_id !== $competition->id) {
            abort(404);
        }

        $phaseId = $competition->phases()
            ->where('phase_key', 'prototype_shortlisting')
            ->value('id');

        if (! $phaseId) {
            abort(404);
        }

        $judgeUserId = request()->user()?->getKey();
        if (! $judgeUserId) {
            abort(403);
        }

        $regionId = $submission->registration?->region_id;
        $isAdmin = request()->user()?->role === 'admin';
        if (! $isAdmin) {
            $isAssigned = \App\Models\CompetitionJudgeAssignment::query()
                ->where('competition_id', $competition->id)
                ->where('competition_phase_id', $phaseId)
                ->where('judge_user_id', $judgeUserId)
                ->where('is_active', true)
                ->when($regionId !== null, fn ($q) => $q->where('region_id', $regionId))
                ->exists();

            if (! $isAssigned) {
                abort(403);
            }
        }

        $validated = $request->validate([
            'status' => ['required', 'in:shortlisted,waitlist,rejected'],
            'rank' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'feedback' => ['nullable', 'string', 'max:5000'],
        ]);

        CompetitionPrototypeShortlist::updateOrCreate(
            ['competition_prototype_submission_id' => $submission->id],
            [
                'competition_id' => $competition->id,
                'competition_phase_id' => $phaseId,
                'region_id' => $regionId,
                'rank' => $validated['rank'] ?? null,
                'status' => $validated['status'],
                'decided_by' => request()->user()?->getKey(),
                'decided_at' => now(),
                'feedback' => $validated['feedback'] ?? null,
            ]
        );

        $this->logStageEvent(
            $competition,
            'prototype_shortlist_decision',
            'Prototype shortlist updated.',
            ['prototype_submission_ulid' => $submission->ulid, 'status' => $validated['status']]
        );

        return back()->with('status', 'Prototype shortlist decision saved.');
    }

    public function prototypeModificationIndex(Competition $competition): View|RedirectResponse
    {
        $phaseId = $competition->phases()
            ->where('phase_key', 'prototype_modification')
            ->value('id');

        if (! $phaseId) {
            return redirect()
                ->route('admin.competitions.show', $competition)
                ->with('error', 'Prototype modification phase is not configured for this competition.');
        }

        $judgeUserId = request()->user()?->getKey();
        if (! $judgeUserId) {
            abort(403);
        }

        $assignments = \App\Models\CompetitionJudgeAssignment::query()
            ->where('competition_id', $competition->id)
            ->where('competition_phase_id', $phaseId)
            ->where('judge_user_id', $judgeUserId)
            ->where('is_active', true)
            ->get();

        $regionIds = $assignments->pluck('region_id')->filter()->unique()->values()->all();

        $modsQuery = CompetitionPrototypeModification::query()
            ->where('competition_id', $competition->id)
            ->where('status', 'submitted')
            ->whereHas('idea.prototypeSubmission.prototypeShortlist', function ($q): void {
                $q->where('status', 'shortlisted');
            })
            ->with([
                'idea',
                'registration.region',
                'registration.school',
                'submittedBy',
                'modificationShortlist',
            ])
            ->orderByDesc('submitted_at');

        if ($regionIds !== []) {
            $modsQuery->whereHas('registration', function ($q) use ($regionIds): void {
                $q->whereIn('region_id', $regionIds);
            });
        }

        $modifications = $modsQuery->get();

        return view('admin.competitions.prototype_modification.index', compact(
            'competition',
            'phaseId',
            'assignments',
            'regionIds',
            'modifications'
        ));
    }

    public function prototypeModificationShow(
        Competition $competition,
        CompetitionPrototypeModification $modification
    ): View|RedirectResponse {
        if ($modification->competition_id !== $competition->id) {
            abort(404);
        }

        $phaseId = $competition->phases()
            ->where('phase_key', 'prototype_modification')
            ->value('id');

        if (! $phaseId) {
            return redirect()
                ->route('admin.competitions.prototype-modification.index', $competition)
                ->with('error', 'Prototype modification phase is not configured for this competition.');
        }

        $judgeUserId = request()->user()?->getKey();
        if (! $judgeUserId) {
            abort(403);
        }

        $isAdmin = request()->user()?->role === 'admin';
        if (! $isAdmin) {
            $regionId = $modification->registration?->region_id;
            $isAssigned = \App\Models\CompetitionJudgeAssignment::query()
                ->where('competition_id', $competition->id)
                ->where('competition_phase_id', $phaseId)
                ->where('judge_user_id', $judgeUserId)
                ->where('is_active', true)
                ->when($regionId !== null, fn ($q) => $q->where('region_id', $regionId))
                ->exists();

            if (! $isAssigned) {
                abort(403);
            }
        }

        $modification->load([
            'idea',
            'registration.region',
            'registration.school',
            'submittedBy',
            'prototypeSubmission',
            'images',
            'modificationShortlist',
        ]);

        return view('admin.competitions.prototype_modification.show', compact(
            'competition',
            'modification',
            'phaseId'
        ));
    }

    public function shortlistPrototypeModification(
        Request $request,
        Competition $competition,
        CompetitionPrototypeModification $modification
    ): RedirectResponse {
        if ($modification->competition_id !== $competition->id) {
            abort(404);
        }

        $phaseId = $competition->phases()
            ->where('phase_key', 'prototype_modification')
            ->value('id');

        if (! $phaseId) {
            abort(404);
        }

        $judgeUserId = request()->user()?->getKey();
        if (! $judgeUserId) {
            abort(403);
        }

        $regionId = $modification->registration?->region_id;
        $isAdmin = request()->user()?->role === 'admin';
        if (! $isAdmin) {
            $isAssigned = \App\Models\CompetitionJudgeAssignment::query()
                ->where('competition_id', $competition->id)
                ->where('competition_phase_id', $phaseId)
                ->where('judge_user_id', $judgeUserId)
                ->where('is_active', true)
                ->when($regionId !== null, fn ($q) => $q->where('region_id', $regionId))
                ->exists();

            if (! $isAssigned) {
                abort(403);
            }
        }

        $validated = $request->validate([
            'status' => ['required', 'in:shortlisted,waitlist,rejected'],
            'rank' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'feedback' => ['nullable', 'string', 'max:5000'],
        ]);

        CompetitionPrototypeModificationShortlist::updateOrCreate(
            ['competition_prototype_modification_id' => $modification->id],
            [
                'competition_id' => $competition->id,
                'competition_phase_id' => $phaseId,
                'region_id' => $regionId,
                'rank' => $validated['rank'] ?? null,
                'status' => $validated['status'],
                'decided_by' => request()->user()?->getKey(),
                'decided_at' => now(),
                'feedback' => $validated['feedback'] ?? null,
            ]
        );

        $this->logStageEvent(
            $competition,
            'prototype_modification_shortlist_decision',
            'Prototype modification shortlist updated.',
            ['prototype_modification_ulid' => $modification->ulid, 'status' => $validated['status']]
        );

        return back()->with('status', 'Prototype modification shortlist decision saved.');
    }

    public function pitchWinnerIndex(Competition $competition): View|RedirectResponse
    {
        $phaseId = $competition->phases()
            ->where('phase_key', 'pitch_winner')
            ->value('id');

        if (! $phaseId) {
            return redirect()
                ->route('admin.competitions.show', $competition)
                ->with('error', 'Pitch & winner phase is not configured for this competition.');
        }

        $calledTeams = CompetitionPrototypeModification::query()
            ->where('competition_id', $competition->id)
            ->where('status', 'submitted')
            ->whereHas('modificationShortlist', function ($q): void {
                $q->where('status', 'shortlisted');
            })
            ->with([
                'registration.region',
                'registration.school',
                'idea',
                'modificationShortlist',
            ])
            ->orderByDesc('submitted_at')
            ->get();

        return view('admin.competitions.pitch_winner.index', compact(
            'competition',
            'phaseId',
            'calledTeams'
        ));
    }

    public function edit(Competition $competition): View
    {
        $clubs = Club::active()->ordered()->get();
        $classes = SchoolClass::active()->ordered()->get();
        $oldPhases = old('phases', $competition->phases()
            ->orderBy('sequence')
            ->get()
            ->map(function (CompetitionPhase $phase) {
                return [
                    'name' => $phase->name,
                    'start_at' => optional($phase->opens_at)->format('Y-m-d\TH:i'),
                    'deadline_at' => optional($phase->closes_at)->format('Y-m-d\TH:i'),
                    'description' => $phase->description,
                    'status' => $phase->status,
                ];
            })
            ->toArray());

        return view('admin.competitions.edit', compact(
            'competition',
            'clubs',
            'classes',
            'oldPhases'
        ));
    }

    public function update(Request $request, Competition $competition): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'terms_and_conditions' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
            'club_id' => ['required', 'exists:clubs,id'],
            'representative_count' => ['required', 'integer', 'min:1'],
            'min_members' => ['required', 'integer', 'min:1', 'gte:representative_count'],
            'max_members' => ['nullable', 'integer', 'min:1', 'gte:min_members'],
            'min_age' => ['nullable', 'integer', 'min:1', 'max:100'],
            'max_age' => ['nullable', 'integer', 'min:1', 'max:100', 'gte:min_age'],
            'registration_start' => ['nullable', 'date'],
            'registration_deadline' => ['nullable', 'date', 'after_or_equal:registration_start'],
            'competition_start' => ['required', 'date'],
            'competition_end' => ['required', 'date', 'after:competition_start'],
            'results_announcement_date' => ['nullable', 'date', 'after_or_equal:competition_end'],
            'status' => ['required', Rule::in(['draft', 'published', 'ongoing', 'judging', 'completed', 'cancelled'])],
            'is_active' => ['nullable', 'boolean'],
            'class_ids' => ['nullable', 'array'],
            'class_ids.*' => ['exists:school_classes,id'],
            'phases' => ['nullable', 'array', 'min:1'],
            'phases.*.name' => ['required_with:phases', 'string', 'max:120'],
            'phases.*.start_at' => ['nullable', 'date'],
            'phases.*.deadline_at' => ['nullable', 'date'],
            'phases.*.description' => ['nullable', 'string'],
            'phases.*.status' => ['required_with:phases', Rule::in(['upcoming', 'open', 'closed', 'results_pending'])],
        ]);

        if ($validated['name'] !== $competition->name) {
            $validated['slug'] = $this->generateUniqueSlug($validated['name'], $competition->id);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('cover_image')) {
            if ($competition->cover_image && Storage::disk('public')->exists($competition->cover_image)) {
                Storage::disk('public')->delete($competition->cover_image);
            }

            $validated['cover_image'] = $request->file('cover_image')->store('competitions', 'public');
        }

        $competition->update($validated);
        $this->syncCompetitionClasses($competition, $request->input('class_ids', []));
        $this->syncCompetitionPhases($competition, $request->input('phases', []));
        $this->logStageEvent($competition, 'competition_updated', 'Competition details updated.');

        return redirect()
            ->route('admin.competitions.show', $competition)
            ->with('status', 'Competition updated successfully.');
    }

    public function destroy(Competition $competition): RedirectResponse
    {
        if ($competition->cover_image && Storage::disk('public')->exists($competition->cover_image)) {
            Storage::disk('public')->delete($competition->cover_image);
        }

        $competition->delete();

        return redirect()
            ->route('admin.competitions.index')
            ->with('status', 'Competition deleted successfully.');
    }

    public function registrations(Request $request, Competition $competition): View
    {
        $registrations = $this->competitionRegistrationsQuery($request, $competition)
            ->with(['teacher', 'school.regionModel', 'school.districtModel', 'region', 'districtModel', 'teacherClub'])
            ->withCount('representativeStudents')
            ->orderByRaw("CASE WHEN status = 'draft' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $eligibilityPool = app(CompetitionEligibilityPoolService::class);
        $competition->loadMissing('classRequirements');
        $poolSummaries = [];
        foreach ($registrations as $registration) {
            if (
                ! $registration->teacherClub
                || ! in_array($registration->status, ['draft', 'rejected'], true)
            ) {
                continue;
            }

            $clubId = (int) $registration->teacherClub->id;
            if (! array_key_exists($clubId, $poolSummaries)) {
                $poolSummaries[$clubId] = $eligibilityPool->summarizePool($competition, $registration->teacherClub);
            }

            $eligibilityPool->applyPoolToOpenRegistration(
                $registration,
                $registration->teacherClub,
                $poolSummaries[$clubId],
            );
        }

        $enrollableSchools = $this->enrollableSchoolsForCompetition($competition);
        $regions = Region::query()->orderBy('name')->get(['id', 'name']);
        $districts = District::query()
            ->when($request->filled('region_id'), fn (Builder $query) => $query->where('region_id', $request->integer('region_id')))
            ->orderBy('name')
            ->get(['id', 'name', 'region_id']);
        $districtsByRegion = District::groupedByRegionId();

        return view('admin.competitions.registrations.index', compact(
            'competition',
            'registrations',
            'enrollableSchools',
            'regions',
            'districts',
            'districtsByRegion',
        ));
    }

    public function enrollSchool(
        Request $request,
        Competition $competition,
        CompetitionParticipantRegistrationService $participantRegistrationService,
        CompetitionEligibilityPoolService $eligibilityPool,
    ): RedirectResponse {
        $validated = $request->validate([
            'school_id' => ['required', 'integer', 'exists:schools,id'],
        ]);

        $schoolId = (int) $validated['school_id'];
        $school = School::query()->findOrFail($schoolId);

        $existing = $participantRegistrationService->findForSchool($competition, $schoolId);
        if ($existing) {
            return back()
                ->with('error', "{$school->name} is already enrolled in this competition.")
                ->withInput();
        }

        $teacherClub = TeacherClub::query()
            ->with(['user', 'school'])
            ->where('club_id', $competition->club_id)
            ->where('school_id', $schoolId)
            ->where('status', 'approved')
            ->orderByDesc('is_leader')
            ->orderBy('id')
            ->first();

        if (! $teacherClub) {
            return back()
                ->with('error', "{$school->name} has no approved teacher for this competition's club.")
                ->withInput();
        }

        $registration = $participantRegistrationService->ensureDraftRegistration($teacherClub, $competition);

        $eligibilityPool->applyPoolToOpenRegistration(
            $registration,
            $teacherClub,
            $eligibilityPool->computePool($competition, $teacherClub),
        );

        $this->logStageEvent(
            $competition,
            'registration_enrolled_by_admin',
            "School enrolled by admin: {$registration->resolvedSchoolName()}.",
            [
                'registration_ulid' => $registration->ulid,
                'school_id' => $schoolId,
                'teacher_club_id' => $teacherClub->id,
            ]
        );

        return redirect()
            ->route('admin.competitions.registrations.index', $competition)
            ->with('status', "{$school->name} enrolled successfully. Registration is incomplete until the teacher finishes eligibility.");
    }

    public function exportRegistrations(Request $request, Competition $competition): Response
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:draft,pending,approved,rejected,ineligible'],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],
        ]);

        $registrations = $this->competitionRegistrationsQuery($request, $competition)
            ->with(['teacher', 'school.regionModel', 'school.districtModel', 'region', 'districtModel', 'representativeStudents', 'teacherClub'])
            ->orderByRaw("CASE WHEN status = 'draft' THEN 0 ELSE 1 END")
            ->latest()
            ->get();

        $filterParts = [];
        if ($request->filled('search')) {
            $filterParts[] = 'Search: '.$request->string('search');
        }
        if ($request->filled('status')) {
            $filterParts[] = 'Status: '.ucfirst((string) $request->status);
        }
        if ($request->filled('region_id')) {
            $regionName = Region::query()->whereKey($request->integer('region_id'))->value('name');
            if ($regionName) {
                $filterParts[] = 'Region: '.$regionName;
            }
        }
        if ($request->filled('district_id')) {
            $districtName = District::query()->whereKey($request->integer('district_id'))->value('name');
            if ($districtName) {
                $filterParts[] = 'District: '.$districtName;
            }
        }

        $pdf = Pdf::loadView('admin.downloads.competition-registrations-list', [
            'competition' => $competition,
            'registrations' => $registrations,
            'title' => $competition->name.' Registrations',
            'generatedAt' => now(),
            'filterSummary' => $filterParts === [] ? null : implode(' · ', $filterParts),
        ])->setPaper('a4', 'landscape');

        return $pdf->download(
            'competition_registrations_'.Str::slug($competition->name).'_'.now()->format('Y-m-d').'.pdf'
        );
    }

    public function showRegistration(Competition $competition, CompetitionRegistration $registration): View
    {
        if ($registration->competition_id !== $competition->id) {
            abort(404);
        }

        $registration->load([
            'teacher',
            'school.regionModel',
            'school.districtModel',
            'region',
            'districtModel',
            'approver',
            'representativeStudents.student.schoolClass',
            'teacherClub',
        ]);

        $competition->loadMissing(['classRequirements.schoolClass']);

        $poolStudents = collect();
        $poolEligibility = null;

        if ($registration->teacherClub) {
            $eligibilityPool = app(CompetitionEligibilityPoolService::class);
            $poolEligibility = $eligibilityPool->computePool($competition, $registration->teacherClub);
            $poolStudents = $poolEligibility['students'];

            // Heal stale draft/rejected snapshots (and promote to pending when pool is met).
            if (in_array($registration->status, ['draft', 'rejected'], true)) {
                $eligibilityPool->applyPoolToOpenRegistration(
                    $registration,
                    $registration->teacherClub,
                    $poolEligibility,
                );
                $registration->refresh();
            }
        }

        $registerUrl = ClientUrl::route('client.competitions.register', $competition);
        $smsCompetitionUrl = $competition->smsUrl();
        $smsTeacherPhone = $registration->resolvedTeacherPhone();
        $smsPhoneNormalized = TanzaniaPhone::normalize($smsTeacherPhone);
        $smsDefaultSender = CompetitionSmsTemplates::defaultSenderId();
        $smsAllowedSenders = CompetitionSmsTemplates::allowedSenders();
        $smsApprovalPreview = CompetitionSmsTemplates::render(
            CompetitionSmsTemplates::template(CompetitionSmsTemplates::KEY_REGISTRATION_APPROVED),
            [
                'teacher_name' => $registration->resolvedTeacherName() ?: 'Teacher',
                'school_name' => $registration->resolvedSchoolName(),
                'competition_name' => $competition->name,
                'url' => $smsCompetitionUrl,
                'short_url' => $smsCompetitionUrl,
            ]
        );
        $smsReminderPreview = CompetitionSmsTemplates::render(
            CompetitionSmsTemplates::template(CompetitionSmsTemplates::KEY_ELIGIBILITY_REMINDER),
            [
                'teacher_name' => $registration->resolvedTeacherName() ?: 'Teacher',
                'school_name' => $registration->resolvedSchoolName(),
                'competition_name' => $competition->name,
                'url' => $registerUrl,
                'short_url' => $smsCompetitionUrl,
            ]
        );

        return view('admin.competitions.registrations.show', compact(
            'competition',
            'registration',
            'poolStudents',
            'poolEligibility',
            'smsTeacherPhone',
            'smsPhoneNormalized',
            'smsDefaultSender',
            'smsAllowedSenders',
            'smsApprovalPreview',
            'smsReminderPreview',
        ));
    }

    public function approveRegistration(
        Request $request,
        Competition $competition,
        CompetitionRegistration $registration
    ): RedirectResponse {
        if ($registration->competition_id !== $competition->id) {
            abort(404);
        }

        if ($registration->status !== 'pending') {
            return back()->with('error', 'Only pending registrations can be approved.');
        }

        $smsInput = $this->validatedSmsSendInput($request);

        $registration->loadCount('representativeStudents');
        $requiredReps = max(1, (int) ($competition->representative_count ?? 10));
        $repCount = (int) $registration->representative_students_count;
        if ($repCount > 0 && $repCount !== $requiredReps) {
            return back()->with('error', "When representatives are recorded, there must be exactly {$requiredReps}.");
        }

        $registration->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => request()->user()?->getKey(),
            'rejection_reason' => null,
        ]);

        $this->logStageEvent(
            $competition,
            'registration_approved',
            "Registration approved for {$registration->resolvedSchoolName()}.",
            ['registration_ulid' => $registration->ulid]
        );

        $emailSent = $this->sendRegistrationApprovedEmail($competition, $registration);
        $smsResult = $this->sendRegistrationApprovedSms($competition, $registration, $smsInput);

        $parts = ['Registration approved successfully.'];
        $parts[] = $emailSent
            ? 'Congratulations email sent to the teacher.'
            : 'No teacher email was available to notify.';
        $parts[] = $this->smsStatusPhrase($smsResult);

        return back()->with('status', implode(' ', $parts));
    }

    public function overrideRegistrationEligibility(
        Request $request,
        Competition $competition,
        CompetitionRegistration $registration
    ): RedirectResponse {
        if ($registration->competition_id !== $competition->id) {
            abort(404);
        }

        if ($registration->status !== 'draft') {
            return back()->with('error', 'Eligibility can only be overridden for incomplete registrations.');
        }

        $validated = $request->validate([
            'override_reason' => ['required', 'string', 'max:1000'],
        ]);
        $smsInput = $this->validatedSmsSendInput($request);

        $registration->update([
            'status' => 'approved',
            'submitted_at' => $registration->submitted_at ?? now(),
            'approved_at' => now(),
            'approved_by' => $request->user()?->getKey(),
            'rejection_reason' => null,
        ]);

        $this->logStageEvent(
            $competition,
            'registration_eligibility_overridden',
            "Eligibility overridden and registration approved for {$registration->resolvedSchoolName()}.",
            [
                'registration_ulid' => $registration->ulid,
                'eligibility_met' => $registration->eligibility_met,
                'reason' => $validated['override_reason'],
            ]
        );

        $this->sendRegistrationApprovedEmail($competition, $registration);
        $smsResult = $this->sendRegistrationApprovedSms($competition, $registration, $smsInput);

        return back()->with(
            'status',
            'Participant passed the registration phase by admin decision. '.$this->smsStatusPhrase($smsResult)
        );
    }

    public function rejectRegistration(
        Request $request,
        Competition $competition,
        CompetitionRegistration $registration
    ): RedirectResponse {
        if ($registration->competition_id !== $competition->id) {
            abort(404);
        }

        if ($registration->status !== 'pending') {
            return back()->with('error', 'Only pending registrations can be rejected.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $registration->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'approved_by' => request()->user()?->getKey(),
        ]);

        $this->logStageEvent(
            $competition,
            'registration_rejected',
            "Registration rejected for {$registration->resolvedSchoolName()}.",
            ['registration_ulid' => $registration->ulid, 'reason' => $validated['rejection_reason']]
        );

        return back()->with('status', 'Registration rejected.');
    }

    public function sendEligibilityReminder(
        Request $request,
        Competition $competition,
        CompetitionRegistration $registration
    ): RedirectResponse {
        if ($registration->competition_id !== $competition->id) {
            abort(404);
        }

        $smsInput = $this->validatedSmsSendInput($request);

        if (! $registration->canSendEligibilityReminder()) {
            if (! $registration->needsEligibilityCompletion()) {
                return back()->with('error', 'Reminders are only for incomplete (draft) registrations.');
            }

            if (! filled($registration->resolvedTeacherEmail()) && ! filled($registration->resolvedTeacherPhone())) {
                return back()->with('error', 'This registration has no teacher email or phone on file.');
            }

            $max = CompetitionRegistration::maxEligibilityReminders();

            return back()->with('error', "Maximum of {$max} reminder(s) already sent for this participant.");
        }

        $registerUrl = ClientUrl::route('client.competitions.register', $competition);
        $teacherEmail = $registration->resolvedTeacherEmail();
        $emailSent = false;

        if (filled($teacherEmail)) {
            try {
                Mail::to($teacherEmail)->send(
                    new CompetitionEligibilityReminderMail($competition, $registration, $registerUrl)
                );
                $emailSent = true;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $smsResult = ['ok' => false, 'skipped' => true, 'reason' => 'not_requested'];
        if ($smsInput['send_sms']) {
            $smsResult = $this->dispatchCompetitionSms(
                $competition,
                $registration,
                $smsInput['sms_message'],
                $smsInput['sms_sender_id'],
                'eligibility_reminder_sms',
                'eligibility-reminder-'.$registration->ulid.'-'.((int) $registration->eligibility_reminder_count + 1)
            );
        }

        if (! $emailSent && ! ($smsResult['ok'] ?? false)) {
            return back()->with('error', 'Could not send reminder by email or SMS. Check teacher contact details and try again.');
        }

        $registration->update([
            'eligibility_reminder_count' => (int) $registration->eligibility_reminder_count + 1,
            'last_eligibility_reminder_sent_at' => now(),
        ]);

        $this->logStageEvent(
            $competition,
            'eligibility_reminder_sent',
            "Eligibility reminder sent to {$registration->resolvedSchoolName()}.",
            [
                'registration_ulid' => $registration->ulid,
                'reminder_count' => $registration->eligibility_reminder_count,
                'email_sent' => $emailSent,
                'teacher_email' => $teacherEmail ?: null,
                'sms' => $smsResult,
            ]
        );

        $parts = [];
        if ($emailSent) {
            $parts[] = 'Reminder email sent to '.$teacherEmail.'.';
        }
        $parts[] = $this->smsStatusPhrase($smsResult);

        return back()->with('status', implode(' ', $parts));
    }

    public function ideas(Competition $competition): View
    {
        $ideas = $competition->ideas()
            ->with([
                'registration.school',
                'registration.teacher',
                'communityRegion',
                'communityDistrict',
                'scores',
                'shortlist',
            ])
            ->latest()
            ->paginate(20);

        return view('admin.competitions.ideas.index', compact('competition', 'ideas'));
    }

    public function showIdea(Competition $competition, CompetitionIdea $idea): View
    {
        if ($idea->competition_id !== $competition->id) {
            abort(404);
        }

        $idea->load([
            'competition.club',
            'registration.school.regionModel',
            'registration.region',
            'registration.teacher',
            'registration.teacherClub',
            'registration.representativeStudents.student.schoolClass',
            'communityRegion',
            'communityDistrict',
            'scores.judge',
            'shortlist',
            'reopenedBy',
        ]);

        $registration = $idea->registration;
        $smsPhoneNormalized = TanzaniaPhone::normalize($registration?->resolvedTeacherPhone());
        $smsDefaultSender = CompetitionSmsTemplates::defaultSenderId();
        $smsAllowedSenders = CompetitionSmsTemplates::allowedSenders();
        $smsCompetitionUrl = $competition->smsUrl();
        $smsTemplateVars = [
            'teacher_name' => $registration?->resolvedTeacherName() ?: 'Teacher',
            'school_name' => $registration?->resolvedSchoolName() ?: 'your school',
            'competition_name' => $competition->name,
            'url' => $smsCompetitionUrl,
            'short_url' => $smsCompetitionUrl,
        ];
        $smsShortlistPreview = CompetitionSmsTemplates::render(
            CompetitionSmsTemplates::template(CompetitionSmsTemplates::KEY_IDEA_SHORTLISTED),
            $smsTemplateVars
        );
        $smsReminderPreview = CompetitionSmsTemplates::render(
            CompetitionSmsTemplates::template(CompetitionSmsTemplates::KEY_IDEA_SHORTLIST_REMINDER),
            $smsTemplateVars
        );
        $shortlistAlreadyNotified = (bool) $idea->shortlist?->hasSentShortlistNotification();
        $canSendShortlistReminder = (bool) $idea->shortlist?->canSendShortlistReminder();

        return view('admin.competitions.ideas.show', compact(
            'competition',
            'idea',
            'smsPhoneNormalized',
            'smsDefaultSender',
            'smsAllowedSenders',
            'smsShortlistPreview',
            'smsReminderPreview',
            'shortlistAlreadyNotified',
            'canSendShortlistReminder',
        ));
    }

    public function scoreIdea(Request $request, Competition $competition, CompetitionIdea $idea): RedirectResponse
    {
        if ($idea->competition_id !== $competition->id) {
            abort(404);
        }

        $judgeUserId = request()->user()?->getKey();
        if (! $judgeUserId) {
            abort(403);
        }

        $validated = $request->validate([
            'stem_score' => ['required', 'integer', 'min:1', 'max:5'],
            'product_potential_score' => ['required', 'integer', 'min:1', 'max:5'],
            'community_relevance_score' => ['required', 'integer', 'min:1', 'max:5'],
            'creativity_score' => ['required', 'integer', 'min:1', 'max:5'],
            'feedback' => ['nullable', 'string', 'max:5000'],
        ]);

        $total = (float) $validated['stem_score']
            + (float) $validated['product_potential_score']
            + (float) $validated['community_relevance_score']
            + (float) $validated['creativity_score'];

        CompetitionIdeaScore::updateOrCreate(
            [
                'competition_id' => $competition->id,
                'competition_idea_id' => $idea->id,
                'judge_user_id' => $judgeUserId,
            ],
            [
                'stem_score' => $validated['stem_score'],
                'product_potential_score' => $validated['product_potential_score'],
                'community_relevance_score' => $validated['community_relevance_score'],
                'creativity_score' => $validated['creativity_score'],
                'local_material_use_score' => 0,
                'total_score' => $total,
                'feedback' => $validated['feedback'] ?? null,
            ]
        );

        $this->logStageEvent(
            $competition,
            'idea_scored',
            "Idea scored for {$idea->registration?->resolvedSchoolName()}.",
            ['idea_ulid' => $idea->ulid, 'total_score' => $total]
        );

        return back()->with('status', 'Idea score saved.');
    }

    public function shortlistIdea(
        Request $request,
        Competition $competition,
        CompetitionIdea $idea
    ): RedirectResponse {
        if ($idea->competition_id !== $competition->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['shortlisted', 'waitlist', 'rejected'])],
            'rank' => ['nullable', 'integer', 'min:1'],
        ]);
        $smsInput = $this->validatedSmsSendInput($request);

        $existing = CompetitionShortlist::query()
            ->where('competition_idea_id', $idea->id)
            ->first();
        $alreadyNotified = $existing?->hasSentShortlistNotification() ?? false;
        $shouldNotify = $validated['status'] === 'shortlisted' && ! $alreadyNotified;

        $shortlist = CompetitionShortlist::updateOrCreate(
            ['competition_idea_id' => $idea->id],
            [
                'competition_id' => $competition->id,
                'competition_phase_id' => $competition->phases()
                    ->where('phase_key', 'idea_shortlisting')
                    ->value('id'),
                'region_id' => $idea->registration?->region_id,
                'rank' => $validated['rank'] ?? null,
                'status' => $validated['status'],
                'decided_by' => $request->user()?->getKey(),
                'decided_at' => now(),
            ]
        );

        $idea->update([
            'status' => $validated['status'] === 'shortlisted' ? 'shortlisted' : 'not_shortlisted',
        ]);

        $this->logStageEvent(
            $competition,
            'idea_shortlist_decision',
            "Shortlist decision set to {$validated['status']} for {$idea->registration?->resolvedSchoolName()}.",
            ['idea_ulid' => $idea->ulid, 'status' => $validated['status'], 'rank' => $validated['rank'] ?? null]
        );

        if (! $shouldNotify) {
            $message = 'Shortlist status updated.';
            if ($validated['status'] === 'shortlisted' && $alreadyNotified) {
                $message .= ' Notification was not sent again. Use Reminder to resend.';
            }

            return back()->with('status', $message);
        }

        $idea->loadMissing(['registration.teacher', 'registration.school']);
        $delivery = $this->deliverShortlistNotification(
            $competition,
            $idea,
            $smsInput,
            'idea-shortlisted-'.$idea->ulid,
            false
        );

        if ($delivery['email_sent'] || ($delivery['sms']['ok'] ?? false)) {
            $shortlist->update(['notified_at' => now()]);
        }

        return back()->with('status', 'Shortlist status updated. '.$delivery['summary']);
    }

    public function sendShortlistReminder(
        Request $request,
        Competition $competition,
        CompetitionIdea $idea
    ): RedirectResponse {
        if ($idea->competition_id !== $competition->id) {
            abort(404);
        }

        $smsInput = $this->validatedSmsSendInput($request);
        $shortlist = CompetitionShortlist::query()
            ->where('competition_idea_id', $idea->id)
            ->first();

        if (! $shortlist || $shortlist->status !== 'shortlisted') {
            return back()->with('error', 'Reminders can only be sent for shortlisted ideas.');
        }

        if (! $shortlist->hasSentShortlistNotification()) {
            return back()->with('error', 'Send the first shortlist notification before sending a reminder.');
        }

        if (! $shortlist->canSendShortlistReminder()) {
            $max = CompetitionShortlist::maxReminders();

            return back()->with('error', "Maximum of {$max} reminder(s) already sent for this shortlist.");
        }

        $registration = $idea->registration;
        if (! filled($registration?->resolvedTeacherEmail()) && ! filled($registration?->resolvedTeacherPhone())) {
            return back()->with('error', 'This idea has no teacher email or phone on file.');
        }

        $idea->loadMissing(['registration.teacher', 'registration.school']);
        $delivery = $this->deliverShortlistNotification(
            $competition,
            $idea,
            $smsInput,
            'idea-shortlist-reminder-'.$idea->ulid.'-'.((int) $shortlist->reminder_count + 1),
            true
        );

        if (! $delivery['email_sent'] && ! ($delivery['sms']['ok'] ?? false)) {
            return back()->with('error', 'Could not send reminder by email or SMS. Check teacher contact details and try again.');
        }

        $shortlist->update([
            'reminder_count' => (int) $shortlist->reminder_count + 1,
            'last_reminder_sent_at' => now(),
        ]);

        return back()->with('status', $delivery['summary']);
    }

    public function updateIdeaFeedback(
        Request $request,
        Competition $competition,
        CompetitionIdea $idea
    ): RedirectResponse {
        if ($idea->competition_id !== $competition->id) {
            abort(404);
        }

        $validated = $request->validate([
            'admin_feedback' => ['nullable', 'string', 'max:5000'],
        ]);

        $feedback = trim((string) ($validated['admin_feedback'] ?? ''));

        $idea->update([
            'admin_feedback' => $feedback === '' ? null : $feedback,
        ]);

        $this->logStageEvent(
            $competition,
            'idea_admin_feedback_saved',
            "School feedback updated for {$idea->registration?->resolvedSchoolName()}.",
            ['idea_ulid' => $idea->ulid, 'has_feedback' => $feedback !== '']
        );

        return back()->with('status', $feedback === '' ? 'School feedback cleared.' : 'School feedback saved.');
    }

    /**
     * @param  array{send_sms: bool, sms_message: string, sms_sender_id: string}  $smsInput
     * @return array{email_sent: bool, sms: array<string, mixed>, summary: string}
     */
    private function deliverShortlistNotification(
        Competition $competition,
        CompetitionIdea $idea,
        array $smsInput,
        string $idempotencyKey,
        bool $isReminder
    ): array {
        $registration = $idea->registration;
        $teacherEmail = $registration?->resolvedTeacherEmail() ?? '';
        $emailSent = false;

        if ($registration && filled($teacherEmail)) {
            try {
                Mail::to($teacherEmail)->send(
                    new CompetitionIdeaShortlistedMail(
                        $competition,
                        $registration,
                        $idea,
                        $competition->participantUrl(),
                        $isReminder
                    )
                );
                $emailSent = true;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $smsResult = ['ok' => false, 'skipped' => true, 'reason' => 'not_requested'];
        if ($smsInput['send_sms']) {
            if ($registration) {
                $smsResult = $this->dispatchCompetitionSms(
                    $competition,
                    $registration,
                    $smsInput['sms_message'],
                    $smsInput['sms_sender_id'],
                    $isReminder ? 'idea_shortlist_reminder_sms' : 'idea_shortlisted_sms',
                    $idempotencyKey
                );
            } else {
                $smsResult = ['ok' => false, 'skipped' => true, 'reason' => 'invalid_phone'];
            }
        }

        if ($emailSent) {
            $emailPhrase = ($isReminder ? 'Reminder email' : 'Shortlist email').' sent to '.$teacherEmail.'.';
        } elseif (! filled($teacherEmail)) {
            $emailPhrase = 'No teacher email was available, so email was skipped.';
        } else {
            $emailPhrase = 'Email could not be sent.';
        }

        $summary = $emailPhrase.' '.$this->smsStatusPhrase($smsResult);

        $this->logStageEvent(
            $competition,
            $isReminder ? 'idea_shortlist_reminder_sent' : 'idea_shortlist_notification_sent',
            $summary,
            [
                'idea_ulid' => $idea->ulid,
                'email_sent' => $emailSent,
                'teacher_email' => filled($teacherEmail) ? $teacherEmail : null,
                'sms' => $smsResult,
            ]
        );

        return [
            'email_sent' => $emailSent,
            'sms' => $smsResult,
            'summary' => $summary,
        ];
    }

    private function competitionRegistrationsQuery(Request $request, Competition $competition): Builder
    {
        $query = $competition->registrations()->getQuery();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search): void {
                $like = '%'.$search.'%';
                $q->where('school_name', 'like', $like)
                    ->orWhereHas('school', function ($schoolQuery) use ($like): void {
                        $schoolQuery->where('name', 'like', $like);
                    })
                    ->orWhere('teacher_name', 'like', $like)
                    ->orWhere('teacher_email', 'like', $like)
                    ->orWhere('teacher_phone', 'like', $like)
                    ->orWhere('school_district', 'like', $like)
                    ->orWhere('school_ward', 'like', $like);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        if ($request->filled('region_id')) {
            $query->where('region_id', $request->integer('region_id'));
        }

        if ($request->filled('district_id')) {
            $query->where('district_id', $request->integer('district_id'));
        }

        return $query;
    }

    /**
     * Active schools with an approved teacher for this competition's club, not yet registered.
     *
     * @return \Illuminate\Support\Collection<int, School>
     */
    private function enrollableSchoolsForCompetition(Competition $competition)
    {
        $registeredSchoolIds = $competition->registrations()
            ->whereNotNull('school_id')
            ->pluck('school_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        return School::query()
            ->active()
            ->ordered()
            ->whereHas('teacherClubs', function (Builder $query) use ($competition): void {
                $query->where('club_id', $competition->club_id)
                    ->where('status', 'approved');
            })
            ->when($registeredSchoolIds !== [], fn (Builder $query) => $query->whereNotIn('id', $registeredSchoolIds))
            ->with(['regionModel', 'districtModel'])
            ->get();
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while ($this->slugExists($slug, $ignoreId)) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return Competition::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists();
    }

    private function syncCompetitionClasses(Competition $competition, array $classIds): void
    {
        CompetitionClassRequirement::where('competition_id', $competition->id)->delete();

        $classIds = collect($classIds)
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values();

        foreach ($classIds as $classId) {
            CompetitionClassRequirement::create([
                'competition_id' => $competition->id,
                'school_class_id' => $classId,
            ]);
        }
    }

    private function syncCompetitionPhases(Competition $competition, array $phases): void
    {
        $phaseRows = collect($phases)
            ->filter(fn ($phase) => ! blank($phase['name'] ?? null))
            ->values();

        if ($phaseRows->isEmpty()) {
            $phaseRows = collect(Competition::DEFAULT_PHASE_TEMPLATE)->map(function (array $phaseTemplate): array {
                return [
                    'name' => $phaseTemplate['name'],
                    'description' => null,
                    'status' => 'upcoming',
                    'start_at' => null,
                    'deadline_at' => null,
                ];
            });
        }

        DB::transaction(function () use ($competition, $phaseRows): void {
            $existingRows = DB::table('competition_phases')
                ->where('competition_id', $competition->id)
                ->get();

            $existingByPhaseKey = $existingRows
                ->keyBy('phase_key');

            // Avoid transient UNIQUE(competition_id, phase_order) collisions while reshuffling.
            DB::table('competition_phases')
                ->where('competition_id', $competition->id)
                ->update([
                    'phase_order' => DB::raw('COALESCE(phase_order, 0) + 1000'),
                    'sequence' => DB::raw('COALESCE(sequence, 0) + 1000'),
                    'updated_at' => now(),
                ]);

            $desiredPhaseKeys = [];

            foreach ($phaseRows as $index => $phase) {
                $default = Competition::DEFAULT_PHASE_TEMPLATE[$index] ?? null;
                $phaseKey = $default['phase_key'] ?? 'custom_phase_'.($index + 1);
                $desiredPhaseKeys[] = $phaseKey;

                $opensAt = $phase['start_at'] ?? null;
                $closesAt = $phase['deadline_at'] ?? null;
                if ($opensAt && $closesAt && $closesAt < $opensAt) {
                    $closesAt = $opensAt;
                }

                $payload = [
                    'phase_order' => $index + 1,
                    'name' => $phase['name'],
                    'description' => $phase['description'] ?? null,
                    'sequence' => $index + 1,
                    'opens_at' => $opensAt,
                    'closes_at' => $closesAt,
                    'status' => $phase['status'] ?? 'upcoming',
                    'is_default' => $default !== null,
                    'is_required_action' => $default['is_required_action'] ?? false,
                    'action_type' => $default['action_type'] ?? 'custom',
                    'updated_at' => now(),
                ];

                $existing = $existingByPhaseKey->get($phaseKey);
                if ($existing) {
                    DB::table('competition_phases')
                        ->where('id', $existing->id)
                        ->update($payload);
                } else {
                    CompetitionPhase::create([
                        'competition_id' => $competition->id,
                        'phase_key' => $phaseKey,
                        ...$payload,
                    ]);
                }
            }

            // Remove phases no longer present in the configured list.
            $staleIds = $existingRows
                ->filter(fn ($row) => ! in_array($row->phase_key, $desiredPhaseKeys, true))
                ->pluck('id')
                ->all();

            if ($staleIds !== []) {
                DB::table('competition_phases')
                    ->whereIn('id', $staleIds)
                    ->delete();
            }
        });
    }

    private function sendRegistrationApprovedEmail(
        Competition $competition,
        CompetitionRegistration $registration
    ): bool {
        $teacherEmail = $registration->resolvedTeacherEmail();
        if (! filled($teacherEmail)) {
            return false;
        }

        $competitionUrl = ClientUrl::route('client.competitions.show', $competition);

        try {
            Mail::to($teacherEmail)->send(
                new CompetitionRegistrationApprovedMail($competition, $registration, $competitionUrl)
            );
        } catch (\Throwable $e) {
            report($e);

            $this->logStageEvent(
                $competition,
                'registration_approved_email_failed',
                "Failed to send registration approval email to {$registration->resolvedSchoolName()} ({$teacherEmail}).",
                [
                    'registration_ulid' => $registration->ulid,
                    'teacher_email' => $teacherEmail,
                    'error' => $e->getMessage(),
                ]
            );

            return false;
        }

        $this->logStageEvent(
            $competition,
            'registration_approved_email_sent',
            "Registration approval email sent to {$registration->resolvedSchoolName()} ({$teacherEmail}).",
            [
                'registration_ulid' => $registration->ulid,
                'teacher_email' => $teacherEmail,
            ]
        );

        return true;
    }

    /**
     * @return array{send_sms: bool, sms_message: string, sms_sender_id: string}
     */
    private function validatedSmsSendInput(Request $request): array
    {
        $allowed = CompetitionSmsTemplates::allowedSenders();

        $validated = $request->validate([
            'send_sms' => ['nullable'],
            'sms_message' => ['nullable', 'string', 'max:480'],
            'sms_sender_id' => ['nullable', 'string', Rule::in($allowed)],
        ]);

        return [
            'send_sms' => $request->boolean('send_sms'),
            'sms_message' => trim((string) ($validated['sms_message'] ?? '')),
            'sms_sender_id' => (string) ($validated['sms_sender_id'] ?? CompetitionSmsTemplates::defaultSenderId()),
        ];
    }

    /**
     * @param  array{send_sms: bool, sms_message: string, sms_sender_id: string}  $smsInput
     * @return array<string, mixed>
     */
    private function sendRegistrationApprovedSms(
        Competition $competition,
        CompetitionRegistration $registration,
        array $smsInput
    ): array {
        if (! $smsInput['send_sms']) {
            return ['ok' => false, 'skipped' => true, 'reason' => 'not_requested'];
        }

        return $this->dispatchCompetitionSms(
            $competition,
            $registration,
            $smsInput['sms_message'],
            $smsInput['sms_sender_id'],
            'registration_approved_sms',
            'registration-approved-'.$registration->ulid
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function dispatchCompetitionSms(
        Competition $competition,
        CompetitionRegistration $registration,
        string $message,
        string $senderId,
        string $eventPrefix,
        ?string $idempotencyKey = null
    ): array {
        $phone = $registration->resolvedTeacherPhone();
        $result = app(SmsNotifier::class)->send($phone, $message, $senderId, $idempotencyKey);

        if ($result['ok'] ?? false) {
            $this->logStageEvent(
                $competition,
                $eventPrefix.'_sent',
                "SMS sent to {$registration->resolvedSchoolName()} ({$result['to']}).",
                [
                    'registration_ulid' => $registration->ulid,
                    'to' => $result['to'] ?? null,
                    'sender_id' => $result['sender_id'] ?? $senderId,
                ]
            );
        } elseif (! ($result['skipped'] ?? false)) {
            $this->logStageEvent(
                $competition,
                $eventPrefix.'_failed',
                "Failed to send SMS to {$registration->resolvedSchoolName()}.",
                [
                    'registration_ulid' => $registration->ulid,
                    'error' => $result['error'] ?? 'unknown',
                    'sender_id' => $result['sender_id'] ?? $senderId,
                ]
            );
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $smsResult
     */
    private function smsStatusPhrase(array $smsResult): string
    {
        if ($smsResult['ok'] ?? false) {
            $phrase = 'SMS sent to '.($smsResult['to'] ?? 'teacher').'.';
            if (isset($smsResult['credits'])) {
                $phrase .= ' Credits deducted: '.(int) $smsResult['credits'].'.';
            }

            return $phrase;
        }

        if ($smsResult['skipped'] ?? false) {
            return match ($smsResult['reason'] ?? '') {
                'not_requested' => 'SMS was not requested.',
                'invalid_phone' => 'SMS skipped (no valid teacher phone).',
                'empty_message' => 'SMS skipped (empty message).',
                'sms_disabled' => 'SMS skipped (SMS disabled or not configured).',
                default => 'SMS skipped.',
            };
        }

        return 'SMS failed: '.($smsResult['error'] ?? 'unknown error').'.';
    }

    private function logStageEvent(
        Competition $competition,
        string $eventType,
        ?string $message = null,
        ?array $payload = null
    ): void {
        CompetitionStageEvent::create([
            'competition_id' => $competition->id,
            'event_type' => $eventType,
            'message' => $message,
            'payload' => $payload,
            'actor_user_id' => request()->user()?->getKey(),
        ]);
    }
}
