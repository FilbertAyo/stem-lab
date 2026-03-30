<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompetitionPhase extends Model
{
    use HasFactory, HasPublicUlid, SoftDeletes;

    protected $fillable = [
        'competition_id',
        'phase_key',
        'phase_order',
        'name',
        'description',
        'sequence',
        'opens_at',
        'closes_at',
        'status',
        'is_default',
        'is_required_action',
        'action_type',
    ];

    protected $casts = [
        'opens_at' => 'datetime',
        'closes_at' => 'datetime',
        'is_default' => 'boolean',
        'is_required_action' => 'boolean',
        'sequence' => 'integer',
        'phase_order' => 'integer',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function isOpenNow(): bool
    {
        $now = now();

        if ($this->status !== 'open') {
            return false;
        }

        if ($this->opens_at && $now->lt($this->opens_at)) {
            return false;
        }

        if ($this->closes_at && $now->gt($this->closes_at)) {
            return false;
        }

        return true;
    }
}
