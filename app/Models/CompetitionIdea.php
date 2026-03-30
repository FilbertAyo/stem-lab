<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompetitionIdea extends Model
{
    use HasFactory, HasPublicUlid, SoftDeletes;

    protected $fillable = [
        'competition_id',
        'competition_registration_id',
        'status',
        'problem_title',
        'problem_statement',
        'proposed_solution',
        'stem_category',
        'community_region_id',
        'community_location',
        'supporting_notes',
        'attachments_meta',
        'submitted_at',
        'reopened_at',
        'reopened_by',
    ];

    protected $casts = [
        'attachments_meta' => 'array',
        'submitted_at' => 'datetime',
        'reopened_at' => 'datetime',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(CompetitionRegistration::class, 'competition_registration_id');
    }

    public function communityRegion(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'community_region_id');
    }

    public function reopenedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reopened_by');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(CompetitionIdeaScore::class, 'competition_idea_id');
    }

    public function shortlist(): HasOne
    {
        return $this->hasOne(CompetitionShortlist::class, 'competition_idea_id');
    }

    public function prototypeSubmission(): HasOne
    {
        return $this->hasOne(CompetitionPrototypeSubmission::class, 'competition_idea_id');
    }

    public function prototypeModification(): HasOne
    {
        return $this->hasOne(CompetitionPrototypeModification::class, 'competition_idea_id');
    }
}
