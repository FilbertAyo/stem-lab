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
        $activeCompetitions = Competition::where('is_active', true)
            ->whereIn('status', ['published', 'ongoing'])
            ->where(function ($q) {
                $q->where('competition_end', '>=', now())
                  ->orWhere('status', 'ongoing');
            })
            ->with(['club', 'awards'])
            ->withCount(['participants', 'awards'])
            ->orderBy('competition_start')
            ->get();

        // Upcoming competitions (not yet started)
        $upcomingCompetitions = Competition::where('is_active', true)
            ->where('status', 'published')
            ->where('competition_start', '>', now())
            ->with(['club'])
            ->orderBy('competition_start')
            ->get();

        // Completed competitions
        $completedCompetitions = Competition::where('is_active', true)
            ->where('status', 'completed')
            ->with(['club'])
            ->withCount('participants')
            ->orderByDesc('competition_end')
            ->limit(6)
            ->get();

        $stats = [
            'total' => Competition::where('is_active', true)->count(),
            'active' => $activeCompetitions->count(),
            'upcoming' => $upcomingCompetitions->count(),
            'completed' => Competition::where('status', 'completed')->count(),
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
        return Competition::where('is_active', true)
            ->whereIn('status', ['published', 'ongoing'])
            ->with(['club'])
            ->withCount('awards')
            ->orderBy('competition_start')
            ->limit($limit)
            ->get();
    }
}

