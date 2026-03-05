<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Platform;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PlatformController extends Controller
{
    public function index(): View
    {
        $platforms = Platform::ordered()->get();

        return view('admin.platforms.index', compact('platforms'));
    }

    public function create(): View
    {
        return view('admin.platforms.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = Str::slug($validated['name']);
        $base = $slug;
        $i = 1;
        while (Platform::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        Platform::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'icon' => $validated['icon'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.platforms.index')
            ->with('success', 'Platform added successfully.');
    }

    public function edit(Platform $platform): View
    {
        return view('admin.platforms.edit', compact('platform'));
    }

    public function update(Request $request, Platform $platform): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($platform->name !== $validated['name']) {
            $slug = Str::slug($validated['name']);
            $base = $slug;
            $i = 1;
            while (Platform::where('slug', $slug)->where('id', '!=', $platform->id)->exists()) {
                $slug = $base . '-' . $i;
                $i++;
            }
            $platform->slug = $slug;
        }

        $platform->name = $validated['name'];
        $platform->icon = $validated['icon'] ?? null;
        $platform->is_active = $request->boolean('is_active', true);
        $platform->save();

        return redirect()
            ->route('admin.platforms.index')
            ->with('success', 'Platform updated successfully.');
    }

    public function destroy(Platform $platform): RedirectResponse
    {
        $platform->delete();

        return redirect()
            ->route('admin.platforms.index')
            ->with('success', 'Platform removed.');
    }
}
