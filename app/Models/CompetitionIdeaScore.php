<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionIdeaScore extends Model
{
    use HasFactory, HasPublicUlid;

    protected $fillable = [
        'competition_id',
        'competition_idea_id',
        'judge_user_id',
        'stem_score',
        'product_potential_score',
        'community_relevance_score',
        'creativity_score',
        'local_material_use_score',
        'total_score',
        'feedback',
    ];

    protected $casts = [
        'stem_score' => 'decimal:2',
        'product_potential_score' => 'decimal:2',
        'community_relevance_score' => 'decimal:2',
        'creativity_score' => 'decimal:2',
        'local_material_use_score' => 'decimal:2',
        'total_score' => 'decimal:2',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function idea(): BelongsTo
    {
        return $this->belongsTo(CompetitionIdea::class, 'competition_idea_id');
    }

    public function judge(): BelongsTo
    {
        return $this->belongsTo(User::class, 'judge_user_id');
    }
}
