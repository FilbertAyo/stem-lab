<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendLegalUpdateNotificationsChunkJob;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Batch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Stevebauman\Purify\Facades\Purify;

class SettingsController extends Controller
{
    public const KEY_PRIVACY_HTML = 'privacy_policy_html';

    public const KEY_TERMS_HTML = 'terms_conditions_html';

    public function index(Request $request): View
    {
        $allowedTabs = ['versioning', 'social', 'general', 'legal'];
        $activeTab = $request->query('tab', 'versioning');
        if (! in_array($activeTab, $allowedTabs, true)) {
            $activeTab = 'versioning';
        }

        $settings = Setting::orderBy('group')->orderBy('key')->get()->groupBy('group');

        $versioning = Setting::where('group', 'versioning')->get()->pluck('value', 'key')->toArray();

        $social = Setting::where('group', 'social')->get()->pluck('value', 'key')->toArray();

        $general = Setting::where('group', 'general')->get()->pluck('value', 'key')->toArray();

        $legal = [
            'privacy_policy_html' => Setting::get(self::KEY_PRIVACY_HTML, ''),
            'terms_conditions_html' => Setting::get(self::KEY_TERMS_HTML, ''),
        ];

        $legalBatchStatusUrlTemplate = route('admin.settings.legal-notification-batch', ['batchId' => '__BATCH_ID__'], false);

        return view('admin.settings.index', compact(
            'settings',
            'versioning',
            'social',
            'general',
            'legal',
            'activeTab',
            'legalBatchStatusUrlTemplate',
        ));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'settings_return_tab' => ['nullable', 'string', Rule::in(['versioning', 'social', 'general'])],
            'app_version' => ['nullable', 'string', 'max:50'],
            'app_version_code' => ['nullable', 'string', 'max:50'],
            'app_build_number' => ['nullable', 'integer', 'min:1'],
            'version_notes' => ['nullable', 'string', 'max:1000'],
            'force_update' => ['nullable', 'boolean'],
            'min_supported_version' => ['nullable', 'string', 'max:50'],

            'instagram_url' => ['nullable', 'url', 'max:255'],
            'whatsapp_url' => ['nullable', 'url', 'max:255'],
            'whatsapp_number' => ['nullable', 'string', 'max:20'],
            'tiktok_url' => ['nullable', 'url', 'max:255'],
            'x_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'telegram_url' => ['nullable', 'url', 'max:255'],

            'site_name' => ['nullable', 'string', 'max:255'],
            'site_email' => ['nullable', 'email', 'max:255'],
            'site_phone' => ['nullable', 'string', 'max:50'],
            'site_address' => ['nullable', 'string', 'max:500'],
            'youtube_video_url' => ['nullable', 'url', 'max:500'],
        ]);

        Setting::set('app_version', $validated['app_version'] ?? '1.0.0', 'text', 'versioning', 'Application version (e.g., 1.0.0)');
        Setting::set('app_version_code', $validated['app_version_code'] ?? '', 'text', 'versioning', 'Version code for mobile apps');
        Setting::set('app_build_number', $validated['app_build_number'] ?? '1', 'number', 'versioning', 'Build number for tracking');
        Setting::set('version_notes', $validated['version_notes'] ?? '', 'text', 'versioning', 'Release notes for this version');
        Setting::set('force_update', $request->has('force_update') ? '1' : '0', 'boolean', 'versioning', 'Force users to update');
        Setting::set('min_supported_version', $validated['min_supported_version'] ?? '', 'text', 'versioning', 'Minimum supported version');

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
            if (! empty($value)) {
                Setting::set($key, $value, 'url', 'social', $description);
            } else {
                Setting::where('key', $key)->delete();
                Cache::forget("setting.{$key}");
            }
        }

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

        $returnTab = $validated['settings_return_tab'] ?? 'versioning';
        if (! in_array($returnTab, ['versioning', 'social', 'general'], true)) {
            $returnTab = 'versioning';
        }

        return redirect()->route('admin.settings.index', ['tab' => $returnTab])
            ->with('status', 'Settings updated successfully.');
    }

    public function updateLegal(Request $request): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'privacy_policy_html' => ['nullable', 'string', 'max:500000'],
            'terms_conditions_html' => ['nullable', 'string', 'max:500000'],
            'notify_users' => ['required', Rule::in([0, 1, '0', '1'])],
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
            }

            return redirect()->route('admin.settings.index', ['tab' => 'legal'])
                ->withErrors($validator)
                ->withInput();
        }

        $privacy = Purify::config('legal')->clean($request->input('privacy_policy_html', '') ?? '');
        $terms = Purify::config('legal')->clean($request->input('terms_conditions_html', '') ?? '');

        Setting::set(self::KEY_PRIVACY_HTML, $privacy, 'text', 'legal', 'Privacy policy HTML for landing page');
        Setting::set(self::KEY_TERMS_HTML, $terms, 'text', 'legal', 'Terms and conditions HTML for landing page');

        $notify = in_array($request->input('notify_users'), [1, '1'], true);

        if (! $notify) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Legal pages saved.']);
            }

            return redirect()->route('admin.settings.index', ['tab' => 'legal'])
                ->with('status', 'Privacy policy and terms saved. No emails were sent.');
        }

        $recipientIds = User::query()
            ->where('role', '!=', 'admin')
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->pluck('id')
            ->all();

        if ($recipientIds === []) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'No recipients found (non-admin users with an email address).'], 422);
            }

            return redirect()->route('admin.settings.index', ['tab' => 'legal'])
                ->with('status', 'Legal pages saved. No notification emails were sent because there are no eligible users.');
        }

        if ($this->pendingLegalNotifyBatchExists()) {
            $message = 'Legal notification emails are already queued or sending. Wait until that finishes before sending again, or clear the queue if it was a mistake.';

            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 409);
            }

            return redirect()->route('admin.settings.index', ['tab' => 'legal'])
                ->withErrors(['notify_users' => $message]);
        }

        $lock = Cache::lock('legal-notify-dispatch', 30);

        if (! $lock->get()) {
            $message = 'Another legal notification send is in progress. Please wait a moment and try again.';

            if ($request->wantsJson()) {
                return response()->json(['message' => $message], 429);
            }

            return redirect()->route('admin.settings.index', ['tab' => 'legal'])
                ->withErrors(['notify_users' => $message]);
        }

        try {
            if ($this->pendingLegalNotifyBatchExists()) {
                $message = 'Legal notification emails are already queued or sending. Wait until that finishes before sending again.';

                if ($request->wantsJson()) {
                    return response()->json(['message' => $message], 409);
                }

                return redirect()->route('admin.settings.index', ['tab' => 'legal'])
                    ->withErrors(['notify_users' => $message]);
            }

            $chunks = array_chunk($recipientIds, 50);
            $jobs = [];
            foreach ($chunks as $chunk) {
                $jobs[] = new SendLegalUpdateNotificationsChunkJob($chunk);
            }

            $adminId = (int) $request->user()->id;
            $batch = Bus::batch($jobs)
                ->name('legal-notify-'.$adminId)
                ->dispatch();

            Cache::put('legal_batch_owner:'.$batch->id, $adminId, now()->addHours(24));
        } finally {
            $lock->release();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Legal pages saved. Email notifications are being sent.',
                'batch_id' => $batch->id,
                'recipient_count' => count($recipientIds),
            ], 202);
        }

        return redirect()->route('admin.settings.index', ['tab' => 'legal'])
            ->with('status', 'Legal pages saved. Email notifications have been queued ('.count($recipientIds).' users). Ensure a queue worker is running.');
    }

    public function legalNotificationBatchStatus(string $batchId): JsonResponse
    {
        $ownerId = Cache::get('legal_batch_owner:'.$batchId);
        if ($ownerId === null || (int) $ownerId !== (int) request()->user()->id) {
            abort(403);
        }

        if (! Schema::hasTable('job_batches')) {
            return response()->json(['message' => 'Job batches table not found.'], 500);
        }

        /** @var Batch|null $batch */
        $batch = Bus::findBatch($batchId);

        if ($batch === null) {
            abort(404);
        }

        $total = $batch->totalJobs;
        $pending = $batch->pendingJobs;
        $progress = $total > 0 ? (int) floor((($total - $pending) / $total) * 100) : 100;

        return response()->json([
            'id' => $batch->id,
            'finished' => $batch->finished(),
            'cancelled' => $batch->cancelled(),
            'failed_jobs' => $batch->failedJobs,
            'total_jobs' => $total,
            'pending_jobs' => $pending,
            'progress' => $progress,
        ]);
    }

    /**
     * True when a prior legal-notification batch still has jobs pending (queued or in progress).
     */
    protected function pendingLegalNotifyBatchExists(): bool
    {
        if (! Schema::hasTable('job_batches')) {
            return false;
        }

        return DB::table('job_batches')
            ->where('name', 'like', 'legal-notify-%')
            ->whereNull('cancelled_at')
            ->where('pending_jobs', '>', 0)
            ->exists();
    }
}
