<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncSquashedMigrations extends Command
{
    protected $signature = 'migrate:sync-squash';

    protected $description = 'Mark renamed/squashed migrations as already run when production tables exist';

    public function handle(): int
    {
        $this->syncRenamedMigration(
            old: '2026_03_26_150421_create_regions_table',
            new: '2025_11_19_161210_create_regions_table',
            table: 'regions',
        );

        $this->pruneStaleMigrationEntries([
            '2026_07_18_130000_add_public_id_to_model3ds_table',
            '2026_07_18_140000_add_public_id_to_vr_experiments_table',
            '2026_07_18_140100_add_public_id_to_vr_question_banks_table',
            '2026_07_18_140200_add_public_id_to_vr_assessment_attempts_table',
            '2026_07_18_140300_add_public_id_to_subjects_table',
            '2026_07_18_140400_add_public_id_to_clubs_table',
            '2026_07_18_140500_add_public_id_to_users_table',
        ]);

        return self::SUCCESS;
    }

    private function syncRenamedMigration(string $old, string $new, string $table): void
    {
        if (DB::table('migrations')->where('migration', $new)->exists()) {
            $this->line("Already synced: {$new}");

            return;
        }

        $wasRunUnderOldName = DB::table('migrations')->where('migration', $old)->exists();
        $tableExists = Schema::hasTable($table);

        if (! $wasRunUnderOldName && ! $tableExists) {
            $this->line("Skipping {$new}: table `{$table}` does not exist yet.");

            return;
        }

        $batch = (int) (DB::table('migrations')->max('batch') ?? 0);

        DB::table('migrations')->insert([
            'migration' => $new,
            'batch' => $batch,
        ]);

        $this->info("Marked as run: {$new}");

        if ($wasRunUnderOldName) {
            DB::table('migrations')->where('migration', $old)->delete();
            $this->line("Removed stale entry: {$old}");
        }
    }

    /**
     * @param  list<string>  $migrations
     */
    private function pruneStaleMigrationEntries(array $migrations): void
    {
        $removed = DB::table('migrations')->whereIn('migration', $migrations)->delete();

        if ($removed > 0) {
            $this->info("Removed {$removed} squashed additive migration entr".($removed === 1 ? 'y' : 'ies').'.');

            return;
        }

        $this->line('No stale additive public_id migration entries to remove.');
    }
}
