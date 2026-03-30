<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompetitionPrototypeShortlist extends Model
{
    use HasFactory, HasPublicUlid, SoftDeletes;

    protected $fillable = [
        'competition_id',
        'competition_phase_id',
        'competition_prototype_submission_id',
        'region_id',
        'rank',
        'status',
        'decided_by',
        'decided_at',
        'feedback',
    ];

    protected $casts = [
        'rank' => 'integer',
        'decided_at' => 'datetime',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(CompetitionPhase::class, 'competition_phase_id');
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(CompetitionPrototypeSubmission::class, 'competition_prototype_submission_id');
    }

    public function mentors()
    {
        return $this->hasMany(CompetitionPrototypeShortlistMentor::class, 'competition_prototype_shortlist_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}

