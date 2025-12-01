<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModelQuestionAnswer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'model_id',
        'question_id',
        'student_answer',
        'is_correct',
        'score',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_correct' => 'boolean',
    ];

    /**
     * Get the user who answered this question.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the 3D model this answer is for.
     */
    public function model3d(): BelongsTo
    {
        return $this->belongsTo(Model3d::class, 'model_id');
    }

    /**
     * Get the question this answer is for.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(ModelQuestion::class, 'question_id');
    }

    /**
     * Scope a query to only include answers for a specific user.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include answers for a specific model.
     */
    public function scopeForModel(Builder $query, int $modelId): Builder
    {
        return $query->where('model_id', $modelId);
    }

    /**
     * Scope a query to only include correct answers.
     */
    public function scopeCorrect(Builder $query): Builder
    {
        return $query->where('is_correct', true);
    }
}
