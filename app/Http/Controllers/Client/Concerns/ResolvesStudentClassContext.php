<?php

namespace App\Http\Controllers\Client\Concerns;

use App\Models\User;

trait ResolvesStudentClassContext
{
    protected function studentSchoolClassId(?User $user = null): ?int
    {
        $user ??= auth()->user();

        return $user?->resolvedSchoolClassId();
    }
}
