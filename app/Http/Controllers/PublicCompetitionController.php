<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\CompetitionRegistration;
use App\Models\District;
use App\Models\Region;
use App\Models\School;
use App\Models\TeacherClub;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PublicCompetitionController extends Controller
{
    /**
     * Display public list of competitions.
     */
    public function index(): View
    {
        // "Active" = live window (started or marked ongoing), not future-only starts.
        // "Upcoming" = published with competition_start in the future (mutually exclusive with active).
        $activeCompetitions = Competition::active()
            ->whereIn('status', ['published', 'ongoing'])
            ->where(function ($q) {
                $q->where('status', 'ongoing')
                    ->orWhere(function ($q2) {
                        $q2->where('status', 'published')
                            ->where(function ($q3) {
                                $q3->whereNull('competition_start')
                                    ->orWhere('competition_start', '<=', now());
                            })
                            ->where(function ($q3) {
                                $q3->whereNull('competition_end')
                                    ->orWhere('competition_end', '>=', now());
                            });
                    });
            })
            ->with(['club', 'phases'])
            ->withCount(['registrations', 'ideas'])
            ->orderBy('competition_start')
            ->get();

        $upcomingCompetitions = Competition::active()
            ->where('status', 'published')
            ->whereNotNull('competition_start')
            ->where('competition_start', '>', now())
            ->with(['club', 'phases'])
            ->orderBy('competition_start')
            ->get();

        $completedCompetitions = Competition::active()
            ->where('status', 'completed')
            ->with(['club', 'phases'])
            ->withCount('registrations')
            ->orderByDesc('competition_end')
            ->limit(6)
            ->get();

        // Calculate stats using single query with aggregation
        $statsQuery = Competition::active()
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status IN (\'published\', \'ongoing\') AND (competition_end >= ? OR status = \'ongoing\') THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = \'published\' AND competition_start > ? THEN 1 ELSE 0 END) as upcoming,
                SUM(CASE WHEN status = \'completed\' THEN 1 ELSE 0 END) as completed
            ', [now(), now()])
            ->first();

        $stats = [
            'total' => (int) $statsQuery->total,
            'active' => (int) $statsQuery->active,
            'upcoming' => (int) $statsQuery->upcoming,
            'completed' => (int) $statsQuery->completed,
        ];

        return view('client.front.competitions', compact(
            'activeCompetitions',
            'upcomingCompetitions',
            'completedCompetitions',
            'stats'
        ));
    }

    /**
     * Display a single competition publicly.
     */
    public function show(Request $request, Competition $competition): View
    {
        $competition->load(['club', 'phases']);

        $schools = School::active()->with(['regionModel', 'districtModel'])->ordered()->get();
        $regions = Region::query()->orderBy('name')->get(['id', 'name']);
        $districtsByRegion = District::groupedByRegionId();

        $allowPublicTeacherSignup = $competition->allowsPublicTeacherRegistration();

        $eligibleQuery = $request->query('eligible');
        $showTeacherSignupForm = $allowPublicTeacherSignup
            && ! Auth::check()
            && in_array($eligibleQuery, ['yes', '1', 'true'], true);
        $showTeacherSignupHelp = $allowPublicTeacherSignup
            && ! Auth::check()
            && $request->query('signup') === 'help';

        $publicTeacherSignupNotice = null;
        if (! $allowPublicTeacherSignup && ! Auth::check() && $competition->is_active) {
            if ($competition->status !== 'ongoing') {
                $publicTeacherSignupNotice = 'Teacher self-registration opens when this competition is ongoing and the registration phase is active. Coming-soon listings are for preview only until then.';
            } elseif (! $competition->isPhaseOpen('registration')) {
                $publicTeacherSignupNotice = 'This competition is ongoing, but the registration phase is not open right now. See the schedule below.';
            }
        }

        $teacherClubForCompetition = null;
        $competitionRegistration = null;
        if (Auth::check() && Auth::user()->role === 'teacher') {
            $teacherClubForCompetition = TeacherClub::query()
                ->where('user_id', Auth::id())
                ->where('club_id', $competition->club_id)
                ->first();
            if ($teacherClubForCompetition) {
                $competitionRegistration = CompetitionRegistration::query()
                    ->where('competition_id', $competition->id)
                    ->where('teacher_club_id', $teacherClubForCompetition->id)
                    ->first();
            }
        }

        return view('client.front.competition-show', compact(
            'competition',
            'schools',
            'regions',
            'districtsByRegion',
            'allowPublicTeacherSignup',
            'publicTeacherSignupNotice',
            'teacherClubForCompetition',
            'competitionRegistration',
            'showTeacherSignupForm',
            'showTeacherSignupHelp',
        ));
    }

    /**
     * Get featured competitions for landing page.
     */
    public static function getFeaturedCompetitions(int $limit = 3)
    {
        return Competition::active()
            ->published()
            ->with(['club', 'phases'])
            ->orderBy('competition_start')
            ->limit($limit)
            ->get();
    }
}
