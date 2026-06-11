@extends('layouts.client.guest')

@section('title', 'Verify Email | ' . config('app.name'))
@section('page', 'verify-email')

@section('content')
@include('partials.auth-top-bar')

<div class="page-wrapper overflow-hidden">
  <section class="bg-light-gray d-flex align-items-center justify-content-center min-vh-100">
    <div class="container py-3">
      <div class="sign-in card mx-auto shadow-lg">
        <div class="card-body py-8 px-lg-5">
          <h4 class="text-center fw-semibold mb-3">Verify Email</h4>

          <p class="text-center mb-3">
            Thanks for signing up! Before getting started, please verify your email address by clicking the link we just sent you.
            If you didn't receive the email, we will gladly send you another.
          </p>

          @if (session('status') == 'verification-link-sent')
            <p class="text-center text-success mb-3">
              A new verification link has been sent to the email address you provided during registration.
            </p>
          @endif

          <div class="d-flex flex-column gap-3 mt-3">
            <form method="POST" action="{{ route('verification.send') }}">
              @csrf
              <button type="submit" class="btn btn-primary w-100 justify-content-center py-2 fw-medium fs-5 lh-lg">
                Resend Verification Email
              </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="btn btn-link w-100 text-dark fw-medium text-decoration-underline">
                Log Out
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
@endsection
