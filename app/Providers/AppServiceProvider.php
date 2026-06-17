<?php

namespace App\Providers;

use App\Listeners\SendWelcomeEmail;
use App\Models\Club;
use App\Models\Competition;
use App\Models\Model3d;
use App\Models\ModelQuestionAnswer;
use App\Models\Platform;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\SubscriptionPackage;
use App\Models\TeacherClub;
use App\Models\User;
use App\Models\VrAssessmentAttempt;
use App\Models\VrExperiment;
use App\Models\VrQuestionBank;
use App\Models\WeeklySession;
use App\Models\WeeklySessionReport;
use App\Services\CacheService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
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
        $this->bindUlidWithLegacyId('school', School::class);
        $this->bindUlidWithLegacyId('club', Club::class);
        $this->bindUlidWithLegacyId('schoolClass', SchoolClass::class);
        $this->bindUlidWithLegacyId('teacherClub', TeacherClub::class);
        $this->bindUlidWithLegacyId('project', Project::class);
        $this->bindUlidWithLegacyId('projectCategory', ProjectCategory::class);
        $this->bindUlidWithLegacyId('platform', Platform::class);
        $this->bindUlidWithLegacyId('competition', Competition::class);
        $this->bindUlidWithLegacyId('user', User::class);
        $this->bindUlidWithLegacyId('model3d', Model3d::class);
        $this->bindUlidWithLegacyId('weeklySession', WeeklySession::class);
        $this->bindUlidWithLegacyId('report', WeeklySessionReport::class);
        $this->bindUlidWithLegacyId('vrExperiment', VrExperiment::class);
        $this->bindUlidWithLegacyId('questionBank', VrQuestionBank::class);
        $this->bindUlidWithLegacyId('attempt', VrAssessmentAttempt::class);
        $this->bindUlidWithLegacyId('subject', Subject::class);

        Event::listen(Registered::class, SendWelcomeEmail::class);

        // Use custom "remember me" duration from config (e.g. 14 days) for client login
        $rememberMinutes = config('auth.guards.web.remember');
        if ($rememberMinutes > 0) {
            /** @var \Illuminate\Auth\SessionGuard $guard */
            $guard = Auth::guard('web');
            $guard->setRememberDuration($rememberMinutes);
        }

        // Clear caches when models are created, updated, or deleted
        $this->registerModelEvents();

        View::composer('client.back.partials.package', function ($view): void {
            $publicSchoolClub = Club::query()
                ->active()
                ->where('name', 'like', '%Chomoza%')
                ->ordered()
                ->first()
                ?? Club::query()->active()->ordered()->first();

            $view->with([
                'subscriptionPackages' => SubscriptionPackage::query()->active()->ordered()->get(),
                'publicSchoolClub' => $publicSchoolClub,
            ]);
        });

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

    /**
     * Resolve route model bindings by ULID with numeric ID fallback.
     */
    protected function bindUlidWithLegacyId(string $parameter, string $modelClass): void
    {
        Route::bind($parameter, function (string $value) use ($modelClass) {
            return $modelClass::query()
                ->where(function ($query) use ($value): void {
                    $query->where('ulid', $value);

                    if (ctype_digit($value)) {
                        $query->orWhere('id', (int) $value);
                    }
                })
                ->firstOrFail();
        });
    }
}
