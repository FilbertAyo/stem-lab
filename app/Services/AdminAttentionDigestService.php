<?php

namespace App\Services;

use App\Mail\AdminAttentionDigestMail;
use App\Models\CompetitionIdea;
use App\Models\CompetitionPhase;
use App\Models\CompetitionPrototypeModification;
use App\Models\CompetitionPrototypeSubmission;
use App\Models\CompetitionRegistration;
use App\Models\Model3d;
use App\Models\Project;
use App\Models\School;
use App\Models\TeacherClub;
use App\Models\VrExperiment;
use App\Models\WeeklySession;
use App\Support\AdminUrl;
use Throwable;

class AdminAttentionDigestService
{
    public function __construct(
        private readonly AdminActivityNotifier $notifier,
    ) {}

    /**
     * @return array<string, list<array{title: string, count: int, items: list<string>, action_url?: string|null, action_label?: string|null}>>
     */
    public function buildSectionsByCategory(): array
    {
        return array_filter([
            'needs_attention' => $this->needsAttentionSections(),
            'clubs' => $this->clubsSections(),
            'competition' => $this->competitionSections(),
            'content' => $this->contentSections(),
        ], fn (array $sections) => $sections !== []);
    }

    /**
     * @return array<string, int> category => number of digests sent
     */
    public function send(): array
    {
        $sent = [];
        $categories = config('admin-alerts.categories', []);

        foreach ($this->buildSectionsByCategory() as $categoryKey => $sections) {
            $label = $categories[$categoryKey]['label'] ?? $categoryKey;

            try {
                $this->notifier->deliver(
                    new AdminAttentionDigestMail($categoryKey, $label, $sections),
                    $categoryKey,
                );
                $sent[$categoryKey] = 1;
            } catch (Throwable $e) {
                report($e);
                $sent[$categoryKey] = 0;
            }
        }

        return $sent;
    }

    /**
     * @return list<array{title: string, count: int, items: list<string>, action_url?: string|null, action_label?: string|null}>
     */
    private function needsAttentionSections(): array
    {
        $sections = [];

        $schools = School::query()
            ->needsReview()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'public_id', 'ulid', 'name', 'created_at']);

        $schoolCount = School::query()->needsReview()->count();
        if ($schoolCount > 0) {
            $sections[] = [
                'title' => 'Schools waiting for review',
                'count' => $schoolCount,
                'items' => $schools->map(fn (School $school) => $school->name.' · registered '.$school->created_at?->format('M d, Y'))->all(),
                'action_url' => AdminUrl::route('admin.schools.index', ['review' => 'pending']),
                'action_label' => 'Open pending schools',
            ];
        }

        $staleClubCount = TeacherClub::query()
            ->pending()
            ->where('created_at', '<=', now()->subDays(2))
            ->count();
        if ($staleClubCount > 0) {
            $sections[] = [
                'title' => 'Club join requests pending 2+ days',
                'count' => $staleClubCount,
                'items' => ["{$staleClubCount} teacher club request(s) are still pending approval."],
                'action_url' => AdminUrl::route('admin.clubs.index'),
                'action_label' => 'Open clubs',
            ];
        }

        $staleRegistrationCount = CompetitionRegistration::query()
            ->where('status', 'pending')
            ->where('submitted_at', '<=', now()->subDay())
            ->count();
        if ($staleRegistrationCount > 0) {
            $sections[] = [
                'title' => 'Competition registrations pending 1+ day',
                'count' => $staleRegistrationCount,
                'items' => ["{$staleRegistrationCount} registration(s) still await approve/reject."],
                'action_url' => AdminUrl::route('admin.competitions.index'),
                'action_label' => 'Open competitions',
            ];
        }

        return $sections;
    }

    /**
     * @return list<array{title: string, count: int, items: list<string>, action_url?: string|null, action_label?: string|null}>
     */
    private function clubsSections(): array
    {
        $pending = TeacherClub::query()
            ->pending()
            ->with(['user:id,name,email', 'club:id,name,public_id,ulid', 'school:id,name,public_id,ulid'])
            ->orderBy('created_at')
            ->limit(15)
            ->get();

        if ($pending->isEmpty()) {
            return [];
        }

        $count = TeacherClub::query()->pending()->count();

        return [[
            'title' => 'Pending club membership requests',
            'count' => $count,
            'items' => $pending->map(function (TeacherClub $membership): string {
                $teacher = $membership->user?->name ?? 'Teacher';
                $club = $membership->club?->name ?? 'Club';
                $school = $membership->school?->name ?? 'School';

                return "{$teacher} · {$school} · {$club}";
            })->all(),
            'action_url' => AdminUrl::route('admin.clubs.index'),
            'action_label' => 'Review club requests',
        ]];
    }

    /**
     * @return list<array{title: string, count: int, items: list<string>, action_url?: string|null, action_label?: string|null}>
     */
    private function competitionSections(): array
    {
        $sections = [];

        $pendingRegs = CompetitionRegistration::query()
            ->where('status', 'pending')
            ->with(['competition:id,name,ulid', 'school:id,name'])
            ->orderBy('submitted_at')
            ->limit(10)
            ->get();
        $pendingRegCount = CompetitionRegistration::query()->where('status', 'pending')->count();
        if ($pendingRegCount > 0) {
            $sections[] = [
                'title' => 'Registrations awaiting review',
                'count' => $pendingRegCount,
                'items' => $pendingRegs->map(function (CompetitionRegistration $registration): string {
                    $competition = $registration->competition?->name ?? 'Competition';
                    $school = $registration->resolvedSchoolName() ?: ($registration->school_name ?: 'School');

                    return "{$school} · {$competition}";
                })->all(),
                'action_url' => AdminUrl::route('admin.competitions.index'),
                'action_label' => 'Open competitions',
            ];
        }

        $ideasPending = CompetitionIdea::query()
            ->where('status', 'submitted')
            ->whereDoesntHave('shortlist')
            ->with(['competition:id,name,ulid', 'registration:id,school_name,school_id'])
            ->orderBy('submitted_at')
            ->limit(10)
            ->get();
        $ideasPendingCount = CompetitionIdea::query()
            ->where('status', 'submitted')
            ->whereDoesntHave('shortlist')
            ->count();
        if ($ideasPendingCount > 0) {
            $sections[] = [
                'title' => 'Ideas waiting for shortlist decision',
                'count' => $ideasPendingCount,
                'items' => $ideasPending->map(function (CompetitionIdea $idea): string {
                    $competition = $idea->competition?->name ?? 'Competition';
                    $title = $idea->problem_title ?: 'Untitled idea';

                    return "{$title} · {$competition}";
                })->all(),
                'action_url' => AdminUrl::route('admin.competitions.index'),
                'action_label' => 'Review ideas',
            ];
        }

        $prototypePendingCount = CompetitionPrototypeSubmission::query()
            ->where('status', 'submitted')
            ->whereDoesntHave('prototypeShortlist')
            ->count();
        if ($prototypePendingCount > 0) {
            $sections[] = [
                'title' => 'Prototypes waiting for shortlisting',
                'count' => $prototypePendingCount,
                'items' => ["{$prototypePendingCount} submitted prototype(s) have no shortlist decision yet."],
                'action_url' => AdminUrl::route('admin.competitions.index'),
                'action_label' => 'Open competitions',
            ];
        }

        $modificationPendingCount = CompetitionPrototypeModification::query()
            ->where('status', 'submitted')
            ->whereDoesntHave('modificationShortlist')
            ->count();
        if ($modificationPendingCount > 0) {
            $sections[] = [
                'title' => 'Prototype modifications waiting for review',
                'count' => $modificationPendingCount,
                'items' => ["{$modificationPendingCount} prototype update(s) still need a decision."],
                'action_url' => AdminUrl::route('admin.competitions.index'),
                'action_label' => 'Open competitions',
            ];
        }

        $closingPhases = CompetitionPhase::query()
            ->where('status', 'open')
            ->whereNotNull('closes_at')
            ->whereBetween('closes_at', [now(), now()->addHours(48)])
            ->with('competition:id,name,ulid')
            ->orderBy('closes_at')
            ->limit(10)
            ->get();
        if ($closingPhases->isNotEmpty()) {
            $sections[] = [
                'title' => 'Open phases closing within 48 hours',
                'count' => $closingPhases->count(),
                'items' => $closingPhases->map(function (CompetitionPhase $phase): string {
                    $competition = $phase->competition?->name ?? 'Competition';
                    $closes = $phase->closes_at?->format('M d, Y H:i') ?? 'soon';

                    return "{$competition} · {$phase->name} closes {$closes}";
                })->all(),
                'action_url' => AdminUrl::route('admin.competitions.index'),
                'action_label' => 'Check phases',
            ];
        }

        $resultsPending = CompetitionPhase::query()
            ->where('status', 'results_pending')
            ->with('competition:id,name,ulid')
            ->orderBy('updated_at')
            ->limit(10)
            ->get();
        if ($resultsPending->isNotEmpty()) {
            $sections[] = [
                'title' => 'Phases with results pending',
                'count' => $resultsPending->count(),
                'items' => $resultsPending->map(function (CompetitionPhase $phase): string {
                    $competition = $phase->competition?->name ?? 'Competition';

                    return "{$competition} · {$phase->name}";
                })->all(),
                'action_url' => AdminUrl::route('admin.competitions.index'),
                'action_label' => 'Resolve results',
            ];
        }

        return $sections;
    }

    /**
     * @return list<array{title: string, count: int, items: list<string>, action_url?: string|null, action_label?: string|null}>
     */
    private function contentSections(): array
    {
        $sections = [];

        $draftSessions = WeeklySession::query()
            ->where('status', 'draft')
            ->whereDate('session_date', '<', now()->toDateString())
            ->orderBy('session_date')
            ->limit(10)
            ->get(['id', 'ulid', 'title', 'session_date', 'status']);

        $draftCount = WeeklySession::query()
            ->where('status', 'draft')
            ->whereDate('session_date', '<', now()->toDateString())
            ->count();

        if ($draftCount > 0) {
            $sections[] = [
                'title' => 'Weekly sessions still draft after start date',
                'count' => $draftCount,
                'items' => $draftSessions->map(function (WeeklySession $session): string {
                    $title = $session->title ?: 'Untitled session';
                    $date = $session->session_date?->format('M d, Y') ?? 'no date';

                    return "{$title} · {$date}";
                })->all(),
                'action_url' => AdminUrl::route('admin.weekly-sessions.index', ['status' => 'draft']),
                'action_label' => 'Open weekly sessions',
            ];
        }

        $inactiveModels = Model3d::query()->where('is_active', false)->count();
        $inactiveVr = VrExperiment::query()->where('is_active', false)->count();
        $inactiveProjects = Project::query()->where('is_active', false)->count();
        $inactiveTotal = $inactiveModels + $inactiveVr + $inactiveProjects;

        if ($inactiveTotal > 0) {
            $sections[] = [
                'title' => 'Inactive learning content',
                'count' => $inactiveTotal,
                'items' => [
                    "3D models inactive: {$inactiveModels}",
                    "VR experiments inactive: {$inactiveVr}",
                    "Projects inactive: {$inactiveProjects}",
                ],
                'action_url' => AdminUrl::route('admin.projects.index', ['status' => 'inactive']),
                'action_label' => 'Review content',
            ];
        }

        return $sections;
    }
}
