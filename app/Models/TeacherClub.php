<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherClub extends Model
{
    protected $table = 'teacher_clubs';

    protected $fillable = [
        'user_id',
        'club_id',
        'school_name',
        'school_address',
        'school_phone',
        'school_email',
        'school_district',
        'school_region',
    ];

    /**
     * Get the teacher (user) that belongs to this record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the club that belongs to this record.
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Get the students registered under this teacher-club-school.
     */
    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
