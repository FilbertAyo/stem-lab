<?php

namespace App\Http\Middleware;

use App\Models\Visitor;
use App\Models\VisitorWeeklyStat;
use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TrackLandingVisitor
{
    private const COOKIE_NAME = 'stem_visitor';

    private const COOKIE_LIFETIME_MINUTES = 1051200; // 2 years

    /**
     * Track unique landing/page visitors via cookie. Only runs on main domain, GET requests.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET')) {
            return $next($request);
        }

        $adminHost = parse_url(config('app.admin_url', 'https://admin.adilishastemlabs.com'), PHP_URL_HOST);
        if ($request->getHost() === $adminHost) {
            return $next($request);
        }

        $token = $request->cookie(self::COOKIE_NAME);
        $now = now();

        if (empty($token)) {
            $token = Str::random(40);
            $visitor = Visitor::create([
                'visitor_token' => $token,
                'first_seen_at' => $now,
                'last_seen_at' => $now,
                'landing_views' => 1,
            ]);
            $this->recordWeeklyStat($visitor->id);

            $response = $next($request);

            return $response->cookie(
                self::COOKIE_NAME,
                $token,
                self::COOKIE_LIFETIME_MINUTES,
                '/',
                null,
                request()->secure(),
                true,
                false,
                'lax'
            );
        }

        $visitor = Visitor::where('visitor_token', $token)->first();
        if ($visitor) {
            $visitor->update([
                'last_seen_at' => $now,
                'landing_views' => DB::raw('landing_views + 1'),
            ]);
            $this->recordWeeklyStat($visitor->id);
        }

        return $next($request);
    }

    private function recordWeeklyStat(int $visitorId): void
    {
        $weekStart = Carbon::now()->startOfWeek()->toDateString();
        $stat = VisitorWeeklyStat::firstOrCreate(
            ['visitor_id' => $visitorId, 'week_start' => $weekStart],
            ['views' => 0]
        );
        $stat->increment('views');
    }
}
