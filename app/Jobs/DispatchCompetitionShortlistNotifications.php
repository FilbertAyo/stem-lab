<?php

namespace App\Jobs;

use App\Models\Competition;
use App\Models\CompetitionIdea;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DispatchCompetitionShortlistNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $competitionId,
        public readonly int $ideaId,
        public readonly string $decisionStatus
    ) {}

    public function handle(): void
    {
        $competition = Competition::query()->find($this->competitionId);
        $idea = CompetitionIdea::query()
            ->with(['registration.teacher', 'registration.school'])
            ->find($this->ideaId);

        if (! $competition || ! $idea || ! $idea->registration || ! $idea->registration->teacher) {
            return;
        }

        // Placeholder for channel integrations (mail/sms/in-app) while keeping heavy dispatch async.
        Log::info('Competition shortlist notification queued', [
            'competition' => $competition->name,
            'competition_id' => $competition->id,
            'idea_id' => $idea->id,
            'decision' => $this->decisionStatus,
            'teacher_email' => $idea->registration->teacher->email,
            'school' => $idea->registration->resolvedSchoolName(),
        ]);
    }
}
