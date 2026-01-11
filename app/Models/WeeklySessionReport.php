<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklySessionReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'weekly_session_id',
        'teacher_club_id',
        'user_id',
        'attendance_count',
        'feedback',
        'engagement_notes',
        'conducted_at',
        'submitted_at',
    ];

    protected $casts = [
        'conducted_at' => 'datetime',
        'submitted_at' => 'datetime',
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
