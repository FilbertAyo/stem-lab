<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">System Settings</h5>
                    <p class="text-muted mb-0">Manage application versioning, social media links, and general settings.</p>
                </div>
            </div>

            @include('admin.settings.partials.flash')

            @include('admin.settings.partials.nav', ['activeTab' => $activeTab])

            @if (in_array($activeTab, ['versioning', 'social', 'general'], true))
                @include('admin.settings.partials.main-form', ['activeTab' => $activeTab])
            @else
                @include('admin.settings.partials.legal-form', [
                    'legal' => $legal,
                    'legalBatchStatusUrlTemplate' => $legalBatchStatusUrlTemplate,
                ])
            @endif
        </div>
    </div>

    @if ($activeTab === 'legal')
        @push('scripts')
            @include('admin.settings.partials.scripts')
        @endpush
    @endif
</x-admin-layout>
