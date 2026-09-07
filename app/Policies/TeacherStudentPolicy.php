<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use App\Services\Teacher\TeacherStudentScopeService;

class TeacherStudentPolicy
{
    public function __construct(
        private readonly TeacherStudentScopeService $scopeService,
    ) {}

    public function view(User $user, Student $student): bool
    {
        return $this->canManageAsTeacher($user, $student);
    }

    public function provision(User $user, Student $student): bool
    {
        return $this->canManageAsTeacher($user, $student);
    }

    public function resetPin(User $user, Student $student): bool
    {
        return $this->canManageAsTeacher($user, $student);
    }

    private function canManageAsTeacher(User $user, Student $student): bool
    {
        if ($user->role !== 'teacher') {
            return false;
        }

        return $this->scopeService->canManageStudent($user, $student);
    }
}
