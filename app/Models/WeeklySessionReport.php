<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeeklySessionReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'weekly_session_id',
        'teacher_club_id',
        'user_id',
        'attendance_count',
        'total_students_registered',
        'girls_present',
        'had_absent_students',
        'absence_reasons',
        'absence_reason_other',
        'activity_type',
        'activity_completed',
        'incompletion_reason',
        'engagement_level',
        'questions_participation',
        'girls_participation',
        'students_special_interest',
        'special_interest_details',
        'learning_understanding',
        'understanding_check_methods',
        'understanding_check_other',
        'students_learned',
        'had_challenges',
        'challenge_types',
        'challenge_other',
        'challenge_description',
        'resources_used',
        'needs_additional_materials',
        'additional_materials_needed',
        'student_confidence',
        'teamwork_quality',
        'stem_attitudes',
        'has_active_projects',
        'project_description',
        'project_completion_status',
        'competition_preparation',
        'competition_name',
        'session_highlight',
        'interesting_questions',
        'additional_comments',
        'needs_support',
        'support_needed',
        'session_satisfaction',
        'would_do_differently',
        'what_worked_well',
        'feedback',
        'engagement_notes',
        'conducted_at',
        'submitted_at',
        'pdf_file',
    ];

    protected $casts = [
        'conducted_at' => 'datetime',
        'submitted_at' => 'datetime',
        'had_absent_students' => 'boolean',
        'students_special_interest' => 'boolean',
        'had_challenges' => 'boolean',
        'needs_additional_materials' => 'boolean',
        'has_active_projects' => 'boolean',
        'competition_preparation' => 'boolean',
        'needs_support' => 'boolean',
        'absence_reasons' => 'array',
        'understanding_check_methods' => 'array',
        'challenge_types' => 'array',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($report) {
            if (empty($report->submitted_at)) {
                $report->submitted_at = now();
            }
        });
    }

    /**
     * Get the weekly session this report belongs to.
     */
    public function weeklySession(): BelongsTo
    {
        return $this->belongsTo(WeeklySession::class);
    }

    /**
     * Get the teacher-club this report belongs to.
     */
    public function teacherClub(): BelongsTo
    {
        return $this->belongsTo(TeacherClub::class);
    }

    /**
     * Get the teacher (user) who submitted this report.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all photos for this report.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(WeeklySessionReportPhoto::class)->orderBy('order');
    }

    /**
     * Scope a query to only include reports for a specific session.
     */
    public function scopeForSession(Builder $query, int $sessionId): Builder
    {
        return $query->where('weekly_session_id', $sessionId);
    }

    /**
     * Scope a query to only include reports from a specific teacher.
     */
    public function scopeForTeacher(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include reports from a specific teacher-club.
     */
    public function scopeForTeacherClub(Builder $query, int $teacherClubId): Builder
    {
        return $query->where('teacher_club_id', $teacherClubId);
    }
}
