<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    protected $fillable = [
        'teacher_club_id',
        'name',
        'date_of_birth',
        'age',
        'gender',
        'school_class_id',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

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

    public function competitionRegistrationMembers()
    {
        return $this->hasMany(CompetitionRegistrationStudent::class);
    }
}
