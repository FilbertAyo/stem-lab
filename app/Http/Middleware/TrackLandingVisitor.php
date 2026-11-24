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

    private const SEEN_COOKIE = 'stem_visitor_seen';

    private const COOKIE_LIFETIME_MINUTES = 1051200; // 2 years

    private const TRACK_INTERVAL_SECONDS = 1800;

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

        $now = now();
        $seenAt = (int) $request->cookie(self::SEEN_COOKIE);
        if ($seenAt > 0 && ($now->getTimestamp() - $seenAt) < self::TRACK_INTERVAL_SECONDS) {
            return $next($request);
        }

        $token = $request->cookie(self::COOKIE_NAME);

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

            return $this->rememberSeen($response->cookie(
                self::COOKIE_NAME,
                $token,
                self::COOKIE_LIFETIME_MINUTES,
                '/',
                null,
                request()->secure(),
                true,
                false,
                'lax'
            ), $now);
        }

        $visitor = Visitor::where('visitor_token', $token)->first();
        if ($visitor) {
            $visitor->update([
                'last_seen_at' => $now,
                'landing_views' => DB::raw('landing_views + 1'),
            ]);
            $this->recordWeeklyStat($visitor->id);
        }

        $response = $next($request);

        return $visitor ? $this->rememberSeen($response, $now) : $response;
    }

    private function rememberSeen(Response $response, Carbon $now): Response
    {
        return $response->cookie(
            self::SEEN_COOKIE,
            (string) $now->getTimestamp(),
            self::COOKIE_LIFETIME_MINUTES,
            '/',
            null,
            request()->secure(),
            true,
            false,
            'lax'
        );
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
