<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $users = User::query()
            ->where('role', 'student')
            ->tap(fn (Builder $query) => $this->applyNameEmailSearch($query, $request))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.students', compact('users'));
    }

    /**
     * Display a listing of teachers.
     */
    public function teachers(Request $request): View
    {
        $users = User::query()
            ->where('role', 'teacher')
            ->tap(fn (Builder $query) => $this->applyNameEmailSearch($query, $request))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.teachers', compact('users'));
    }

    /**
     * Filter users by name or email (case-insensitive where supported).
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
                ->orWhere('email', 'like', $like);
        });
    }

    protected function redirectToAdminUsersIndex(Request $request): RedirectResponse
    {
        $query = [];
        if ($request->filled('q')) {
            $query['q'] = $request->input('q');
        }

        return redirect()->route('admin.users.index', $query);
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
     * Update the specified admin user.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
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
}
