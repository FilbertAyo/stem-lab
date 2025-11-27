<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    protected $fillable = [
        'teacher_club_id',
        'name',
        'age',
        'gender',
        'school_class_id',
    ];

    /**
     * Get the teacher-club relationship this student belongs to.
     */
    public function teacherClub(): BelongsTo
    {
        return $this->belongsTo(TeacherClub::class);
    }

    /**
     * Get the school class this student belongs to.
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    /**
     * Get competition participations for this student.
     */
    public function competitionParticipations()
    {
        return $this->hasMany(CompetitionParticipantStudent::class);
    }

    /**
     * Get competitions this student is participating in.
     */
    public function competitions()
    {
        return $this->belongsToMany(CompetitionParticipant::class, 'competition_participant_students')
            ->withPivot(['role'])
            ->withTimestamps();
    }
}
