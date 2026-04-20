<section>
    <p class="text-muted mb-4">
        Ensure your account is using a long, random password to stay secure.
    </p>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="row">
            <div class="col-md-12 mb-3">
                <label for="update_password_current_password" class="form-label">Current Password</label>
                <input
                    type="password"
                    class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                    id="update_password_current_password"
                    name="current_password"
                    autocomplete="current-password"
                    placeholder="Enter your current password"
                >
                @error('current_password', 'updatePassword')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="update_password_password" class="form-label">New Password</label>
                <input
                    type="password"
                    class="form-control @error('password', 'updatePassword') is-invalid @enderror border px-2"
                    id="update_password_password"
                    name="password"
                    autocomplete="new-password"
                    placeholder="Enter new password"
                >
                @error('password', 'updatePassword')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label for="update_password_password_confirmation" class="form-label">Confirm New Password</label>
                <input
                    type="password"
                    class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror border px-2"
                    id="update_password_password_confirmation"
                    name="password_confirmation"
                    autocomplete="new-password"
                    placeholder="Confirm new password"
                >
                @error('password_confirmation', 'updatePassword')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="alert alert-info small mb-3 alert-dismissible fade show" role="alert" style="background-color: #e7f5ff; border-color: #b6d4fe;">
            <i class="bi bi-info-circle me-1"></i>
            <strong>Password requirements:</strong> Use at least 8 characters with a mix of letters, numbers, and symbols.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <div class="d-flex align-items-center gap-3">
                <button type="submit" class="badge bg-primary border-0 text-white">
                <i class="bi bi-shield-check me-1"></i> Update Password
            </button>

            @if (session('status') === 'password-updated')
                <span class="text-success small" x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)">
                    <i class="bi bi-check-circle me-1"></i> Password updated successfully!
                </span>
            @endif
        </div>
    </form>
</section>
