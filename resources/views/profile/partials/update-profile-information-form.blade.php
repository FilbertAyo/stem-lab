<section>

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
                    class="form-control @error('name') is-invalid @enderror border px-2"
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
                <label for="phone" class="form-label">Phone Number</label>
                <input
                    type="tel"
                    class="form-control @error('phone') is-invalid @enderror border px-2"
                    id="phone"
                    name="phone"
                    value="{{ old('phone', $user->phone) }}"
                    placeholder="+255XX or 07XX"
                    autocomplete="tel"
                    required
                >
                @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input
                    type="email"
                    class="form-control @error('email') is-invalid @enderror border px-2"
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

            @if($user->role === 'student')
                <div class="col-md-6 mb-3">
                    <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                    <input
                        type="date"
                        class="form-control @error('date_of_birth') is-invalid @enderror border px-2"
                        id="date_of_birth"
                        name="date_of_birth"
                        value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}"
                        max="{{ now()->toDateString() }}"
                        required
                    >
                    @error('date_of_birth')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                    <select
                        class="form-select @error('gender') is-invalid @enderror border px-2"
                        id="gender"
                        name="gender"
                        required
                    >
                        <option value="">Select gender...</option>
                        <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                    @error('gender')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="school_class_id" class="form-label">Class <span class="text-muted">(optional)</span></label>
                    <select
                        class="form-select @error('school_class_id') is-invalid @enderror border px-2"
                        id="school_class_id"
                        name="school_class_id"
                    >
                        <option value="">Select class (optional)...</option>
                        @foreach($schoolClasses ?? [] as $class)
                            <option value="{{ $class->id }}" {{ (string) old('school_class_id', $user->school_class_id) === (string) $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('school_class_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            @endif
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
            <button type="submit" class="btn btn-primary">
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
