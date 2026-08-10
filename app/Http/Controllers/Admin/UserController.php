<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompetitionRegistration;
use App\Models\User;
use App\Support\TanzaniaPhone;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of admin users.
     */
    public function index(Request $request): View
    {
        $adminUsers = User::query()
            ->where('role', 'admin')
            ->tap(fn (Builder $query) => $this->applyNameEmailSearch($query, $request))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('adminUsers'));
    }

    /**
     * Display a listing of students.
     */
    public function students(Request $request): View
    {
        $users = $this->usersQueryForRole($request, 'student')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.students', compact('users'));
    }

    /**
     * Display a listing of teachers.
     */
    public function teachers(Request $request): View
    {
        $users = $this->usersQueryForRole($request, 'teacher')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.teachers', compact('users'));
    }

    /**
     * Download students list as PDF.
     */
    public function exportStudents(Request $request): Response
    {
        return $this->exportUsers($request, 'student', 'students', 'Students');
    }

    /**
     * Download teachers list as PDF.
     */
    public function exportTeachers(Request $request): Response
    {
        return $this->exportUsers($request, 'teacher', 'teachers', 'Teachers');
    }

    /**
     * Build the base query used for listing and exporting users of a given role.
     */
    protected function usersQueryForRole(Request $request, string $role, bool $applyDateRange = true): Builder
    {
        return User::query()
            ->where('role', $role)
            ->tap(fn (Builder $query) => $this->applyNameEmailSearch($query, $request))
            ->when($applyDateRange, fn (Builder $query) => $this->applyCreatedAtDateRange($query, $request))
            ->orderBy('created_at', 'desc');
    }

    /**
     * Download a PDF export for students or teachers.
     */
    protected function exportUsers(Request $request, string $role, string $filenamePrefix, string $title): Response
    {
        $validated = $request->validate([
            'scope' => ['required', 'in:range,all'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        $applyDateRange = $validated['scope'] === 'range';
        $users = $this->usersQueryForRole($request, $role, $applyDateRange)->get();

        $scopeLabel = 'all';
        $filterSummary = $validated['scope'] === 'all' ? 'All records' : 'Filtered list';

        if ($applyDateRange) {
            $fromDate = $request->string('from_date')->trim()->value();
            $toDate = $request->string('to_date')->trim()->value();

            if ($fromDate !== '' && $toDate !== '') {
                $scopeLabel = $fromDate.'_to_'.$toDate;
                $filterSummary .= ' · '.$fromDate.' to '.$toDate;
            } elseif ($fromDate !== '') {
                $scopeLabel = 'from_'.$fromDate;
                $filterSummary .= ' · from '.$fromDate;
            } elseif ($toDate !== '') {
                $scopeLabel = 'to_'.$toDate;
                $filterSummary .= ' · to '.$toDate;
            }

            if ($request->filled('q')) {
                $filterSummary .= ' · search: '.$request->input('q');
            }
        } elseif ($request->filled('q')) {
            $filterSummary .= ' · search: '.$request->input('q');
        }

        $filename = $filenamePrefix.'_'.$scopeLabel.'_'.now()->format('Y-m-d').'.pdf';

        $pdf = Pdf::loadView('admin.users.users-list-pdf', [
            'users' => $users,
            'title' => $title,
            'generatedAt' => now(),
            'filterSummary' => $filterSummary,
        ])->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    /**
     * Filter users by name, email, or phone (case-insensitive where supported).
     */
    protected function applyNameEmailSearch(Builder $query, Request $request): void
    {
        $term = $request->string('q')->trim()->value();
        if ($term === '') {
            return;
        }

        $like = '%'.$term.'%';
        $query->where(function (Builder $inner) use ($like): void {
            $inner->where('name', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('phone', 'like', $like);
        });
    }

    /**
     * Filter users by created_at date range (inclusive).
     */
    protected function applyCreatedAtDateRange(Builder $query, Request $request): void
    {
        $fromDate = $request->string('from_date')->trim()->value();
        $toDate = $request->string('to_date')->trim()->value();

        if ($fromDate !== '') {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate !== '') {
            $query->whereDate('created_at', '<=', $toDate);
        }
    }

    protected function redirectToAdminUsersIndex(Request $request): RedirectResponse
    {
        return redirect()->route('admin.users.index', $this->listFilterQuery($request));
    }

    protected function redirectToTeachersIndex(Request $request): RedirectResponse
    {
        return redirect()->route('admin.users.teachers', $this->listFilterQuery($request));
    }

    protected function redirectToStudentsIndex(Request $request): RedirectResponse
    {
        return redirect()->route('admin.users.students', $this->listFilterQuery($request));
    }

    /**
     * @return array<string, string>
     */
    protected function listFilterQuery(Request $request): array
    {
        $query = [];

        if ($request->filled('q')) {
            $query['q'] = $request->input('q');
        }

        if ($request->filled('from_date')) {
            $query['from_date'] = $request->input('from_date');
        }

        if ($request->filled('to_date')) {
            $query['to_date'] = $request->input('to_date');
        }

        return $query;
    }

    /**
     * Store a newly created admin user.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', Rules\Password::defaults()],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
            'is_active' => true,
        ]);

        return $this->redirectToAdminUsersIndex($request)
            ->with('success', 'Admin user created successfully.');
    }

    /**
     * Update the specified admin or teacher user.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        if ($user->role === 'teacher') {
            return $this->updateTeacher($request, $user);
        }

        if ($user->role !== 'admin') {
            return $this->redirectToTeachersIndex($request)
                ->with('error', 'This account cannot be updated from here.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', Rules\Password::defaults()],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return $this->redirectToAdminUsersIndex($request)
            ->with('success', 'Admin user updated successfully.');
    }

    /**
     * Update a teacher's profile details.
     */
    protected function updateTeacher(Request $request, User $user): RedirectResponse
    {
        if ($user->role !== 'teacher') {
            return $this->redirectToTeachersIndex($request)
                ->with('error', 'Only teacher accounts can be updated from this page.');
        }

        $phone = $request->input('phone');
        if (filled($phone)) {
            $normalizedPhone = TanzaniaPhone::normalize((string) $phone);
            if ($normalizedPhone) {
                $request->merge(['phone' => $normalizedPhone]);
            }
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => [
                'nullable',
                'string',
                'max:16',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || $value === '') {
                        return;
                    }

                    if (! TanzaniaPhone::normalize((string) $value)) {
                        $fail('Enter a valid Tanzania phone number (e.g. +255765890921 or 0765890921).');
                    }
                },
            ],
            'password' => ['nullable', Rules\Password::defaults()],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        $this->syncTeacherRegistrationSnapshots($user, [
            'teacher_name' => $updateData['name'],
            'teacher_email' => $updateData['email'],
            'teacher_phone' => $updateData['phone'],
        ]);

        return $this->redirectToTeachersIndex($request)
            ->with('success', 'Teacher updated successfully.');
    }

    /**
     * Keep competition registration contact fields in sync with the teacher account.
     */
    protected function syncTeacherRegistrationSnapshots(User $user, array $contact): void
    {
        CompetitionRegistration::query()
            ->where('teacher_user_id', $user->id)
            ->update($contact);
    }

    /**
     * Toggle the active status of the specified admin user.
     */
    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        // Prevent disabling yourself
        if ($user->id === auth()->id()) {
            return $this->redirectToAdminUsersIndex($request)
                ->with('error', 'You cannot disable your own account.');
        }

        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        $status = $user->is_active ? 'enabled' : 'disabled';

        return $this->redirectToAdminUsersIndex($request)
            ->with('success', "Admin user {$status} successfully.");
    }

    /**
     * Delete a teacher or student user.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if (! in_array($user->role, ['teacher', 'student'], true)) {
            return $this->redirectForDeletedUserRole($request, $user->role)
                ->with('error', 'This account cannot be deleted from here.');
        }

        if ($user->id === auth()->id()) {
            return $this->redirectForDeletedUserRole($request, $user->role)
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        $message = $user->role === 'student'
            ? 'Student deleted successfully.'
            : 'Teacher deleted successfully.';

        return $this->redirectForDeletedUserRole($request, $user->role)
            ->with('success', $message);
    }

    protected function redirectForDeletedUserRole(Request $request, string $role): RedirectResponse
    {
        return match ($role) {
            'student' => $this->redirectToStudentsIndex($request),
            'teacher' => $this->redirectToTeachersIndex($request),
            default => $this->redirectToTeachersIndex($request),
        };
    }
}
