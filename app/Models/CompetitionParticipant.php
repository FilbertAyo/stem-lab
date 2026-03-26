<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class CompetitionParticipant extends Model
{
    use HasFactory, HasPublicUlid;

    protected $fillable = [
        'competition_id',
        'teacher_club_id',
        'team_name',
        'team_description',
        'status',
        'rejection_reason',
        'award_id',
        'final_score',
        'rank',
        'registered_at',
        'approved_at',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'approved_at' => 'datetime',
        'final_score' => 'decimal:2',
        'rank' => 'integer',
    ];

    /**
     * Get the competition this participant is in.
     */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    /**
     * Get the teacher-club relationship.
     */
    public function teacherClub(): BelongsTo
    {
        return $this->belongsTo(TeacherClub::class);
    }

    /**
     * Get the award won by this participant.
     */
    public function award(): BelongsTo
    {
        return $this->belongsTo(CompetitionAward::class, 'award_id');
    }

    /**
     * Get the students participating from this team.
     */
    public function participantStudents(): HasMany
    {
        return $this->hasMany(CompetitionParticipantStudent::class);
    }

    /**
     * Get the actual student records through the pivot.
     */
    public function students(): HasManyThrough
    {
        return $this->hasManyThrough(
            Student::class,
            CompetitionParticipantStudent::class,
            'competition_participant_id',
            'id',
            'id',
            'student_id'
        );
    }

    /**
     * Get submissions for this participant.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(CompetitionSubmission::class);
    }

    /**
     * Get the teacher (user) through the teacher-club relationship.
     */
    public function getTeacherAttribute()
    {
        return $this->teacherClub?->user;
    }

    /**
     * Get the school through the teacher-club relationship.
     */
    public function getSchoolAttribute()
    {
        return $this->teacherClub?->school;
    }

    /**
     * Get status badge color.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'withdrawn' => 'secondary',
            'disqualified' => 'dark',
            default => 'secondary',
        };
    }

    /**
     * Check if this participant can make submissions.
     */
    public function canSubmit(): bool
    {
        return $this->status === 'approved' && $this->competition->canAcceptSubmissions();
    }

    /**
     * Get the number of participating students.
     */
    public function getStudentCountAttribute(): int
    {
        return $this->participantStudents()->count();
    }

    /**
     * Scope for approved participants.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for pending participants.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}

