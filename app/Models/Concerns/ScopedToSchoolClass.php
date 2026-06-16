<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait ScopedToSchoolClass
{
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(\App\Models\SchoolClass::class);
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
            $classQuery->where('school_class_id', $schoolClassId)
                ->orWhereNull('school_class_id');
        });
    }

    public function isAccessibleToStudentClass(?int $schoolClassId): bool
    {
        if ($schoolClassId === null) {
            return true;
        }

        if ($this->school_class_id === null) {
            return true;
        }

        return (int) $this->school_class_id === $schoolClassId;
    }
}
