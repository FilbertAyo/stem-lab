<?php

namespace App\DataTransferObjects;

use App\Models\Club;
use App\Models\School;
use App\Models\Student;
use App\Models\User;

readonly class StudentClubMatch
{
    public function __construct(
        public Student $student,
        public Club $club,
        public ?School $school,
        public ?User $leader,
        public string $clubCode,
    ) {}
}
