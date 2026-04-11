<form action="{{ route('admin.settings.update') }}" method="POST" id="admin-settings-main-form">
    @csrf
    @method('PUT')
    <input type="hidden" name="settings_return_tab" value="{{ $activeTab }}">

    <div class="{{ $activeTab === 'versioning' ? '' : 'd-none' }}">
        @include('admin.settings.partials.fields-versioning')
    </div>
    <div class="{{ $activeTab === 'social' ? '' : 'd-none' }}">
        @include('admin.settings.partials.fields-social')
    </div>
    <div class="{{ $activeTab === 'general' ? '' : 'd-none' }}">
        @include('admin.settings.partials.fields-general')
    </div>
</form>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.dashboard') }}" class="btn btn-light">
        <i class="ti ti-x"></i> Cancel
    </a>
    <button type="submit" class="btn btn-primary" form="admin-settings-main-form">
        <i class="ti ti-check"></i> Save Settings
    </button>
</div>
