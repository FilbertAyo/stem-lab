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
        'notified_at',
        'reminder_count',
        'last_reminder_sent_at',
    ];

    protected $casts = [
        'rank' => 'integer',
        'decided_at' => 'datetime',
        'notified_at' => 'datetime',
        'reminder_count' => 'integer',
        'last_reminder_sent_at' => 'datetime',
    ];

    public static function maxReminders(): int
    {
        return max(1, (int) config('competitions.max_shortlist_reminders', 3));
    }

    public function hasSentShortlistNotification(): bool
    {
        return $this->notified_at !== null;
    }

    public function canSendShortlistReminder(): bool
    {
        if ($this->status !== 'shortlisted' || ! $this->hasSentShortlistNotification()) {
            return false;
        }

        return (int) $this->reminder_count < self::maxReminders();
    }

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
