<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
<script>
(function () {
    const cfg = document.getElementById('legal-settings-config');
    const legalForm = document.getElementById('admin-settings-legal-form');
    const overlay = document.getElementById('legal-notify-overlay');
    const overlayTitle = document.getElementById('legal-notify-overlay-title');
    const overlayProgress = document.getElementById('legal-notify-overlay-progress');
    const legalBatchStatusUrlTemplate = cfg && cfg.dataset.batchUrlTemplate ? cfg.dataset.batchUrlTemplate : '';
    const settingsIndexUrl = cfg && cfg.dataset.settingsIndexUrl ? cfg.dataset.settingsIndexUrl : '';

    function initTinyMCE() {
        if (typeof tinymce === 'undefined' || !legalForm) {
            return;
        }
        tinymce.init({
            selector: 'textarea[data-tinymce-legal]',
            height: 580,
            menubar: false,
            license_key: 'gpl',
            promotion: false,
            plugins: 'lists link autoresize code',
            toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link | removeformat | code',
            content_style: 'body { font-family: system-ui, sans-serif; font-size: 16px; }',
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTinyMCE);
    } else {
        initTinyMCE();
    }

    function batchStatusUrl(batchId) {
        return legalBatchStatusUrlTemplate.replace('__BATCH_ID__', batchId);
    }

    function showOverlay() {
        overlay.classList.remove('d-none');
        overlay.classList.add('d-flex');
    }

    function hideOverlay() {
        overlay.classList.add('d-none');
        overlay.classList.remove('d-flex');
    }

    async function pollBatch(batchId) {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var attempts = 0;
        var maxAttempts = 120;
        for (;;) {
            attempts += 1;
            if (attempts > maxAttempts) {
                throw new Error('Timed out waiting for notifications. Ensure a queue worker is running (php artisan queue:work).');
            }
            const res = await fetch(batchStatusUrl(batchId), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token,
                },
                credentials: 'same-origin',
            });
            if (!res.ok) {
                throw new Error('Status check failed');
            }
            const data = await res.json();
            overlayProgress.textContent = 'Progress: ' + data.progress + '% (' + (data.total_jobs - data.pending_jobs) + ' / ' + data.total_jobs + ' jobs)';
            if (data.finished) {
                if (data.failed_jobs > 0) {
                    throw new Error(data.failed_jobs + ' batch job(s) failed.');
                }
                return;
            }
            await new Promise(function (r) { setTimeout(r, 1500); });
        }
    }

    if (legalForm) {
        legalForm.addEventListener('submit', async function (e) {
            const submitter = e.submitter;
            if (!submitter || submitter.getAttribute('name') !== 'notify_users' || submitter.value !== '1') {
                return;
            }
            e.preventDefault();
            if (typeof tinymce !== 'undefined') {
                tinymce.triggerSave();
            }
            const privacy = document.getElementById('privacy_policy_html').value;
            const terms = document.getElementById('terms_conditions_html').value;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            overlayTitle.textContent = 'Sending notifications…';
            overlayProgress.textContent = 'Saving and starting emails…';
            showOverlay();

            let res;
            try {
                res = await fetch(legalForm.action, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        privacy_policy_html: privacy,
                        terms_conditions_html: terms,
                        notify_users: 1,
                    }),
                });
            } catch (err) {
                hideOverlay();
                alert('Network error while saving.');
                return;
            }

            const payload = await res.json().catch(function () { return {}; });

            if (res.status === 422) {
                hideOverlay();
                alert(payload.message || 'Could not send notifications.');
                return;
            }

            if (!res.ok) {
                hideOverlay();
                alert(payload.message || ('Request failed (' + res.status + ').'));
                return;
            }

            const batchId = payload.batch_id;
            if (!batchId) {
                hideOverlay();
                alert('Missing batch id from server.');
                return;
            }

            try {
                await pollBatch(batchId);
            } catch (err) {
                hideOverlay();
                alert(err.message || 'Batch failed.');
                return;
            }

            overlayTitle.textContent = 'Done';
            overlayProgress.textContent = 'All notification jobs finished.';
            setTimeout(function () {
                hideOverlay();
                window.location.href = settingsIndexUrl || (window.location.pathname + '?tab=legal');
            }, 600);
        });
    }
})();
</script>
