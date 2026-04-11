<form action="{{ route('admin.settings.legal') }}" method="POST" id="admin-settings-legal-form">
    @csrf
    @method('PUT')
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">Privacy policy &amp; terms</h6>
            <p class="text-muted mb-0 small">Edit the content shown on the public Privacy Policy and Terms &amp; Conditions pages. Use the toolbar for bold, lists, and text sizes.</p>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="privacy_policy_html" class="form-label">Privacy policy (HTML)</label>
                <textarea class="form-control @error('privacy_policy_html') is-invalid @enderror"
                          id="privacy_policy_html" name="privacy_policy_html" rows="28"
                          data-tinymce-legal>{{ old('privacy_policy_html', $legal['privacy_policy_html'] ?? '') }}</textarea>
                @error('privacy_policy_html')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="terms_conditions_html" class="form-label">Terms &amp; conditions (HTML)</label>
                <textarea class="form-control @error('terms_conditions_html') is-invalid @enderror"
                          id="terms_conditions_html" name="terms_conditions_html" rows="28"
                          data-tinymce-legal>{{ old('terms_conditions_html', $legal['terms_conditions_html'] ?? '') }}</textarea>
                @error('terms_conditions_html')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <button type="submit" name="notify_users" value="0" class="btn btn-primary" id="legal-save-silent-btn">
                    <i class="ti ti-device-floppy"></i> Save without notification
                </button>
                <button type="submit" name="notify_users" value="1" class="btn btn-outline-primary" id="legal-save-notify-btn">
                    <i class="ti ti-mail"></i> Save and email users
                </button>
            </div>
            <p class="text-muted small mt-3 mb-0">
                “Save and email users” sends a short notice to every non-admin account that has an email address. Run a queue worker (<code>php artisan queue:work</code>) so messages are delivered.
            </p>
        </div>
    </div>
</form>

<div id="legal-settings-config" class="d-none" hidden
    data-batch-url-template="{{ e($legalBatchStatusUrlTemplate ?? '') }}"
    data-settings-index-url="{{ e(route('admin.settings.index', ['tab' => 'legal'])) }}"></div>

<div id="legal-notify-overlay" class="position-fixed top-0 start-0 w-100 h-100 d-none align-items-center justify-content-center" style="z-index: 2000; background: rgba(0,0,0,0.45);" aria-hidden="true">
    <div class="bg-white rounded-3 shadow p-4 text-center" style="min-width: 280px; max-width: 90vw;">
        <div class="spinner-border text-primary mb-3" role="status" aria-label="Loading"></div>
        <p class="mb-1 fw-semibold" id="legal-notify-overlay-title">Sending notifications…</p>
        <p class="text-muted small mb-0" id="legal-notify-overlay-progress">Starting…</p>
    </div>
</div>
