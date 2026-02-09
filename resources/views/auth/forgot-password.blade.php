@extends('layouts.client.guest')

@section('title', 'Forgot Password | ' . config('app.name'))
@section('page', 'forgot-password')

@section('content')
<div class="page-wrapper overflow-hidden">
  <section class="bg-light-gray d-flex align-items-center justify-content-center min-vh-100">
    <div class="container py-3">
      <div class="sign-in card mx-auto shadow-lg">
        <div class="card-body py-8 px-lg-5">
          <a href="{{ url('/') }}" class="mb-8 hstack justify-content-center">
            <img src="{{ asset('images/logo/logo-dark.png') }}" style="height:45px; width:auto;" alt="logo-dark" class="img-fluid">
          </a>

          <p class="text-center mb-3">
            Forgot your password? No problem. Enter your email address and we will send you a password reset link.
          </p>

          @if (session('status'))
            <p class="text-center text-success mb-3">
              {{ session('status') }}
            </p>
          @endif

          <form class="d-flex flex-column gap-3 mt-3" method="POST" action="{{ route('password.email') }}">
            @csrf

            <div>
              <input
                type="email"
                name="email"
                id="email"
                value="{{ old('email') }}"
                placeholder="Email"
                required
                autofocus
                autocomplete="email"
                class="form-control border-bottom @error('email') is-invalid @enderror"
              >
              @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <button type="submit" class="btn btn-dark w-100 justify-content-center py-2 fw-medium my-3 fs-5 lh-lg">
              Email Password Reset Link
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>
</div>
@endsection
