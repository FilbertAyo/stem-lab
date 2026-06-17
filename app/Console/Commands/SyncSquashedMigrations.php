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
}
