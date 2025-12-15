<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">System Settings</h5>
                    <p class="text-muted mb-0">Manage application versioning, social media links, and general settings.</p>
                </div>
            </div>

            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="versioning-tab" data-bs-toggle="tab" data-bs-target="#versioning" type="button" role="tab">
                            <i class="ti ti-code"></i> Versioning
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="social-tab" data-bs-toggle="tab" data-bs-target="#social" type="button" role="tab">
                            <i class="ti ti-brand-instagram"></i> Social Media
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                            <i class="ti ti-settings"></i> General
                        </button>
                    </li>
                </ul>

                <!-- Tabs Content -->
                <div class="tab-content" id="settingsTabsContent">
                    <!-- Versioning Tab -->
                    <div class="tab-pane fade show active" id="versioning" role="tabpanel">
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
                    </div>

                    <!-- Social Media Tab -->
                    <div class="tab-pane fade" id="social" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Social Media Links</h6>
                                <p class="text-muted mb-0 small">Add your social media profile URLs to display on the website.</p>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="instagram_url" class="form-label">
                                            <i class="ti ti-brand-instagram text-danger"></i> Instagram URL
                                        </label>
                                        <input type="url" class="form-control @error('instagram_url') is-invalid @enderror" 
                                               id="instagram_url" name="instagram_url" 
                                               value="{{ old('instagram_url', $social['instagram_url'] ?? '') }}" 
                                               placeholder="https://instagram.com/yourprofile">
                                        @error('instagram_url')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="tiktok_url" class="form-label">
                                            <i class="ti ti-brand-tiktok"></i> TikTok URL
                                        </label>
                                        <input type="url" class="form-control @error('tiktok_url') is-invalid @enderror" 
                                               id="tiktok_url" name="tiktok_url" 
                                               value="{{ old('tiktok_url', $social['tiktok_url'] ?? '') }}" 
                                               placeholder="https://tiktok.com/@yourprofile">
                                        @error('tiktok_url')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="x_url" class="form-label">
                                            <i class="ti ti-brand-x"></i> X (Twitter) URL
                                        </label>
                                        <input type="url" class="form-control @error('x_url') is-invalid @enderror" 
                                               id="x_url" name="x_url" 
                                               value="{{ old('x_url', $social['x_url'] ?? '') }}" 
                                               placeholder="https://x.com/yourprofile">
                                        @error('x_url')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="linkedin_url" class="form-label">
                                            <i class="ti ti-brand-linkedin text-primary"></i> LinkedIn URL
                                        </label>
                                        <input type="url" class="form-control @error('linkedin_url') is-invalid @enderror" 
                                               id="linkedin_url" name="linkedin_url" 
                                               value="{{ old('linkedin_url', $social['linkedin_url'] ?? '') }}" 
                                               placeholder="https://linkedin.com/company/yourcompany">
                                        @error('linkedin_url')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="facebook_url" class="form-label">
                                            <i class="ti ti-brand-facebook text-primary"></i> Facebook URL
                                        </label>
                                        <input type="url" class="form-control @error('facebook_url') is-invalid @enderror" 
                                               id="facebook_url" name="facebook_url" 
                                               value="{{ old('facebook_url', $social['facebook_url'] ?? '') }}" 
                                               placeholder="https://facebook.com/yourpage">
                                        @error('facebook_url')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="youtube_url" class="form-label">
                                            <i class="ti ti-brand-youtube text-danger"></i> YouTube URL
                                        </label>
                                        <input type="url" class="form-control @error('youtube_url') is-invalid @enderror" 
                                               id="youtube_url" name="youtube_url" 
                                               value="{{ old('youtube_url', $social['youtube_url'] ?? '') }}" 
                                               placeholder="https://youtube.com/@yourchannel">
                                        @error('youtube_url')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="whatsapp_url" class="form-label">
                                            <i class="ti ti-brand-whatsapp text-success"></i> WhatsApp Link URL
                                        </label>
                                        <input type="url" class="form-control @error('whatsapp_url') is-invalid @enderror" 
                                               id="whatsapp_url" name="whatsapp_url" 
                                               value="{{ old('whatsapp_url', $social['whatsapp_url'] ?? '') }}" 
                                               placeholder="https://wa.me/1234567890">
                                        <small class="form-text text-muted">Use wa.me format: https://wa.me/1234567890</small>
                                        @error('whatsapp_url')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="whatsapp_number" class="form-label">
                                            <i class="ti ti-phone"></i> WhatsApp Number
                                        </label>
                                        <input type="text" class="form-control @error('whatsapp_number') is-invalid @enderror" 
                                               id="whatsapp_number" name="whatsapp_number" 
                                               value="{{ old('whatsapp_number', $social['whatsapp_number'] ?? '') }}" 
                                               placeholder="+255 755 123 456">
                                        <small class="form-text text-muted">Display number (without link)</small>
                                        @error('whatsapp_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="telegram_url" class="form-label">
                                            <i class="ti ti-brand-telegram text-info"></i> Telegram URL
                                        </label>
                                        <input type="url" class="form-control @error('telegram_url') is-invalid @enderror" 
                                               id="telegram_url" name="telegram_url" 
                                               value="{{ old('telegram_url', $social['telegram_url'] ?? '') }}" 
                                               placeholder="https://t.me/yourchannel">
                                        @error('telegram_url')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- General Tab -->
                    <div class="tab-pane fade" id="general" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">General Settings</h6>
                                <p class="text-muted mb-0 small">Basic site information and contact details.</p>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="site_name" class="form-label">Site Name</label>
                                        <input type="text" class="form-control @error('site_name') is-invalid @enderror" 
                                               id="site_name" name="site_name" 
                                               value="{{ old('site_name', $general['site_name'] ?? config('app.name')) }}" 
                                               placeholder="Adilisha STEM Lab">
                                        @error('site_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="site_email" class="form-label">Site Email</label>
                                        <input type="email" class="form-control @error('site_email') is-invalid @enderror" 
                                               id="site_email" name="site_email" 
                                               value="{{ old('site_email', $general['site_email'] ?? '') }}" 
                                               placeholder="info@example.com">
                                        @error('site_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="site_phone" class="form-label">Site Phone</label>
                                        <input type="text" class="form-control @error('site_phone') is-invalid @enderror" 
                                               id="site_phone" name="site_phone" 
                                               value="{{ old('site_phone', $general['site_phone'] ?? '') }}" 
                                               placeholder="+255 755 123 456">
                                        @error('site_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label for="site_address" class="form-label">Site Address</label>
                                        <textarea class="form-control @error('site_address') is-invalid @enderror" 
                                                  id="site_address" name="site_address" rows="3" 
                                                  placeholder="Full physical address">{{ old('site_address', $general['site_address'] ?? '') }}</textarea>
                                        @error('site_address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label for="youtube_video_url" class="form-label">
                                            <i class="ti ti-brand-youtube text-danger"></i> YouTube Video URL
                                        </label>
                                        <input type="url" class="form-control @error('youtube_video_url') is-invalid @enderror" 
                                               id="youtube_video_url" name="youtube_video_url" 
                                               value="{{ old('youtube_video_url', $general['youtube_video_url'] ?? '') }}" 
                                               placeholder="https://www.youtube.com/watch?v=...">
                                        <small class="form-text text-muted">YouTube video URL for the About section (e.g., https://www.youtube.com/watch?v=xcJtL7QggTI)</small>
                                        @error('youtube_video_url')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-light">
                        <i class="ti ti-x"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-check"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>

