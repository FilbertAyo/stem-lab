<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUlid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherClub extends Model
{
    use HasPublicUlid;

    protected $table = 'teacher_clubs';

    protected $fillable = [
        'user_id',
        'club_id',
        'school_id',
        'status',
        'is_leader',
    ];

    protected $casts = [
        'is_leader' => 'boolean',
    ];

    /**
     * Get the teacher (user) that belongs to this record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the club that belongs to this record.
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Get the school that belongs to this record.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the students registered under this teacher-club-school.
     */
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function competitionRegistrations()
    {
        return $this->hasMany(CompetitionRegistration::class);
    }

    /**
     * Scope a query to only include approved memberships.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include pending requests.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include leader records.
     */
    public function scopeLeaders(Builder $query): Builder
    {
        return $query->where('is_leader', true);
    }

    /**
     * Scope a query to only include non-leader records.
     */
    public function scopeNonLeaders(Builder $query): Builder
    {
        return $query->where('is_leader', false);
    }

    /**
     * Check if this record represents a leader.
     */
    public function isLeader(): bool
    {
        return $this->is_leader === true;
    }

    /**
     * Approve the membership request.
     */
    public function approve(): bool
    {
        return $this->update(['status' => 'approved']);
    }

    /**
     * Reject the membership request.
     */
    public function reject(): bool
    {
        return $this->update(['status' => 'rejected']);
    }

    /**
     * Get all session reports submitted by this teacher-club.
     */
    public function sessionReports()
    {
        return $this->hasMany(WeeklySessionReport::class);
    }
}
