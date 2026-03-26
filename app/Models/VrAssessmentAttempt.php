<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VrAssessmentAttempt extends Model
{
    use HasFactory, HasPublicUlid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'question_bank_id',
        'started_at',
        'completed_at',
        'score',
        'correct_answers',
        'total_questions',
        'passed',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'score' => 'decimal:2',
        'correct_answers' => 'integer',
        'total_questions' => 'integer',
        'passed' => 'boolean',
    ];

    /**
     * Get the user who made this attempt.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the question bank for this attempt.
     */
    public function questionBank(): BelongsTo
    {
        return $this->belongsTo(VrQuestionBank::class, 'question_bank_id');
    }

    /**
     * Get all answers for this attempt.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(VrQuestionAnswer::class, 'attempt_id');
    }

    /**
     * Check if the attempt is completed.
     */
    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Check if the attempt is still in progress.
     */
    public function isInProgress(): bool
    {
        return $this->completed_at === null;
    }

    /**
     * Get the duration of the attempt in minutes.
     */
    public function getDurationInMinutesAttribute(): ?int
    {
        if (!$this->completed_at) {
            return null;
        }

        return $this->started_at->diffInMinutes($this->completed_at);
    }
}

