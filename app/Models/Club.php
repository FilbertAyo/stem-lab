<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the teachers that belong to this club.
     */
    public function teachers()
    {
        return $this->belongsToMany(User::class, 'teacher_clubs')
            ->withPivot(['school_id'])
            ->withTimestamps();
    }

    /**
     * Get the teacher-club pivot records.
     */
    public function teacherClubs()
    {
        return $this->hasMany(TeacherClub::class);
    }

    /**
     * Get the competitions for this club.
     */
    public function competitions()
    {
        return $this->hasMany(Competition::class);
    }

    /**
     * Get active competitions for this club.
     */
    public function activeCompetitions()
    {
        return $this->competitions()->active()->published();
    }
}

