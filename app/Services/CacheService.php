<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Cache keys constants
     */
    const ACTIVE_SUBJECTS = 'active_subjects';
    const MODEL_STATS = 'model_stats';
    const USER_PROGRESS_PREFIX = 'user_progress_';
    const QUESTION_BANKS = 'active_question_banks';
    const DASHBOARD_STATS = 'dashboard_stats';

    /**
     * Cache TTL constants (in seconds)
     */
    const TTL_SHORT = 300;      // 5 minutes
    const TTL_MEDIUM = 1800;    // 30 minutes
    const TTL_LONG = 3600;      // 1 hour
    const TTL_VERY_LONG = 86400; // 24 hours

    /**
     * Get active subjects with caching.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActiveSubjects()
    {
        return Cache::remember(self::ACTIVE_SUBJECTS, self::TTL_LONG, function () {
            return \App\Models\Subject::active()->ordered()->get();
        });
    }

    /**
     * Get model statistics with caching.
     * 
     * @return array
     */
    public static function getModelStats()
    {
        return Cache::remember(self::MODEL_STATS, self::TTL_MEDIUM, function () {
            return [
                'total' => \App\Models\Model3d::count(),
                'by_subject' => \App\Models\Model3d::selectRaw('subject_id, count(*) as count')
                    ->groupBy('subject_id')
                    ->get()
                    ->pluck('count', 'subject_id')
                    ->toArray(),
            ];
        });
    }

    /**
     * Get user progress summary with caching.
     * 
     * @param int $userId
     * @return \Illuminate\Support\Collection
     */
    public static function getUserProgress(int $userId)
    {
        $cacheKey = self::USER_PROGRESS_PREFIX . $userId;
        
        return Cache::remember($cacheKey, self::TTL_SHORT, function () use ($userId) {
            return \App\Models\ModelQuestionAnswer::forUser($userId)
                ->selectRaw('
                    model_id,
                    COUNT(*) as answered_count,
                    SUM(CASE WHEN is_correct = true THEN 1 ELSE 0 END) as correct_count
                ')
                ->groupBy('model_id')
                ->get()
                ->keyBy('model_id');
        });
    }

    /**
     * Get active question banks with caching.
     * Only returns question banks where:
     * - The question bank is active
     * - The associated VR experiment is active
     * - The associated subject is active
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActiveQuestionBanks()
    {
        return Cache::remember(self::QUESTION_BANKS, self::TTL_MEDIUM, function () {
            return \App\Models\VrQuestionBank::with(['vrExperiment.schoolClasses', 'vrExperiment.subject'])
                ->where('is_active', true)
                ->whereHas('vrExperiment', function ($query) {
                    $query->where('is_active', true)
                        ->whereHas('subject', function ($subQuery) {
                            $subQuery->where('is_active', true);
                        });
                })
                ->withCount('questions')
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    /**
     * Clear cache for a specific key or pattern.
     * 
     * @param string|array $keys
     * @return void
     */
    public static function forget($keys)
    {
        if (is_array($keys)) {
            foreach ($keys as $key) {
                Cache::forget($key);
            }
        } else {
            Cache::forget($keys);
        }
    }

    /**
     * Clear all application caches.
     * 
     * @return void
     */
    public static function clearAll()
    {
        Cache::forget(self::ACTIVE_SUBJECTS);
        Cache::forget(self::MODEL_STATS);
        Cache::forget(self::QUESTION_BANKS);
        Cache::forget(self::DASHBOARD_STATS);
        
        // Clear user progress caches (pattern-based clearing would require Redis tags)
        // For now, we'll clear on user action
    }

    /**
     * Clear user-specific caches.
     * 
     * @param int $userId
     * @return void
     */
    public static function clearUserCache(int $userId)
    {
        Cache::forget(self::USER_PROGRESS_PREFIX . $userId);
    }

    /**
     * Clear model-related caches.
     * 
     * @return void
     */
    public static function clearModelCaches()
    {
        Cache::forget(self::MODEL_STATS);
        Cache::forget(self::ACTIVE_SUBJECTS);
        
        // Clear all model question caches (pattern-based)
        // Note: For Redis, we could use tags, but for compatibility we'll clear on model update
    }

    /**
     * Clear questions cache for a specific model.
     * 
     * @param int $modelId
     * @return void
     */
    public static function clearModelQuestionsCache(int $modelId)
    {
        Cache::forget("model_questions_{$modelId}");
    }

    /**
     * Clear subject-related caches.
     * 
     * @return void
     */
    public static function clearSubjectCaches()
    {
        Cache::forget(self::ACTIVE_SUBJECTS);
        Cache::forget(self::MODEL_STATS);
        Cache::forget(self::QUESTION_BANKS);
    }
}

