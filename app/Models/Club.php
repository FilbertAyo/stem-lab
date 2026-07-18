<?php

namespace App\Models;

use App\Models\Concerns\HasPublicShortId;
use App\Models\Concerns\HasPublicUlid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    use HasFactory;
    use HasPublicUlid;
    use HasPublicShortId {
        HasPublicShortId::getRouteKeyName insteadof HasPublicUlid;
    }

    protected $fillable = [
        'ulid',
        'name',
        'description',
        'image_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the teachers that belong to this club.
     */
    public function teachers()
    {
        return $this->belongsToMany(User::class, 'teacher_clubs')
            ->withPivot(['school_id'])
            ->withTimestamps();
    }

    /**
     * Get the teacher-club pivot records.
     */
    public function teacherClubs()
    {
        return $this->hasMany(TeacherClub::class);
    }

    /**
     * Get the club leader (single TeacherClub where is_leader = true).
     */
    public function leader()
    {
        return $this->hasOne(TeacherClub::class)->where('is_leader', true);
    }

    /**
     * Get pending join requests for this club.
     */
    public function pendingRequests()
    {
        return $this->hasMany(TeacherClub::class)->where('status', 'pending');
    }

    /**
     * Check if club has a leader.
     */
    public function hasLeader(): bool
    {
        return $this->teacherClubs()->where('is_leader', true)->exists();
    }

    /**
     * Get the leader user.
     */
    public function getLeader(): ?User
    {
        $leaderRecord = $this->teacherClubs()->where('is_leader', true)->first();
        return $leaderRecord ? $leaderRecord->user : null;
    }

    /**
     * Get the competitions for this club.
     */
    public function competitions()
    {
        return $this->hasMany(Competition::class);
    }

    /**
     * Get active competitions for this club.
     */
    public function activeCompetitions()
    {
        return $this->competitions()->active()->published();
    }

    /**
     * Get the weekly sessions for this club.
     */
    public function weeklySessions()
    {
        return $this->hasMany(WeeklySession::class);
    }

    /**
     * Scope a query to only include active clubs.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order clubs by name.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('name');
    }
}

