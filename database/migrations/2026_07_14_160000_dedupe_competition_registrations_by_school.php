<?php

use App\Models\CompetitionIdea;
use App\Models\CompetitionRegistration;
use App\Models\CompetitionRegistrationStudent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dedupeRegistrationsBySchool();

        Schema::table('competition_registrations', function (Blueprint $table): void {
            $table->dropUnique('cr_comp_tclub_uq');
        });

        Schema::table('competition_registrations', function (Blueprint $table): void {
            $table->unique(
                ['competition_id', 'school_id'],
                'cr_comp_school_uq'
            );
            $table->index('teacher_club_id', 'cr_teacher_club_idx');
        });
    }

    public function down(): void
    {
        Schema::table('competition_registrations', function (Blueprint $table): void {
            $table->dropUnique('cr_comp_school_uq');
            $table->dropIndex('cr_teacher_club_idx');
        });

        Schema::table('competition_registrations', function (Blueprint $table): void {
            $table->unique(
                ['competition_id', 'teacher_club_id'],
                'cr_comp_tclub_uq'
            );
        });
    }

    private function dedupeRegistrationsBySchool(): void
    {
        $rows = CompetitionRegistration::withTrashed()
            ->whereNotNull('school_id')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (CompetitionRegistration $r): string => $r->competition_id.':'.$r->school_id);

        foreach ($rows as $group) {
            $active = $group->whereNull('deleted_at')->values();
            if ($active->count() <= 1) {
                // Force-delete soft-deleted duplicates so they cannot collide after unique change.
                foreach ($group->whereNotNull('deleted_at') as $trashed) {
                    $trashed->forceDelete();
                }

                continue;
            }

            $sorted = $active->sortByDesc(function (CompetitionRegistration $registration): array {
                $statusRank = match ($registration->status) {
                    'approved' => 50,
                    'pending' => 40,
                    'draft' => 20,
                    'rejected' => 10,
                    'ineligible' => 5,
                    default => 0,
                };

                return [
                    $statusRank,
                    $registration->eligibility_met ? 1 : 0,
                    $registration->idea()->exists() ? 1 : 0,
                    $registration->representativeStudents()->exists() ? 1 : 0,
                    $registration->submitted_at?->timestamp ?? 0,
                    -1 * (int) $registration->id,
                ];
            })->values();

            /** @var CompetitionRegistration $winner */
            $winner = $sorted->first();
            $losers = $sorted->slice(1);

            foreach ($losers as $loser) {
                $this->reassignChildrenThenForceDelete($winner, $loser);
            }

            foreach ($group->whereNotNull('deleted_at') as $trashed) {
                $trashed->forceDelete();
            }
        }
    }

    private function reassignChildrenThenForceDelete(
        CompetitionRegistration $winner,
        CompetitionRegistration $loser,
    ): void {
        DB::transaction(function () use ($winner, $loser): void {
            if (! $winner->idea()->exists()) {
                CompetitionIdea::withTrashed()
                    ->where('competition_registration_id', $loser->id)
                    ->update(['competition_registration_id' => $winner->id]);
            } else {
                CompetitionIdea::withTrashed()
                    ->where('competition_registration_id', $loser->id)
                    ->forceDelete();
            }

            $existingStudentIds = CompetitionRegistrationStudent::query()
                ->where('competition_registration_id', $winner->id)
                ->pluck('student_id')
                ->all();

            CompetitionRegistrationStudent::query()
                ->where('competition_registration_id', $loser->id)
                ->whereNotIn('student_id', $existingStudentIds)
                ->update(['competition_registration_id' => $winner->id]);

            CompetitionRegistrationStudent::query()
                ->where('competition_registration_id', $loser->id)
                ->delete();

            $loser->forceDelete();
        });
    }
};
