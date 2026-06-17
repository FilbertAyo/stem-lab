<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionPackageController extends Controller
{
    public function index(): View
    {
        $packages = SubscriptionPackage::query()->ordered()->get();

        return view('admin.subscription-packages.index', compact('packages'));
    }

    public function edit(SubscriptionPackage $subscriptionPackage): View
    {
        return view('admin.subscription-packages.edit', [
            'package' => $subscriptionPackage,
        ]);
    }

    public function update(Request $request, SubscriptionPackage $subscriptionPackage): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price_heading' => ['nullable', 'string', 'max:255'],
            'price_subheading' => ['nullable', 'string', 'max:255'],
            'price_compare' => ['nullable', 'string', 'max:255'],
            'badge_label' => ['nullable', 'string', 'max:255'],
            'cta_label' => ['required', 'string', 'max:255'],
            'cta_action' => ['required', 'string', 'max:64'],
            'features' => ['nullable', 'string'],
            'linked_title' => ['nullable', 'string', 'max:255'],
            'linked_description' => ['nullable', 'string'],
            'linked_features' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['features'] = $this->parseFeatureLines($validated['features'] ?? null);
        $validated['linked_features'] = $this->parseFeatureLines($validated['linked_features'] ?? null);
        $validated['is_active'] = $request->boolean('is_active');

        $subscriptionPackage->update($validated);

        return redirect()
            ->route('admin.subscription-packages.index')
            ->with('success', 'Subscription package updated successfully.');
    }

    /**
     * @return list<string>
     */
    private function parseFeatureLines(?string $input): array
    {
        if ($input === null || trim($input) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $input))));
    }
}
