<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionParticipantStudent extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_participant_id',
        'student_id',
        'role',
    ];

    /**
     * Get the competition participant this belongs to.
     */
    public function competitionParticipant(): BelongsTo
    {
        return $this->belongsTo(CompetitionParticipant::class);
    }

    /**
     * Get the student.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the competition through the participant.
     */
    public function getCompetitionAttribute()
    {
        return $this->competitionParticipant?->competition;
    }
}

