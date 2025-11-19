<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClubController extends Controller
{
    public function index(): View
    {
        $clubs = Club::orderBy('name')->get();

        return view('admin.clubs.index', compact('clubs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:clubs,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('clubs', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        unset($validated['image']);

        Club::create($validated);

        return redirect()->route('admin.clubs.index')->with('status', 'Club created successfully.');
    }

    public function update(Request $request, Club $club): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('clubs', 'name')->ignore($club->id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            if ($club->image_path && Storage::disk('public')->exists($club->image_path)) {
                Storage::disk('public')->delete($club->image_path);
            }

            $validated['image_path'] = $request->file('image')->store('clubs', 'public');
        }

        unset($validated['image']);

        $club->update($validated);

        return redirect()->route('admin.clubs.index')->with('status', 'Club updated successfully.');
    }

    public function toggleStatus(Club $club): RedirectResponse
    {
        $club->update([
            'is_active' => ! $club->is_active,
        ]);

        $message = $club->is_active ? 'Club enabled successfully.' : 'Club disabled successfully.';

        return redirect()->route('admin.clubs.index')->with('status', $message);
    }
}

