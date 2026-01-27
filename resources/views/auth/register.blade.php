@extends('layouts.landing')

@section('title', 'Sign Up | ' . config('app.name'))
@section('page', 'signup')

@section('content')
<div class="page-wrapper overflow-hidden">
  <section class="bg-light-gray d-flex align-items-center justify-content-center min-vh-100">
    <div class="container py-3">
      <div class="sign-in card mx-auto shadow-lg">
        <div class="card-body py-8 px-lg-5">
          <a href="{{ url('/') }}" class="mb-3 hstack justify-content-center">
            <img src="{{ asset('images/logo/logo-dark.png') }}" style="height:45px; width:auto;" alt="logo-dark" class="img-fluid">
          </a>

       

          <form class="d-flex flex-column gap-3" method="POST" action="{{ route('register') }}" id="register-form">
            @csrf

            <div>
              <input
                type="text"
                id="name"
                name="name"
                class="form-control border-bottom @error('name') is-invalid @enderror"
                placeholder="Name"
                value="{{ old('name') }}"
                required
                autocomplete="name"
              >
              @error('name')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div>
              <input
                type="email"
                class="form-control border-bottom @error('email') is-invalid @enderror"
                id="email"
                name="email"
                placeholder="Email"
                value="{{ old('email') }}"
                required
                autocomplete="email"
              >
              @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div>
              <select
                name="role"
                id="role"
                class="form-control border-bottom @error('role') is-invalid @enderror"
                required
                aria-label="Who are you?"
              >
                <option value="" disabled {{ old('role') ? '' : 'selected' }}>Who are you?</option>
                <option value="student" {{ old('role') === 'student' ? 'selected' : '' }}>Student</option>
                <option value="teacher" {{ old('role') === 'teacher' ? 'selected' : '' }}>Teacher</option>
              </select>
              @error('role')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div>
              <input
                type="password"
                class="form-control border-bottom @error('password') is-invalid @enderror"
                id="password"
                name="password"
                placeholder="Password"
                required
                autocomplete="new-password"
              >
              @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div>
              <input
                type="password"
                class="form-control border-bottom @error('password_confirmation') is-invalid @enderror"
                id="password_confirmation"
                name="password_confirmation"
                placeholder="Confirm Password"
                required
                autocomplete="new-password"
              >
              @error('password_confirmation')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <button type="submit" class="btn btn-dark w-100 justify-content-center py-2 fw-medium my-7 fs-4 lh-lg">
              Sign Up
            </button>

            <div
              id="register-loading-indicator"
              class="mt-2"
              style="display: none; align-items: center; justify-content: center; gap: 0.75rem;"
              role="status"
              aria-live="polite"
            >
              <span class="register-spinner" aria-hidden="true"></span>
              <span class="sr-only">Creating your account...</span>
            </div>
          </form>

          <p class="text-center mb-1 d-block fw-medium">
            By creating an account, you agree with our
            <a class="text-dark" href="{{ url('/privacy-policy') }}">Privacy</a>
            and
            <a class="text-dark" href="{{ url('/privacy-policy') }}">Policy</a>.
          </p>

          <p class="mb-0 fw-medium text-center">
            Already have an account?
            <a class="text-dark" href="{{ route('login') }}">Sign In</a>
          </p>
        </div>
      </div>
    </div>
  </section>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Optional: show the loading indicator on submit
    const form = document.getElementById('register-form');
    const loading = document.getElementById('register-loading-indicator');

    if (form && loading) {
      form.addEventListener('submit', function() {
        loading.style.display = 'flex';
      });
    }
  });
</script>
@endpush
