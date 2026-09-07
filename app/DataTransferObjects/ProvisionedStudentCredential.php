<?php

namespace App\DataTransferObjects;

use App\Models\Student;

readonly class ProvisionedStudentCredential
{
    public function __construct(
        public Student $student,
        public string $loginId,
        public string $pin,
    ) {}

    /**
     * @return array{name: string, login_id: string, pin: string}
     */
    public function toSessionPayload(): array
    {
        return [
            'name' => $this->student->name,
            'login_id' => $this->loginId,
            'pin' => $this->pin,
        ];
    }
}
