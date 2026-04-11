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
