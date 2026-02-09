@extends('layouts.client.guest')

@section('title', 'Sign In | ' . config('app.name'))
@section('page', 'signin')

@section('content')
<div class="page-wrapper overflow-hidden">
  <section class="bg-light-gray d-flex align-items-center justify-content-center min-vh-100">
    <div class="container py-3">
      <div class="sign-in card mx-auto shadow-lg">
        <div class="card-body py-8 px-lg-5">
          <a href="{{ url('/') }}" class="mb-8 hstack justify-content-center">
            <img src="{{ asset('images/logo/logo-dark.png') }}" style="height:45px; width:auto;" alt="logo-dark" class="img-fluid">
          </a>

          <form class="d-flex flex-column gap-3" method="POST" action="{{ route('login') }}">
            @csrf

            <div>
              <input
                type="email"
                class="form-control border-bottom @error('email') is-invalid @enderror"
                id="email"
                name="email"
                value="{{ old('email') }}"
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
                class="form-control border-bottom @error('password') is-invalid @enderror"
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

            <button type="submit" class="btn btn-dark w-100 justify-content-center py-2 fw-medium my-7 fs-4 lh-lg">
              Sign In
            </button>
          </form>

          @if (Route::has('password.request'))
            <a class="text-center mb-1 d-block text-dark fw-medium" href="{{ route('password.request') }}">
              Forget Password?
            </a>
          @endif

          <p class="mb-0 fw-medium text-center">
            Not a member yet?
            <a class="text-dark" href="{{ route('register') }}">Sign Up</a>
          </p>
        </div>
      </div>
    </div>
  </section>
</div>
@endsection
