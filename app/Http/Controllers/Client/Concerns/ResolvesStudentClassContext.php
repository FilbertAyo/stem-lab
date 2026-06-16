<?php

namespace App\Http\Controllers\Client\Concerns;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait ResolvesStudentClassContext
{
    protected function studentSchoolClassId(?User $user = null): ?int
    {
        $user ??= auth()->user();

        return $user?->resolvedSchoolClassId();
    }

    protected function studentSchoolClass(?User $user = null): ?SchoolClass
    {
        $classId = $this->studentSchoolClassId($user);

        if ($classId === null) {
            return null;
        }

        return SchoolClass::query()->find($classId);
    }

    protected function classFilterIsActive(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $user?->shouldFilterContentByClass() ?? false;
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     */
    protected function applyStudentClassFilter(Builder $query, ?User $user = null): Builder
    {
        return $query->visibleToStudentClass($this->studentSchoolClassId($user));
    }

    protected function classFilterViewData(?User $user = null): array
    {
        $user ??= auth()->user();
        $studentSchoolClass = $this->studentSchoolClass($user);

        return [
            'studentSchoolClass' => $studentSchoolClass,
            'classFilterActive' => $this->classFilterIsActive($user),
        ];
    }
}
