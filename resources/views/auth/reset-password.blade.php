@extends('layouts.guest')

@section('title', 'Reset Password | ' . config('app.name'))
@section('page', 'reset-password')

@section('content')
<div class="page-wrapper overflow-hidden">
  <section class="bg-light-gray d-flex align-items-center justify-content-center min-vh-100">
    <div class="container py-3">
      <div class="sign-in card mx-auto shadow-lg">
        <div class="card-body py-8 px-lg-5">
          <a href="{{ url('/') }}" class="mb-8 hstack justify-content-center">
            <img src="{{ asset('images/logo/logo-dark.png') }}" style="height:45px; width:auto;" alt="logo-dark" class="img-fluid">
          </a>

          <form class="d-flex flex-column gap-3" method="POST" action="{{ route('password.store') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
              <input
                type="email"
                name="email"
                id="email"
                value="{{ old('email', $request->email) }}"
                placeholder="Email"
                required
                autofocus
                autocomplete="username"
                class="form-control border-bottom @error('email') is-invalid @enderror"
              >
              @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div>
              <input
                type="password"
                name="password"
                id="password"
                placeholder="Password"
                required
                autocomplete="new-password"
                class="form-control border-bottom @error('password') is-invalid @enderror"
              >
              @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div>
              <input
                type="password"
                name="password_confirmation"
                id="password_confirmation"
                placeholder="Confirm Password"
                required
                autocomplete="new-password"
                class="form-control border-bottom @error('password_confirmation') is-invalid @enderror"
              >
              @error('password_confirmation')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <button type="submit" class="btn btn-dark w-100 justify-content-center py-2 fw-medium my-3 fs-5 lh-lg">
              Reset Password
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>
</div>
@endsection
