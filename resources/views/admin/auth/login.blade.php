<x-admin-guest-layout>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="auth-header">
        <a href="#"><img src="{{ asset('images/logo/logo-dark.png') }}" alt="Adilisha STEM Lab" style="height: 45px; width: auto;"></a>
    </div>
    <div class="card my-5">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <h3 class="mb-0"><b>Login</b></h3>
            </div>

            <form method="POST" action="{{ route('admin.login.store') }}">
                @csrf
                <input type="hidden" name="admin_login" value="1">
                <div class="form-group mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" placeholder="Email Address" value="{{ old('email') }}">
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Password">
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>
                {{-- Removed "Remember me" checkbox for admin - sessions should expire properly --}}
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>

            </form>


        </div>
    </div>

</x-admin-guest-layout>

