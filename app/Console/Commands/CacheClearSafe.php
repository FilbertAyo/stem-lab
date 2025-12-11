<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class CacheClearSafe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-safe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear application cache safely, handling connection failures';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Clearing application cache...');

        // Try to clear cache using the configured driver
        $cacheCleared = false;
        try {
            Cache::flush();
            $this->info('Cache cleared using configured driver.');
            $cacheCleared = true;
        } catch (\Exception $e) {
            $this->warn('Failed to clear cache using configured driver: ' . $e->getMessage());
        }

        // Fallback: Clear file cache directly (always do this as backup)
        $cachePath = storage_path('framework/cache/data');
        if (File::exists($cachePath)) {
            try {
                File::cleanDirectory($cachePath);
                if (!$cacheCleared) {
                    $this->info('File cache cleared successfully.');
                }
            } catch (\Exception $e) {
                $this->warn('Failed to clear file cache: ' . $e->getMessage());
            }
        }

        // Also clear config, route, and view caches
        try {
            $this->call('config:clear');
        } catch (\Exception $e) {
            $this->warn('Failed to clear config cache: ' . $e->getMessage());
        }

        try {
            $this->call('route:clear');
        } catch (\Exception $e) {
            $this->warn('Failed to clear route cache: ' . $e->getMessage());
        }

        try {
            $this->call('view:clear');
        } catch (\Exception $e) {
            $this->warn('Failed to clear view cache: ' . $e->getMessage());
        }

        $this->info('Cache clearing completed.');
        return Command::SUCCESS;
    }
}
