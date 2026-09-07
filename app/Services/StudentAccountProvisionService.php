<?php

namespace App\Services;

use App\DataTransferObjects\ProvisionedStudentCredential;
use App\Models\SmileyTutorCredential;
use App\Models\Student;
use App\Models\User;
use App\Support\StudentLoginId;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentAccountProvisionService
{
    public function __construct(
        private readonly StudentPinGenerator $pinGenerator,
        private readonly StudentClubLinkService $clubLinkService,
    ) {}

    public function provision(Student $student): ProvisionedStudentCredential
    {
        if ($student->isLinked()) {
            throw ValidationException::withMessages([
                'student' => 'This student already has a platform account.',
            ]);
        }

        $loginId = StudentLoginId::normalize((string) $student->admission_number);

        if ($loginId === '') {
            throw ValidationException::withMessages([
                'student' => 'This student does not have an admission number.',
            ]);
        }

        if (User::query()->where('login_id', $loginId)->exists()) {
            throw ValidationException::withMessages([
                'student' => 'A login already exists for this admission number.',
            ]);
        }

        $pin = $this->pinGenerator->generate();

        return DB::transaction(function () use ($student, $loginId, $pin) {
            $user = User::query()->create([
                'name' => $student->name,
                'email' => null,
                'phone' => null,
                'login_id' => $loginId,
                'uses_pin' => true,
                'password' => $pin,
                'role' => 'student',
                'gender' => $student->gender,
                'date_of_birth' => $student->date_of_birth,
                'school_class_id' => $student->school_class_id,
                'student_onboarding_completed_at' => $this->onboardingCompletedAt($student),
                'is_active' => true,
            ]);

            SmileyTutorCredential::assignToUser($user);

            $student = $this->clubLinkService->attachUser($user, $student);

            return new ProvisionedStudentCredential(
                student: $student,
                loginId: $loginId,
                pin: $pin,
            );
        });
    }

    /**
     * @param  Collection<int, Student>|iterable<Student>  $students
     * @return array{credentials: list<ProvisionedStudentCredential>, skipped: int}
     */
    public function provisionMany(iterable $students): array
    {
        $credentials = [];
        $skipped = 0;

        foreach ($students as $student) {
            if ($student->isLinked()) {
                $skipped++;

                continue;
            }

            $credentials[] = $this->provision($student);
        }

        return [
            'credentials' => $credentials,
            'skipped' => $skipped,
        ];
    }

    public function resetPin(Student $student): ProvisionedStudentCredential
    {
        $user = $student->user;

        if ($user === null || ! $user->usesPinLogin()) {
            throw ValidationException::withMessages([
                'student' => 'PIN reset is only available for students who sign in with a PIN.',
            ]);
        }

        $pin = $this->pinGenerator->generate();
        $loginId = StudentLoginId::normalize((string) ($user->login_id ?: $student->admission_number));

        $user->forceFill([
            'password' => $pin,
            'login_id' => $loginId,
            'uses_pin' => true,
        ])->save();

        return new ProvisionedStudentCredential(
            student: $student->fresh(['user', 'schoolClass']),
            loginId: $loginId,
            pin: $pin,
        );
    }

    /**
     * @param  list<ProvisionedStudentCredential>  $credentials
     * @return list<array{name: string, login_id: string, pin: string}>
     */
    public function sessionPayload(array $credentials): array
    {
        return array_map(
            fn (ProvisionedStudentCredential $credential) => $credential->toSessionPayload(),
            $credentials,
        );
    }

    private function onboardingCompletedAt(Student $student): mixed
    {
        if (blank($student->gender) || blank($student->date_of_birth)) {
            return null;
        }

        return now();
    }
}
