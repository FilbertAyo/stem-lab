<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\TeacherClub;
use App\Models\User;
use App\Services\CompetitionEligibilityPoolService;
use App\Services\StudentAccountProvisionService;
use App\Services\StudentCsvBulkImportService;
use App\Services\TeacherClubSharedContextService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StudentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Store a newly registered student from the club show page.
     */
    public function store(Request $request, TeacherClub $teacherClub): RedirectResponse
    {
        $this->authorizeClubPageAccess($teacherClub);

        $validated = $this->validateForClubMutation($request, [
            'name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'string', 'max:32'],
            'gender' => ['required', 'string', 'in:male,female,other'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
        ], $teacherClub);

        $writableClub = $this->resolveWritableTeacherClub($teacherClub);

        return $this->createStudentFromValidated($request, $writableClub, $teacherClub, $validated);
    }

    /**
     * @deprecated Use store() with club-scoped route instead.
     */
    public function storeWithoutRouteClub(Request $request): RedirectResponse
    {
        $validated = $this->validateForClubMutation($request, [
            'teacher_club_id' => ['required', $this->teacherClubIdRule()],
            'name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'string', 'max:32'],
            'gender' => ['required', 'string', 'in:male,female,other'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'redirect_to' => ['nullable', 'string', 'max:2048'],
        ]);

        $pageClub = $this->findTeacherClubByIdOrUlid($validated['teacher_club_id']);
        abort_if($pageClub === null, 404);

        $this->authorizeClubPageAccess($pageClub);

        $writableClub = $this->resolveWritableTeacherClub($pageClub);

        return $this->createStudentFromValidated($request, $writableClub, $pageClub, $validated);
    }

    /**
     * Store multiple students from CSV on the club show page.
     */
    public function bulkStore(Request $request, TeacherClub $teacherClub, StudentCsvBulkImportService $csvImport): RedirectResponse
    {
        $this->authorizeClubPageAccess($teacherClub);

        $validated = $this->validateForClubMutation($request, [
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ], $teacherClub);

        $writableClub = $this->resolveWritableTeacherClub($teacherClub);

        $result = $csvImport->import($writableClub, $request->file('csv_file'));

        if ($result['errors'] !== []) {
            return $this->redirectAfterStudentMutation($request, $teacherClub)
                ->with('error', $csvImport->formatErrorsForFlash($result['errors']))
                ->withInput();
        }

        return $this->redirectAfterStudentMutation(
            $request,
            $teacherClub,
            "Successfully imported {$result['success_count']} student(s).",
        );
    }

    /**
     * @deprecated Use bulkStore() with club-scoped route instead.
     */
    public function bulkStoreWithoutRouteClub(Request $request, StudentCsvBulkImportService $csvImport): RedirectResponse
    {
        $validated = $this->validateForClubMutation($request, [
            'teacher_club_id' => ['required', $this->teacherClubIdRule()],
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
            'redirect_to' => ['nullable', 'string', 'max:2048'],
        ]);

        $pageClub = $this->findTeacherClubByIdOrUlid($validated['teacher_club_id']);
        abort_if($pageClub === null, 404);

        $this->authorizeClubPageAccess($pageClub);

        return $this->bulkStore($request, $pageClub, $csvImport);
    }

    /**
     * Update a student owned by the current teacher (approved club).
     */
    public function update(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeStudentForTeacher($student);

        $validated = $this->validateForClubMutation($request, [
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'string', 'in:male,female,other'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'date_of_birth' => ['required', 'string', 'max:32'],
            'redirect_to' => ['nullable', 'string', 'max:2048'],
        ], $student->teacherClub);

        $parsedDob = app(StudentCsvBulkImportService::class)->parseDateOfBirth($validated['date_of_birth']);
        if ($parsedDob === null) {
            return $this->redirectAfterStudentMutation($request, $student->teacherClub)
                ->withErrors(['date_of_birth' => 'Invalid date of birth. Use DD-MM-YYYY, DD/MM/YYYY, or YYYY-MM-DD.'])
                ->withInput();
        }

        if ($parsedDob->isFuture()) {
            return $this->redirectAfterStudentMutation($request, $student->teacherClub)
                ->withErrors(['date_of_birth' => 'Date of birth cannot be in the future.'])
                ->withInput();
        }

        $age = $parsedDob->age;
        if ($age < 1 || $age > 150) {
            return $this->redirectAfterStudentMutation($request, $student->teacherClub)
                ->withErrors(['date_of_birth' => 'Date of birth must result in age between 1 and 150.'])
                ->withInput();
        }

        $student->update([
            'name' => $validated['name'],
            'gender' => $validated['gender'],
            'school_class_id' => (int) $validated['school_class_id'],
            'date_of_birth' => $parsedDob->format('Y-m-d'),
            'age' => $age,
        ]);

        if ($student->teacherClub) {
            app(CompetitionEligibilityPoolService::class)
                ->refreshOpenRegistrationSnapshots($student->teacherClub);
        }

        return $this->redirectAfterStudentMutation(
            $request,
            $student->teacherClub,
            'Student updated.',
        );
    }

    /**
     * Delete a student owned by the current teacher (approved club).
     */
    public function destroy(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeStudentForTeacher($student);

        $teacherClub = $student->teacherClub;
        $student->delete();

        if ($teacherClub) {
            app(CompetitionEligibilityPoolService::class)
                ->refreshOpenRegistrationSnapshots($teacherClub);
        }

        return $this->redirectAfterStudentMutation(
            $request,
            $teacherClub,
            'Student removed from your club roster.',
        );
    }

    /**
     * Delete multiple students owned by the current teacher (approved clubs).
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $pageClub = $this->findTeacherClubFromRequest($request);

        $validated = $this->validateForClubMutation($request, [
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
            'redirect_to' => ['nullable', 'string', 'max:2048'],
            'teacher_club_id' => ['nullable', $this->teacherClubIdRule()],
        ], $pageClub);

        $manageableTeacherClubIds = $user->approvedCoMemberTeacherClubIds();

        $students = Student::query()
            ->whereIn('id', $validated['student_ids'])
            ->whereIn('teacher_club_id', $manageableTeacherClubIds)
            ->get();

        $deleted = $students->count();
        Student::query()->whereIn('id', $students->pluck('id'))->delete();

        $requested = count(array_unique($validated['student_ids']));

        $teacherClub = $pageClub;
        if ($teacherClub === null && ! empty($validated['teacher_club_id'])) {
            $teacherClub = $this->findTeacherClubByIdOrUlid($validated['teacher_club_id']);
        }
        if ($teacherClub === null && $students->isNotEmpty()) {
            $teacherClub = $students->first()->teacherClub;
        }

        // Refresh each affected membership (bulk can span co-member clubs on shared pages).
        $students->pluck('teacher_club_id')->unique()->filter()->each(function ($teacherClubId): void {
            $club = TeacherClub::query()->find($teacherClubId);
            if ($club) {
                app(CompetitionEligibilityPoolService::class)->refreshOpenRegistrationSnapshots($club);
            }
        });

        if ($deleted === 0) {
            return $this->redirectAfterStudentMutation($request, $teacherClub)
                ->with('error', 'No students could be removed. Refresh the page and try again.');
        }

        if ($deleted < $requested) {
            return $this->redirectAfterStudentMutation(
                $request,
                $teacherClub,
                "Removed {$deleted} student(s). Some rows were skipped because they are no longer in your club roster.",
            );
        }

        return $this->redirectAfterStudentMutation(
            $request,
            $teacherClub,
            "Removed {$deleted} student(s).",
        );
    }

    public function provision(
        Request $request,
        Student $student,
        StudentAccountProvisionService $provision,
    ): RedirectResponse {
        $this->authorize('provision', $student);

        $credential = $provision->provision($student);

        return $this->redirectAfterStudentMutation($request, $student->teacherClub)
            ->with('status', 'Login created for '.$credential->student->name.'. Write down the PIN; it will not be shown again.')
            ->with('provisioned_credentials', $provision->sessionPayload([$credential]));
    }

    public function resetPin(
        Request $request,
        Student $student,
        StudentAccountProvisionService $provision,
    ): RedirectResponse {
        $this->authorize('resetPin', $student);

        $credential = $provision->resetPin($student);

        return $this->redirectAfterStudentMutation($request, $student->teacherClub)
            ->with('status', 'PIN reset for '.$credential->student->name.'. Write down the new PIN; it will not be shown again.')
            ->with('provisioned_credentials', $provision->sessionPayload([$credential]));
    }

    public function bulkProvision(Request $request, StudentAccountProvisionService $provision): RedirectResponse
    {
        $pageClub = $this->findTeacherClubFromRequest($request);

        $validated = $this->validateForClubMutation($request, [
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
            'redirect_to' => ['nullable', 'string', 'max:2048'],
            'teacher_club_id' => ['nullable', $this->teacherClubIdRule()],
        ], $pageClub);

        $user = Auth::user();
        $manageableTeacherClubIds = $user->approvedCoMemberTeacherClubIds();

        $students = Student::query()
            ->whereIn('id', $validated['student_ids'])
            ->whereIn('teacher_club_id', $manageableTeacherClubIds)
            ->get();

        $result = $provision->provisionMany($students);

        $teacherClub = $pageClub;
        if ($teacherClub === null && $students->isNotEmpty()) {
            $teacherClub = $students->first()->teacherClub;
        }

        if ($result['credentials'] === []) {
            return $this->redirectAfterStudentMutation($request, $teacherClub)
                ->with('error', 'No logins were created. Selected students already have accounts.');
        }

        $created = count($result['credentials']);
        $message = "Created logins for {$created} student(s). Write down the PINs; they will not be shown again.";
        if ($result['skipped'] > 0) {
            $message .= " {$result['skipped']} already had an account.";
        }

        return $this->redirectAfterStudentMutation($request, $teacherClub)
            ->with('status', $message)
            ->with('provisioned_credentials', $provision->sessionPayload($result['credentials']));
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function createStudentFromValidated(
        Request $request,
        TeacherClub $writableClub,
        TeacherClub $pageClub,
        array $validated,
    ): RedirectResponse {
        $parsedDob = app(StudentCsvBulkImportService::class)->parseDateOfBirth($validated['date_of_birth']);
        if ($parsedDob === null) {
            return $this->redirectAfterStudentMutation($request, $pageClub)
                ->withErrors(['date_of_birth' => 'Invalid date of birth. Use DD-MM-YYYY, DD/MM/YYYY, or YYYY-MM-DD.'])
                ->withInput();
        }

        if ($parsedDob->isFuture()) {
            return $this->redirectAfterStudentMutation($request, $pageClub)
                ->withErrors(['date_of_birth' => 'Date of birth cannot be in the future.'])
                ->withInput();
        }

        $age = $parsedDob->age;
        if ($age < 1 || $age > 150) {
            return $this->redirectAfterStudentMutation($request, $pageClub)
                ->withErrors(['date_of_birth' => 'Date of birth must result in age between 1 and 150.'])
                ->withInput();
        }

        Student::create([
            'teacher_club_id' => $writableClub->id,
            'name' => $validated['name'],
            'date_of_birth' => $parsedDob->format('Y-m-d'),
            'age' => $age,
            'gender' => $validated['gender'],
            'school_class_id' => $validated['school_class_id'],
        ]);

        app(CompetitionEligibilityPoolService::class)
            ->refreshOpenRegistrationSnapshots($writableClub);

        return $this->redirectAfterStudentMutation($request, $pageClub, 'Student registered successfully!');
    }

    /**
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    private function validateForClubMutation(Request $request, array $rules, ?TeacherClub $pageClub = null): array
    {
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            throw (new ValidationException($validator))
                ->redirectTo($this->resolveReturnUrl($request, $pageClub));
        }

        return $validator->validated();
    }

    private function authorizeClubPageAccess(TeacherClub $teacherClub): void
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless(
            $user->approvedCoMemberTeacherClubIds()->contains($teacherClub->id),
            403,
        );
    }

    /**
     * Resolve the shared roster owner (club leader) for student create/import.
     */
    private function resolveWritableTeacherClub(TeacherClub $pageClub): TeacherClub
    {
        /** @var User $user */
        $user = Auth::user();

        $query = $user->teacherClubs()
            ->where('club_id', $pageClub->club_id)
            ->approved();

        if ($pageClub->school_id === null) {
            $query->whereNull('school_id');
        } else {
            $query->where('school_id', $pageClub->school_id);
        }

        // Ensure the acting teacher belongs to this school club before writing.
        $query->firstOrFail();

        return app(TeacherClubSharedContextService::class)->resolveRosterOwner($pageClub);
    }

    private function teacherClubIdRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (! is_scalar($value) || $value === '') {
                $fail('The selected club membership is invalid.');

                return;
            }

            if ($this->findTeacherClubByIdOrUlid($value) === null) {
                $fail('The selected club membership is invalid.');
            }
        };
    }

    private function findTeacherClubFromRequest(Request $request): ?TeacherClub
    {
        $teacherClubId = $request->input('teacher_club_id');

        if (! is_scalar($teacherClubId) || $teacherClubId === '') {
            return null;
        }

        return $this->findTeacherClubByIdOrUlid($teacherClubId);
    }

    private function findTeacherClubByIdOrUlid(mixed $value): ?TeacherClub
    {
        if (! is_scalar($value) || $value === '') {
            return null;
        }

        $query = TeacherClub::query();

        if (is_numeric($value)) {
            $query->where('id', (int) $value);
        } else {
            $query->where('ulid', (string) $value);
        }

        return $query->first();
    }

    private function authorizeStudentForTeacher(Student $student): void
    {
        $user = Auth::user();
        $canManage = $user->approvedCoMemberTeacherClubIds()->contains($student->teacher_club_id);

        abort_unless($canManage, 403);
    }

    private function redirectAfterStudentMutation(
        Request $request,
        ?TeacherClub $teacherClub = null,
        ?string $message = null,
    ): RedirectResponse {
        $redirect = redirect()->to($this->resolveReturnUrl($request, $teacherClub));

        if ($message !== null) {
            $redirect->with('status', $message);
        }

        return $redirect;
    }

    private function resolveReturnUrl(Request $request, ?TeacherClub $teacherClub = null): string
    {
        if ($teacherClub !== null) {
            return route('client.clubs.show', $teacherClub, absolute: false);
        }

        $teacherClub = $this->findTeacherClubFromRequest($request);
        if ($teacherClub !== null) {
            $user = Auth::user();
            if ($user && $user->approvedCoMemberTeacherClubIds()->contains($teacherClub->id)) {
                return route('client.clubs.show', $teacherClub, absolute: false);
            }
        }

        $target = $request->input('redirect_to');
        if (is_string($target) && $target !== '') {
            $normalized = $this->normalizeRedirectTarget($target);
            if ($normalized !== null) {
                return $normalized;
            }
        }

        return route('client.clubs.index', absolute: false);
    }

    private function normalizeRedirectTarget(string $target): ?string
    {
        if (str_starts_with($target, '/') && ! str_starts_with($target, '//')) {
            return $target;
        }

        $appUrl = rtrim((string) config('app.url'), '/');
        if ($appUrl !== '' && str_starts_with($target, $appUrl)) {
            $path = parse_url($target, PHP_URL_PATH) ?: '/';
            $query = parse_url($target, PHP_URL_QUERY);

            return $query ? $path.'?'.$query : $path;
        }

        return null;
    }
}
