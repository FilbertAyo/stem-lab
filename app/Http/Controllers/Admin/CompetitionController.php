<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Competition;
use App\Models\CompetitionAward;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompetitionController extends Controller
{
    /**
     * Display a listing of competitions.
     */
    public function index(Request $request): View
    {
        $query = Competition::with(['club', 'creator', 'participants'])
            ->withCount(['participants', 'awards']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by club
        if ($request->filled('club_id')) {
            $query->where('club_id', $request->club_id);
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $competitions = $query->latest()->paginate(15)->withQueryString();
        $clubs = Club::where('is_active', true)->orderBy('name')->get();

        return view('admin.competitions.index', compact('competitions', 'clubs'));
    }

    /**
     * Show the form for creating a new competition.
     */
    public function create(): View
    {
        $clubs = Club::where('is_active', true)->orderBy('name')->get();

        return view('admin.competitions.create', compact('clubs'));
    }

    /**
     * Store a newly created competition.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'terms_and_conditions' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
            'club_id' => ['required', 'exists:clubs,id'],
            'min_members' => ['required', 'integer', 'min:1'],
            'max_members' => ['nullable', 'integer', 'min:1', 'gte:min_members'],
            'min_age' => ['nullable', 'integer', 'min:1', 'max:100'],
            'max_age' => ['nullable', 'integer', 'min:1', 'max:100', 'gte:min_age'],
            'registration_start' => ['nullable', 'date'],
            'registration_deadline' => ['nullable', 'date', 'after_or_equal:registration_start'],
            'competition_start' => ['required', 'date'],
            'competition_end' => ['required', 'date', 'after:competition_start'],
            'results_announcement_date' => ['nullable', 'date', 'after_or_equal:competition_end'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Generate unique slug
        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $counter = 1;
        while (Competition::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }
        $validated['slug'] = $slug;

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('competitions', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['created_by'] = auth()->id();

        $competition = Competition::create($validated);

        return redirect()
            ->route('admin.competitions.show', $competition)
            ->with('status', 'Competition created successfully. You can now add awards.');
    }

    /**
     * Display the specified competition.
     */
    public function show(Competition $competition): View
    {
        $competition->load([
            'club',
            'creator',
            'awards' => fn($q) => $q->orderBy('position'),
            'participants' => fn($q) => $q->with(['teacherClub.user', 'teacherClub.school', 'participantStudents.student'])->latest(),
        ]);

        $competition->loadCount(['participants', 'awards']);

        return view('admin.competitions.show', compact('competition'));
    }

    /**
     * Show the form for editing the specified competition.
     */
    public function edit(Competition $competition): View
    {
        $clubs = Club::where('is_active', true)->orderBy('name')->get();

        return view('admin.competitions.edit', compact('competition', 'clubs'));
    }

    /**
     * Update the specified competition.
     */
    public function update(Request $request, Competition $competition): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'terms_and_conditions' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
            'club_id' => ['required', 'exists:clubs,id'],
            'min_members' => ['required', 'integer', 'min:1'],
            'max_members' => ['nullable', 'integer', 'min:1', 'gte:min_members'],
            'min_age' => ['nullable', 'integer', 'min:1', 'max:100'],
            'max_age' => ['nullable', 'integer', 'min:1', 'max:100', 'gte:min_age'],
            'registration_start' => ['nullable', 'date'],
            'registration_deadline' => ['nullable', 'date', 'after_or_equal:registration_start'],
            'competition_start' => ['required', 'date'],
            'competition_end' => ['required', 'date', 'after:competition_start'],
            'results_announcement_date' => ['nullable', 'date', 'after_or_equal:competition_end'],
            'status' => ['required', Rule::in(['draft', 'published', 'ongoing', 'judging', 'completed', 'cancelled'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Update slug if name changed
        if ($validated['name'] !== $competition->name) {
            $slug = Str::slug($validated['name']);
            $originalSlug = $slug;
            $counter = 1;
            while (Competition::where('slug', $slug)->where('id', '!=', $competition->id)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }
            $validated['slug'] = $slug;
        }

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            // Delete old image
            if ($competition->cover_image && Storage::disk('public')->exists($competition->cover_image)) {
                Storage::disk('public')->delete($competition->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('competitions', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $competition->update($validated);

        return redirect()
            ->route('admin.competitions.show', $competition)
            ->with('status', 'Competition updated successfully.');
    }

    /**
     * Remove the specified competition.
     */
    public function destroy(Competition $competition): RedirectResponse
    {
        // Delete cover image
        if ($competition->cover_image && Storage::disk('public')->exists($competition->cover_image)) {
            Storage::disk('public')->delete($competition->cover_image);
        }

        $competition->delete();

        return redirect()
            ->route('admin.competitions.index')
            ->with('status', 'Competition deleted successfully.');
    }

    /**
     * Update competition status.
     */
    public function updateStatus(Request $request, Competition $competition): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['draft', 'published', 'ongoing', 'judging', 'completed', 'cancelled'])],
        ]);

        $competition->update($validated);

        return redirect()
            ->back()
            ->with('status', 'Competition status updated to ' . ucfirst($validated['status']) . '.');
    }

    /**
     * Store a new award for the competition.
     */
    public function storeAward(Request $request, Competition $competition): RedirectResponse
    {
        $validated = $request->validate([
            'position' => ['required', 'integer', 'min:1', Rule::unique('competition_awards')->where('competition_id', $competition->id)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'prize_type' => ['nullable', 'string', 'max:100'],
            'prize_value' => ['nullable', 'numeric', 'min:0'],
            'prize_image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('prize_image')) {
            $validated['prize_image'] = $request->file('prize_image')->store('competition-awards', 'public');
        }

        $validated['competition_id'] = $competition->id;

        CompetitionAward::create($validated);

        return redirect()
            ->route('admin.competitions.show', $competition)
            ->with('status', 'Award added successfully.');
    }

    /**
     * Update an award.
     */
    public function updateAward(Request $request, Competition $competition, CompetitionAward $award): RedirectResponse
    {
        $validated = $request->validate([
            'position' => ['required', 'integer', 'min:1', Rule::unique('competition_awards')->where('competition_id', $competition->id)->ignore($award->id)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'prize_type' => ['nullable', 'string', 'max:100'],
            'prize_value' => ['nullable', 'numeric', 'min:0'],
            'prize_image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('prize_image')) {
            if ($award->prize_image && Storage::disk('public')->exists($award->prize_image)) {
                Storage::disk('public')->delete($award->prize_image);
            }
            $validated['prize_image'] = $request->file('prize_image')->store('competition-awards', 'public');
        }

        $award->update($validated);

        return redirect()
            ->route('admin.competitions.show', $competition)
            ->with('status', 'Award updated successfully.');
    }

    /**
     * Delete an award.
     */
    public function destroyAward(Competition $competition, CompetitionAward $award): RedirectResponse
    {
        if ($award->prize_image && Storage::disk('public')->exists($award->prize_image)) {
            Storage::disk('public')->delete($award->prize_image);
        }

        $award->delete();

        return redirect()
            ->route('admin.competitions.show', $competition)
            ->with('status', 'Award deleted successfully.');
    }
}

