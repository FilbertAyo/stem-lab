@extends('layouts.client.guest')

@section('title', 'Forgot Password | ' . config('app.name'))
@section('page', 'forgot-password')

@section('content')
<x-auth-shell
  brand-title="No worries, we’ve got you."
  brand-lead="Enter the email tied to your account and we’ll send a link so you can pick a new password."
>
  <span class="auth-shell__eyebrow">Password help</span>
  <h2 class="auth-shell__title">Forgot your password?</h2>
  <p class="auth-shell__subtitle">Type your email and we&rsquo;ll send you a reset link right away.</p>

  @if (session('status'))
    <div class="alert alert-success py-2 px-3 small mb-3" role="status">
      {{ session('status') }}
    </div>
  @endif

  <form class="d-flex flex-column gap-3" method="POST" action="{{ route('password.email') }}" novalidate>
    @csrf

    <div class="auth-field">
      <label class="auth-field__label" for="email">Email</label>
      <div class="auth-field__control">
        <iconify-icon class="auth-field__icon" icon="lucide:mail" aria-hidden="true"></iconify-icon>
        <input
          type="email"
          name="email"
          id="email"
          value="{{ old('email') }}"
          placeholder="you@example.com"
          required
          autofocus
          autocomplete="email"
          class="form-control @error('email') is-invalid @enderror"
        >
      </div>
      @error('email')
        <div class="invalid-feedback d-block">{{ $message }}</div>
      @enderror
    </div>

    <button type="submit" class="btn btn-primary w-100 justify-content-center mt-1">
      <span>Send reset link</span>
      <iconify-icon icon="lucide:arrow-right" aria-hidden="true" class="ms-1"></iconify-icon>
    </button>
  </form>

  <p class="auth-shell__footer text-center mt-4 mb-0">
    Remembered it?
    <a href="{{ route('login') }}">Back to sign in</a>
  </p>
</x-auth-shell>
@endsection
