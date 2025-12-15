<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    public function index(): View
    {
        // Get all settings grouped by their group
        $settings = Setting::orderBy('group')->orderBy('key')->get()->groupBy('group');
        
        // Get versioning settings
        $versioning = Setting::where('group', 'versioning')->get()->pluck('value', 'key')->toArray();
        
        // Get social media settings
        $social = Setting::where('group', 'social')->get()->pluck('value', 'key')->toArray();
        
        // Get general settings
        $general = Setting::where('group', 'general')->get()->pluck('value', 'key')->toArray();

        return view('admin.settings.index', compact('settings', 'versioning', 'social', 'general'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_version' => ['nullable', 'string', 'max:50'],
            'app_version_code' => ['nullable', 'string', 'max:50'],
            'app_build_number' => ['nullable', 'integer', 'min:1'],
            'version_notes' => ['nullable', 'string', 'max:1000'],
            'force_update' => ['nullable', 'boolean'],
            'min_supported_version' => ['nullable', 'string', 'max:50'],
            
            // Social media links
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'whatsapp_url' => ['nullable', 'url', 'max:255'],
            'whatsapp_number' => ['nullable', 'string', 'max:20'],
            'tiktok_url' => ['nullable', 'url', 'max:255'],
            'x_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'telegram_url' => ['nullable', 'url', 'max:255'],
            
            // General settings
            'site_name' => ['nullable', 'string', 'max:255'],
            'site_email' => ['nullable', 'email', 'max:255'],
            'site_phone' => ['nullable', 'string', 'max:50'],
            'site_address' => ['nullable', 'string', 'max:500'],
            'youtube_video_url' => ['nullable', 'url', 'max:500'],
        ]);

        // Update versioning settings
        Setting::set('app_version', $validated['app_version'] ?? '1.0.0', 'text', 'versioning', 'Application version (e.g., 1.0.0)');
        Setting::set('app_version_code', $validated['app_version_code'] ?? '', 'text', 'versioning', 'Version code for mobile apps');
        Setting::set('app_build_number', $validated['app_build_number'] ?? '1', 'number', 'versioning', 'Build number for tracking');
        Setting::set('version_notes', $validated['version_notes'] ?? '', 'text', 'versioning', 'Release notes for this version');
        Setting::set('force_update', $request->has('force_update') ? '1' : '0', 'boolean', 'versioning', 'Force users to update');
        Setting::set('min_supported_version', $validated['min_supported_version'] ?? '', 'text', 'versioning', 'Minimum supported version');

        // Update social media settings
        $socialFields = [
            'instagram_url' => 'Instagram Profile URL',
            'whatsapp_url' => 'WhatsApp Link URL',
            'whatsapp_number' => 'WhatsApp Phone Number',
            'tiktok_url' => 'TikTok Profile URL',
            'x_url' => 'X (Twitter) Profile URL',
            'linkedin_url' => 'LinkedIn Profile URL',
            'facebook_url' => 'Facebook Page URL',
            'youtube_url' => 'YouTube Channel URL',
            'telegram_url' => 'Telegram Channel URL',
        ];

        foreach ($socialFields as $key => $description) {
            $value = $validated[$key] ?? '';
            if (!empty($value)) {
                Setting::set($key, $value, 'url', 'social', $description);
            } else {
                // Clear if not provided
                Setting::where('key', $key)->delete();
                Cache::forget("setting.{$key}");
            }
        }

        // Update general settings
        $generalFields = [
            'site_name' => 'Site Name',
            'site_email' => 'Site Email',
            'site_phone' => 'Site Phone',
            'site_address' => 'Site Address',
            'youtube_video_url' => 'YouTube Video URL (for About section)',
        ];

        foreach ($generalFields as $key => $description) {
            $value = $validated[$key] ?? '';
            if ($key === 'site_email') {
                $type = 'email';
            } elseif ($key === 'youtube_video_url') {
                $type = 'url';
            } else {
                $type = 'text';
            }
            Setting::set($key, $value, $type, 'general', $description);
        }

        return redirect()->route('admin.settings.index')->with('status', 'Settings updated successfully.');
    }
}
