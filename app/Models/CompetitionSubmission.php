<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetitionSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_participant_id',
        'title',
        'description',
        'methodology',
        'results',
        'conclusion',
        'materials_used',
        'challenges_faced',
        'learning_outcomes',
        'status',
        'submitted_at',
        'score',
        'judge_feedback',
        'judged_by',
        'judged_at',
        'version',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'judged_at' => 'datetime',
        'score' => 'decimal:2',
        'version' => 'integer',
    ];

    /**
     * Get the participant that made this submission.
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(CompetitionParticipant::class, 'competition_participant_id');
    }

    /**
     * Get the judge who reviewed this submission.
     */
    public function judge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'judged_by');
    }

    /**
     * Get all attachments for this submission.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(SubmissionAttachment::class)->orderBy('order');
    }

    /**
     * Get PDF attachments.
     */
    public function pdfs(): HasMany
    {
        return $this->attachments()->where('type', 'pdf');
    }

    /**
     * Get image attachments.
     */
    public function images(): HasMany
    {
        return $this->attachments()->where('type', 'image');
    }

    /**
     * Get document attachments.
     */
    public function documents(): HasMany
    {
        return $this->attachments()->where('type', 'document');
    }

    /**
     * Get the competition through participant.
     */
    public function getCompetitionAttribute()
    {
        return $this->participant?->competition;
    }

    /**
     * Get the teacher club through participant.
     */
    public function getTeacherClubAttribute()
    {
        return $this->participant?->teacherClub;
    }

    /**
     * Get status badge color.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'draft' => 'secondary',
            'submitted' => 'info',
            'under_review' => 'warning',
            'reviewed' => 'success',
            'revision_requested' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Check if this submission can be edited.
     */
    public function canEdit(): bool
    {
        return in_array($this->status, ['draft', 'revision_requested']);
    }

    /**
     * Check if this submission has been judged.
     */
    public function isJudged(): bool
    {
        return $this->status === 'reviewed' && $this->score !== null;
    }

    /**
     * Scope for submitted entries.
     */
    public function scopeSubmitted($query)
    {
        return $query->whereNotNull('submitted_at');
    }

    /**
     * Scope for pending review.
     */
    public function scopePendingReview($query)
    {
        return $query->where('status', 'submitted');
    }

    /**
     * Scope for reviewed submissions.
     */
    public function scopeReviewed($query)
    {
        return $query->where('status', 'reviewed');
    }
}

