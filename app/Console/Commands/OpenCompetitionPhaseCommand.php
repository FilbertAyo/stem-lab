<?php

namespace App\Console\Commands;

use App\Models\Competition;
use App\Models\CompetitionPhase;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class OpenCompetitionPhaseCommand extends Command
{
    protected $signature = 'competitions:open-phase
        {competition? : Competition ULID or numeric ID (defaults to latest active)}
        {--phase=idea_submission : Phase key to open}
        {--close-registration : Also set registration phase to closed}
        {--opens= : Opens-at datetime (defaults to now)}
        {--closes= : Closes-at datetime (defaults to +60 days)}';

    protected $description = 'Open a competition phase (e.g. idea_submission) so approved schools can continue';

    public function handle(): int
    {
        $competitionArg = $this->argument('competition');
        $phaseKey = (string) $this->option('phase');

        $competition = $this->resolveCompetition($competitionArg);
        if (! $competition) {
            $this->error('No competition found.');

            return self::FAILURE;
        }

        $phase = $competition->phases()->where('phase_key', $phaseKey)->first();
        if (! $phase) {
            $this->error("Phase [{$phaseKey}] not found on competition [{$competition->name}].");

            return self::FAILURE;
        }

        $opensAt = $this->option('opens') ? Carbon::parse((string) $this->option('opens')) : now()->subMinute();
        $closesAt = $this->option('closes') ? Carbon::parse((string) $this->option('closes')) : now()->addDays(60);

        $phase->update([
            'status' => 'open',
            'opens_at' => $opensAt,
            'closes_at' => $closesAt,
        ]);

        $this->info("Opened [{$phaseKey}] for: {$competition->name} (ulid={$competition->ulid})");
        $this->line("  status=open | opens_at={$phase->fresh()->opens_at} | closes_at={$phase->fresh()->closes_at}");
        $this->line('  isOpenNow='.($phase->fresh()->isOpenNow() ? 'yes' : 'no'));

        if ($this->option('close-registration')) {
            $registration = $competition->phases()->where('phase_key', 'registration')->first();
            if ($registration instanceof CompetitionPhase) {
                $registration->update(['status' => 'closed']);
                $this->info('Registration phase set to closed.');
            }
        }

        $approved = $competition->registrations()->where('status', 'approved')->count();
        $this->line("Approved schools that can submit ideas: {$approved}");

        return self::SUCCESS;
    }

    private function resolveCompetition(?string $arg): ?Competition
    {
        if (filled($arg)) {
            if (ctype_digit($arg)) {
                return Competition::query()->find((int) $arg);
            }

            return Competition::query()->where('ulid', $arg)->first();
        }

        return Competition::query()
            ->where('is_active', true)
            ->latest('id')
            ->first()
            ?? Competition::query()->latest('id')->first();
    }
}
