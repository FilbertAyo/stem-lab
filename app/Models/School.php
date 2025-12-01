<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'district',
        'region',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the teacher-club records for this school.
     */
    public function teacherClubs(): HasMany
    {
        return $this->hasMany(TeacherClub::class);
    }

    /**
     * Get the students registered under this school (through teacher clubs).
     */
    public function students(): HasManyThrough
    {
        return $this->hasManyThrough(Student::class, TeacherClub::class);
    }

    /**
     * Scope a query to only include active schools.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order schools by name.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('name');
    }
}
