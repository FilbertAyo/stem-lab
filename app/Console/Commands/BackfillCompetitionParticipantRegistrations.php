<?php

namespace App\Console\Commands;

use App\Models\Competition;
use App\Models\TeacherClub;
use App\Services\CompetitionParticipantRegistrationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class BackfillCompetitionParticipantRegistrations extends Command
{
    protected $signature = 'competitions:backfill-participant-registrations
                            {competition_ulid : Competition ULID}
                            {--from=2026-05-11 : Include teacher_clubs created on or after this date (Y-m-d, app timezone)}
                            {--to= : Include teacher_clubs created on or before this date (Y-m-d, default: now)}';

    protected $description = 'Create draft competition registrations for club members who joined in a date range (participant follow-up list).';

    public function handle(CompetitionParticipantRegistrationService $service): int
    {
        $competition = Competition::query()
            ->where('ulid', $this->argument('competition_ulid'))
            ->first();

        if (! $competition) {
            $this->error('Competition not found.');

            return self::FAILURE;
        }

        if (! $competition->club_id) {
            $this->error('Competition is not linked to a club.');

            return self::FAILURE;
        }

        $tz = config('app.timezone');
        $from = Carbon::parse($this->option('from'), $tz)->startOfDay();
        $to = $this->option('to')
            ? Carbon::parse($this->option('to'), $tz)->endOfDay()
            : now($tz)->endOfDay();

        $teacherClubs = TeacherClub::query()
            ->where('club_id', $competition->club_id)
            ->where('created_at', '>=', $from)
            ->where('created_at', '<=', $to)
            ->with(['user', 'school'])
            ->orderBy('id')
            ->get();

        $created = 0;
        $skipped = 0;

        foreach ($teacherClubs as $teacherClub) {
            $existing = $competition->registrations()
                ->where('teacher_club_id', $teacherClub->id)
                ->exists();

            if ($existing) {
                $skipped++;

                continue;
            }

            $service->ensureDraftRegistration($teacherClub, $competition);
            $created++;
        }

        $this->info("Competition: {$competition->name}");
        $this->info("Club members in range: {$teacherClubs->count()}");
        $this->info("Draft registrations created: {$created}");
        $this->info("Skipped (already had registration): {$skipped}");

        return self::SUCCESS;
    }
}
