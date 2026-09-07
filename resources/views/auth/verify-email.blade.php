@extends('layouts.client.guest')

@section('title', 'Verify Email | ' . config('app.name'))
@section('page', 'verify-email')

@section('content')
<x-auth-shell
  brand-title="One quick step."
  brand-lead="Confirm your email so we can keep your account safe and send you important updates."
  :show-back="false"
>
  <div class="text-center">
    <div class="verify-email__icon" aria-hidden="true">
      <iconify-icon icon="lucide:mail-check"></iconify-icon>
    </div>

    <span class="auth-shell__eyebrow d-inline-block">Verify email</span>
    <h2 class="auth-shell__title">Check your inbox</h2>
    <p class="auth-shell__subtitle">
      Thanks for signing up! Click the link we sent to your email to activate your account.
      Didn&rsquo;t get it? We&rsquo;ll gladly send another.
    </p>

    @if (session('status') == 'verification-link-sent')
      <div class="alert alert-success py-2 px-3 small mb-3" role="status">
        A new verification link has been sent to your email.
      </div>
    @endif

    <div class="d-flex flex-column gap-2 mt-3">
      <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn btn-primary w-100 justify-content-center">
          <iconify-icon icon="lucide:send" aria-hidden="true" class="me-1"></iconify-icon>
          Resend verification email
        </button>
      </form>

      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-link w-100 text-muted fw-medium">
          Log out
        </button>
      </form>
    </div>
  </div>
</x-auth-shell>
@endsection

@push('head')
<style>
  .verify-email__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 68px;
    height: 68px;
    border-radius: 50%;
    background: rgba(var(--bs-primary-rgb), 0.10);
    color: var(--auth-primary);
    font-size: 2rem;
    margin: 0 auto 1.25rem;
    animation: verify-pulse 2.4s ease-in-out infinite;
  }

  @keyframes verify-pulse {
    0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(var(--bs-primary-rgb), 0.25); }
    50%      { transform: scale(1.04); box-shadow: 0 0 0 12px rgba(var(--bs-primary-rgb), 0); }
  }
</style>
@endpush
