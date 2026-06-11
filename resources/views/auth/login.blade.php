@extends('layouts.client.guest')

@section('title', 'Sign In | ' . config('app.name'))
@section('page', 'signin')

@section('content')
@include('partials.auth-top-bar')

<div class="page-wrapper overflow-hidden">
  <section class="bg-light-gray d-flex align-items-center justify-content-center min-vh-100">
    <div class="container py-3">
      <div class="sign-in card mx-auto shadow-lg">
        <div class="card-body py-8 px-lg-5">
          <h4 class="text-center fw-semibold mb-4">Sign In</h4>

          <form class="d-flex flex-column gap-3" method="POST" action="{{ route('login') }}">
            @csrf

            <div>
              <input
                type="email"
                class="form-control border px-2 @error('email') is-invalid @enderror"
                id="email"
                name="email"
                value="{{ old('email', request('email')) }}"
                placeholder="Email"
                required
                autofocus
                autocomplete="username"
              >
              @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div>
              <input
                type="password"
                class="form-control border px-2 @error('password') is-invalid @enderror"
                id="password"
                name="password"
                placeholder="Password"
                required
                autocomplete="current-password"
              >
              @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div class="form-check">
              <input
                type="checkbox"
                class="form-check-input"
                id="remember"
                name="remember"
                value="1"
                {{ old('remember') ? 'checked' : '' }}
              >
              <label class="form-check-label fw-medium" for="remember">Remember me</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 justify-content-center py-2 fw-medium my-3 fs-4 lh-lg">
              Sign In
            </button>
          </form>

          @if (Route::has('password.request'))
            <a class="text-center d-block text-danger fw-medium" href="{{ route('password.request') }}">
              Forget Password?
            </a>
          @endif

          <p class="mb-0 fw-medium text-center">
            Not a member yet?
            <a class="text-primary" href="{{ route('register') }}">Sign Up</a>
          </p>
        </div>
      </div>
    </div>
  </section>
</div>
@endsection
