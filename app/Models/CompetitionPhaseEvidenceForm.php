<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompetitionPhaseEvidenceForm extends Model
{
    use HasFactory, HasPublicUlid, SoftDeletes;

    protected $fillable = [
        'competition_phase_id',
        'google_form_url',
        'updated_by',
    ];

    public function phase(): BelongsTo
    {
        return $this->belongsTo(CompetitionPhase::class, 'competition_phase_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

