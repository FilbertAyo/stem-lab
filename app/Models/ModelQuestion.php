<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModelQuestion extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'model_id',
        'question_type',
        'question_text',
        'options',
        'correct_answer',
        'explanation',
        'order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'options' => 'array',
    ];

    /**
     * Get the 3D model that owns this question.
     */
    public function model3d(): BelongsTo
    {
        return $this->belongsTo(Model3d::class, 'model_id');
    }

    /**
     * Get all answers for this question.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(ModelQuestionAnswer::class, 'question_id');
    }
}
