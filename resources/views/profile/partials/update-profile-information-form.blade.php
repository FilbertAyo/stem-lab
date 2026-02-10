<section>
    <p class="text-muted mb-4">
        Update your account's profile information and email address.
    </p>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input
                    type="text"
                    class="form-control @error('name') is-invalid @enderror"
                    id="name"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    required
                    autofocus
                    autocomplete="name"
                >
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input
                    type="email"
                    class="form-control @error('email') is-invalid @enderror"
                    id="email"
                    name="email"
                    value="{{ old('email', $user->email) }}"
                    required
                    autocomplete="username"
                >
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="mt-2">
                        <p class="text-warning small mb-1">
                            <i class="bi bi-exclamation-circle me-1"></i>
                            Your email address is unverified.
                        </p>
                        <button form="send-verification" class="btn btn-link btn-sm p-0 text-decoration-underline">
                            Click here to re-send the verification email.
                        </button>

                        @if (session('status') === 'verification-link-sent')
                            <p class="text-success small mt-2">
                                <i class="bi bi-check-circle me-1"></i>
                                A new verification link has been sent to your email address.
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Role</label>
                <input type="text" class="form-control bg-light-soft" value="{{ ucfirst($user->role) }}" disabled>
                <small class="text-muted">Contact administrator to change your role.</small>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Account Status</label>
                <input type="text" class="form-control bg-light-soft" value="{{ $user->is_active ? 'Active' : 'Inactive' }}" disabled>
            </div>
        </div>

        <div class="d-flex align-items-center gap-3 mt-2">
            <button type="submit" class="btn btn-primary text-white">
                <i class="bi bi-check-lg me-1"></i> Save Changes
            </button>

            @if (session('status') === 'profile-updated')
                <span class="text-success small" x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)">
                    <i class="bi bi-check-circle me-1"></i> Profile updated successfully!
                </span>
            @endif
        </div>
    </form>
</section>
