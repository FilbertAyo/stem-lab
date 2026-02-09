@extends('layouts.client.guest')

@section('title', 'Confirm Password | ' . config('app.name'))
@section('page', 'confirm-password')

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
            This is a secure area of the application. Please confirm your password before continuing.
          </p>

          <form class="d-flex flex-column gap-3 mt-3" method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <div>
              <input
                type="password"
                name="password"
                id="password"
                placeholder="Password"
                required
                autocomplete="current-password"
                class="form-control border-bottom @error('password') is-invalid @enderror"
              >
              @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <button type="submit" class="btn btn-dark w-100 justify-content-center py-2 fw-medium my-3 fs-5 lh-lg">
              Confirm
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>
</div>
@endsection
