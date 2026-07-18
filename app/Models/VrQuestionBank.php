<?php

namespace App\Models;

use App\Models\Concerns\HasPublicShortId;
use App\Models\Concerns\HasPublicUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VrQuestionBank extends Model
{
    use HasFactory;
    use HasPublicUlid;
    use HasPublicShortId {
        HasPublicShortId::getRouteKeyName insteadof HasPublicUlid;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vr_experiment_id',
        'title',
        'description',
        'time_limit',
        'passing_score',
        'is_active',
        'display_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'time_limit' => 'integer',
        'passing_score' => 'integer',
        'display_order' => 'integer',
    ];

    /**
     * Get the VR experiment that owns this question bank.
     */
    public function vrExperiment(): BelongsTo
    {
        return $this->belongsTo(VrExperiment::class);
    }

    /**
     * Get all questions in this bank.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(VrQuestion::class, 'question_bank_id');
    }

    /**
     * Get all assessment attempts for this bank.
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(VrAssessmentAttempt::class, 'question_bank_id');
    }

    /**
     * Get the total points possible for this question bank.
     */
    public function getTotalPointsAttribute(): int
    {
        return $this->questions()->sum('points');
    }

    /**
     * Get the question count for this bank.
     */
    public function getQuestionCountAttribute(): int
    {
        return $this->questions()->count();
    }
}

