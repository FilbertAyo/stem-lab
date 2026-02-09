<?php

namespace App\Providers;

use App\Models\Club;
use App\Models\Model3d;
use App\Models\ModelQuestionAnswer;
use App\Models\Subject;
use App\Models\VrQuestionBank;
use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Clear caches when models are created, updated, or deleted
        $this->registerModelEvents();
        
        // Ensure admin routes generate URLs with admin subdomain
        // This is handled automatically by Route::domain(), but we ensure it's set correctly
        if (request()->getHost() === parse_url(config('app.admin_url'), PHP_URL_HOST)) {
            URL::forceRootUrl(config('app.admin_url'));
        }
    }

    /**
     * Register model event listeners for cache invalidation.
     */
    protected function registerModelEvents(): void
    {
        // Subject model events
        Subject::saved(function () {
            CacheService::clearSubjectCaches();
        });

        Subject::deleted(function () {
            CacheService::clearSubjectCaches();
        });

        // Model3d events
        Model3d::saved(function () {
            CacheService::clearModelCaches();
        });

        Model3d::deleted(function () {
            CacheService::clearModelCaches();
        });

        // ModelQuestionAnswer events - clear user progress cache
        ModelQuestionAnswer::saved(function ($answer) {
            CacheService::clearUserCache($answer->user_id);
        });

        ModelQuestionAnswer::deleted(function ($answer) {
            CacheService::clearUserCache($answer->user_id);
        });

        // VrQuestionBank events
        VrQuestionBank::saved(function () {
            CacheService::forget(CacheService::QUESTION_BANKS);
        });

        VrQuestionBank::deleted(function () {
            CacheService::forget(CacheService::QUESTION_BANKS);
        });

        // Club events
        Club::saved(function () {
            Cache::forget('active_clubs');
        });

        Club::deleted(function () {
            Cache::forget('active_clubs');
        });
    }
}
