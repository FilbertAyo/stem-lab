<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait ScopedToManySchoolClasses
{
    public function schoolClasses(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\SchoolClass::class,
            'model3d_school_class',
            'model3d_id',
            'school_class_id'
        )->withTimestamps();
    }

    /**
     * Limit results to content for the student's class.
     * When no class is set on the student, no filter is applied.
     */
    public function scopeVisibleToStudentClass(Builder $query, ?int $schoolClassId): Builder
    {
        if ($schoolClassId === null) {
            return $query;
        }

        return $query->where(function (Builder $classQuery) use ($schoolClassId) {
            $classQuery->whereDoesntHave('schoolClasses')
                ->orWhereHas('schoolClasses', fn (Builder $relation) => $relation->where('school_classes.id', $schoolClassId));
        });
    }

    public function isAccessibleToStudentClass(?int $schoolClassId): bool
    {
        if ($schoolClassId === null) {
            return true;
        }

        if (! $this->relationLoaded('schoolClasses')) {
            $this->load('schoolClasses');
        }

        if ($this->schoolClasses->isEmpty()) {
            return true;
        }

        return $this->schoolClasses->contains('id', $schoolClassId);
    }
}
