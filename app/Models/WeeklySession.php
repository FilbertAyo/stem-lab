<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUlid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WeeklySession extends Model
{
    use HasFactory, HasPublicUlid;

    protected $fillable = [
        'club_id',
        'subject_id',
        'vr_experiment_id',
        'created_by',
        'title',
        'description',
        'session_date',
        'status',
    ];

    protected $casts = [
        'session_date' => 'date',
    ];

    /**
     * Get the club that this session belongs to.
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Get the subject associated with this session.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the VR experiment associated with this session.
     */
    public function vrExperiment(): BelongsTo
    {
        return $this->belongsTo(VrExperiment::class);
    }

    /**
     * Get the user who created this session.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the classes associated with this session.
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'weekly_session_classes')
            ->withTimestamps();
    }

    /**
     * Get the 3D models associated with this session.
     */
    public function model3ds(): BelongsToMany
    {
        return $this->belongsToMany(Model3d::class, 'weekly_session_model3ds')
            ->withTimestamps();
    }

    /**
     * Get the status badge color for UI.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'draft' => 'bg-secondary',
            'scheduled' => 'bg-info',
            'active' => 'bg-success',
            'completed' => 'bg-primary',
            default => 'bg-secondary',
        };
    }

    /**
     * Scope a query to only include sessions for a specific club.
     */
    public function scopeForClub(Builder $query, int $clubId): Builder
    {
        return $query->where('club_id', $clubId);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include upcoming sessions.
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('session_date', '>=', now()->toDateString());
    }

    /**
     * Scope a query to only include past sessions.
     */
    public function scopePast(Builder $query): Builder
    {
        return $query->where('session_date', '<', now()->toDateString());
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeByDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('session_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to order sessions by date.
     */
    public function scopeOrderedByDate(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('session_date', $direction);
    }

    /**
     * Get all reports for this session.
     */
    public function sessionReports()
    {
        return $this->hasMany(WeeklySessionReport::class);
    }

    /**
     * Check if a teacher-club has submitted a report for this session.
     */
    public function hasReportFrom(int $teacherClubId): bool
    {
        return $this->sessionReports()->where('teacher_club_id', $teacherClubId)->exists();
    }

    /**
     * Get report from a specific teacher-club.
     */
    public function getReportFrom(int $teacherClubId): ?WeeklySessionReport
    {
        return $this->sessionReports()->where('teacher_club_id', $teacherClubId)->first();
    }

    /**
     * Scope a query to only include sessions visible to teachers (exclude draft).
     */
    public function scopeVisibleToTeachers(Builder $query, array $clubIds = []): Builder
    {
        $query->where('status', '!=', 'draft');
        
        if (!empty($clubIds)) {
            $query->whereIn('club_id', $clubIds);
        }
        
        return $query;
    }
}
