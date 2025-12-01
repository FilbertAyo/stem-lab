<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use Illuminate\View\View;

class PublicCompetitionController extends Controller
{
    /**
     * Display public list of competitions.
     */
    public function index(): View
    {
        // Active competitions (registration open or ongoing)
        $activeCompetitions = Competition::active()
            ->published()
            ->where(function ($q) {
                $q->where('competition_end', '>=', now())
                  ->orWhere('status', 'ongoing');
            })
            ->with(['club', 'awards'])
            ->withCount(['participants', 'awards'])
            ->orderBy('competition_start')
            ->get();

        // Upcoming competitions (not yet started)
        $upcomingCompetitions = Competition::active()
            ->where('status', 'published')
            ->where('competition_start', '>', now())
            ->with(['club'])
            ->orderBy('competition_start')
            ->get();

        // Completed competitions
        $completedCompetitions = Competition::active()
            ->where('status', 'completed')
            ->with(['club'])
            ->withCount('participants')
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
    public function show(Competition $competition): View
    {
        $competition->load(['club', 'awards']);
        $competition->loadCount(['participants', 'awards']);

        // Get winners if completed
        $winners = collect();
        if ($competition->status === 'completed') {
            $winners = $competition->participants()
                ->whereNotNull('award_id')
                ->with(['award', 'teacherClub.school'])
                ->orderBy('rank')
                ->get();
        }

        return view('client.front.competition-show', compact('competition', 'winners'));
    }

    /**
     * Get featured competitions for landing page.
     */
    public static function getFeaturedCompetitions(int $limit = 3)
    {
        return Competition::active()
            ->published()
            ->with(['club'])
            ->withCount('awards')
            ->orderBy('competition_start')
            ->limit($limit)
            ->get();
    }
}

