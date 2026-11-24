<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompetitionPrototypeSubmission extends Model
{
    use HasFactory, HasPublicUlid, SoftDeletes;

    protected $fillable = [
        'competition_id',
        'competition_phase_id',
        'competition_registration_id',
        'competition_idea_id',
        'competition_shortlist_id',
        'submitted_by',
        'status',
        'submitted_at',
        'prototype_title',
        'prototype_concept',
        'prototype_objectives',
        'prototype_design_description',
        'prototype_materials',
        'prototype_build_process',
        'prototype_challenges',
        'prototype_test_results',
        'prototype_next_steps',
        'video_path',
        'prototype_media_upload_link',
        'prototype_media_notes',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(CompetitionPhase::class, 'competition_phase_id');
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(CompetitionRegistration::class, 'competition_registration_id');
    }

    public function idea(): BelongsTo
    {
        return $this->belongsTo(CompetitionIdea::class, 'competition_idea_id');
    }

    public function shortlist(): BelongsTo
    {
        return $this->belongsTo(CompetitionShortlist::class, 'competition_shortlist_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function prototypeShortlist(): HasOne
    {
        return $this->hasOne(CompetitionPrototypeShortlist::class, 'competition_prototype_submission_id');
    }

    public function modification(): HasOne
    {
        return $this->hasOne(CompetitionPrototypeModification::class, 'competition_prototype_submission_id');
    }

    public function images(): MorphMany
    {
        return $this->morphMany(CompetitionPrototypeImage::class, 'imageable')->orderBy('sort_order');
    }
}
