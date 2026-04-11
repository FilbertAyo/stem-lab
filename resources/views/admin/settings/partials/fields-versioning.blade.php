<div class="card">
    <div class="card-header">
        <h6 class="mb-0">Application Versioning</h6>
        <p class="text-muted mb-0 small">Manage application version information and update requirements.</p>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="app_version" class="form-label">App Version <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('app_version') is-invalid @enderror"
                       id="app_version" name="app_version"
                       value="{{ old('app_version', $versioning['app_version'] ?? '1.0.0') }}"
                       placeholder="e.g., 1.0.0" required>
                <small class="form-text text-muted">Semantic version format (major.minor.patch)</small>
                @error('app_version')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label for="app_version_code" class="form-label">Version Code</label>
                <input type="text" class="form-control @error('app_version_code') is-invalid @enderror"
                       id="app_version_code" name="app_version_code"
                       value="{{ old('app_version_code', $versioning['app_version_code'] ?? '') }}"
                       placeholder="e.g., v1.0.0">
                <small class="form-text text-muted">Version code for mobile apps (iOS/Android)</small>
                @error('app_version_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label for="app_build_number" class="form-label">Build Number</label>
                <input type="number" class="form-control @error('app_build_number') is-invalid @enderror"
                       id="app_build_number" name="app_build_number"
                       value="{{ old('app_build_number', $versioning['app_build_number'] ?? '1') }}"
                       min="1" placeholder="e.g., 1">
                <small class="form-text text-muted">Incremental build number for tracking</small>
                @error('app_build_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label for="min_supported_version" class="form-label">Minimum Supported Version</label>
                <input type="text" class="form-control @error('min_supported_version') is-invalid @enderror"
                       id="min_supported_version" name="min_supported_version"
                       value="{{ old('min_supported_version', $versioning['min_supported_version'] ?? '') }}"
                       placeholder="e.g., 1.0.0">
                <small class="form-text text-muted">Oldest version that is still supported</small>
                @error('min_supported_version')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12 mb-3">
                <label for="version_notes" class="form-label">Version Notes / Release Notes</label>
                <textarea class="form-control @error('version_notes') is-invalid @enderror"
                          id="version_notes" name="version_notes" rows="4"
                          placeholder="What's new in this version...">{{ old('version_notes', $versioning['version_notes'] ?? '') }}</textarea>
                <small class="form-text text-muted">Release notes or changelog for this version</small>
                @error('version_notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12 mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="force_update" name="force_update"
                           value="1" {{ old('force_update', $versioning['force_update'] ?? '0') == '1' ? 'checked' : '' }}>
                    <label class="form-check-label" for="force_update">
                        <strong>Force Update</strong>
                    </label>
                    <small class="form-text text-muted d-block">When enabled, users will be required to update to the latest version</small>
                </div>
            </div>
        </div>
    </div>
</div>
