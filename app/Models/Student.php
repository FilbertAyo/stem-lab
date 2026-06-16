<?php

namespace App\Models;

use App\Services\ShortCodeGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    protected $fillable = [
        'teacher_club_id',
        'user_id',
        'linked_at',
        'name',
        'date_of_birth',
        'age',
        'gender',
        'school_class_id',
        'admission_number',
    ];

    protected static function booted(): void
    {
        static::creating(function (Student $student): void {
            if (blank($student->admission_number)) {
                $student->admission_number = app(ShortCodeGenerator::class)
                    ->generate('students', 'admission_number');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'linked_at' => 'datetime',
        ];
    }

    public function isLinked(): bool
    {
        return $this->user_id !== null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

