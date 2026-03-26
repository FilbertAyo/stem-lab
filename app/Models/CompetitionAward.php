<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CompetitionAward extends Model
{
    use HasFactory, HasPublicUlid;

    protected $fillable = [
        'competition_id',
        'position',
        'title',
        'description',
        'prize_type',
        'prize_value',
        'prize_image',
    ];

    protected $casts = [
        'position' => 'integer',
        'prize_value' => 'decimal:2',
    ];

    /**
     * Get the competition this award belongs to.
     */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    /**
     * Get the winner (participant) for this award.
     */
    public function winner(): HasOne
    {
        return $this->hasOne(CompetitionParticipant::class, 'award_id');
    }

    /**
     * Get the position label (1st, 2nd, 3rd, etc.)
     */
    public function getPositionLabelAttribute(): string
    {
        return match($this->position) {
            1 => '1st Place',
            2 => '2nd Place',
            3 => '3rd Place',
            default => $this->position . 'th Place',
        };
    }

    /**
     * Get formatted prize value.
     */
    public function getFormattedPrizeAttribute(): string
    {
        if ($this->prize_value) {
            return number_format($this->prize_value, 2) . ' TZS';
        }
        return 'N/A';
    }
}

