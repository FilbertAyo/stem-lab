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
