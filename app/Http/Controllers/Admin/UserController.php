<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
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
    public function index(): View
    {
        $adminUsers = User::where('role', 'admin')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('admin.users.index', compact('adminUsers'));
    }

    /**
     * Display a listing of students and teachers.
     */
    public function students(Request $request): View
    {
        $query = User::whereIn('role', ['student', 'teacher']);
        
        // Filter by role if provided
        if ($request->has('role') && in_array($request->role, ['student', 'teacher'])) {
            $query->where('role', $request->role);
        }
        
        $users = $query->orderBy('role')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $selectedRole = $request->get('role', 'all');
        
        return view('admin.users.students', compact('users', 'selectedRole'));
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

        return redirect()->route('admin.users.index')
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

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('admin.users.index')
            ->with('success', 'Admin user updated successfully.');
    }

    /**
     * Toggle the active status of the specified admin user.
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        // Prevent disabling yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot disable your own account.');
        }

        $user->update([
            'is_active' => !$user->is_active,
        ]);

        $status = $user->is_active ? 'enabled' : 'disabled';
        
        return redirect()->route('admin.users.index')
            ->with('success', "Admin user {$status} successfully.");
    }
}

