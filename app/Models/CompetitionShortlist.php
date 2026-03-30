<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompetitionShortlist extends Model
{
    use HasFactory, HasPublicUlid, SoftDeletes;

    protected $fillable = [
        'competition_id',
        'competition_phase_id',
        'competition_idea_id',
        'region_id',
        'rank',
        'status',
        'decided_by',
        'decided_at',
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

    public function idea(): BelongsTo
    {
        return $this->belongsTo(CompetitionIdea::class, 'competition_idea_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
