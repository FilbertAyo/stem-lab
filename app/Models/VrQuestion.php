<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VrQuestion extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'question_bank_id',
        'question_type',
        'question_text',
        'options',
        'correct_answer',
        'explanation',
        'points',
        'order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'options' => 'array',
        'points' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Get the question bank that owns this question.
     */
    public function questionBank(): BelongsTo
    {
        return $this->belongsTo(VrQuestionBank::class, 'question_bank_id');
    }

    /**
     * Get all answers for this question.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(VrQuestionAnswer::class, 'question_id');
    }

    /**
     * Check if the given answer is correct.
     */
    public function isCorrectAnswer(string $answer): bool
    {
        if ($this->question_type === 'short_answer') {
            // Case-insensitive comparison for short answers
            return strtolower(trim($answer)) === strtolower(trim($this->correct_answer));
        }
        
        return $answer === $this->correct_answer;
    }
}

