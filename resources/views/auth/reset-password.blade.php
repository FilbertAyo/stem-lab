@extends('layouts.client.guest')

@section('title', 'Reset Password | ' . config('app.name'))
@section('page', 'reset-password')

@section('content')
<x-auth-shell
  brand-title="Pick a fresh password."
  brand-lead="Choose something you’ll remember. You’ll be back in your dashboard in a moment."
>
  <span class="auth-shell__eyebrow">Reset password</span>
  <h2 class="auth-shell__title">Set a new password</h2>
  <p class="auth-shell__subtitle">Make it strong &mdash; at least 8 characters with a mix of letters and numbers.</p>

  <form class="d-flex flex-column gap-3" method="POST" action="{{ route('password.store') }}" novalidate>
    @csrf
    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <div class="auth-field">
      <label class="auth-field__label" for="email">Email</label>
      <div class="auth-field__control">
        <iconify-icon class="auth-field__icon" icon="lucide:mail" aria-hidden="true"></iconify-icon>
        <input
          type="email"
          name="email"
          id="email"
          value="{{ old('email', $request->email) }}"
          placeholder="you@example.com"
          required
          autofocus
          autocomplete="username"
          class="form-control @error('email') is-invalid @enderror"
        >
      </div>
      @error('email')
        <div class="invalid-feedback d-block">{{ $message }}</div>
      @enderror
    </div>

    <div class="auth-field">
      <label class="auth-field__label" for="password">New password</label>
      <div class="auth-field__control">
        <iconify-icon class="auth-field__icon" icon="lucide:lock" aria-hidden="true"></iconify-icon>
        <input
          type="password"
          name="password"
          id="password"
          placeholder="Create a new password"
          required
          autocomplete="new-password"
          class="form-control @error('password') is-invalid @enderror"
        >
        <button
          type="button"
          class="auth-field__toggle"
          data-toggle-password="#password"
          aria-label="Show password"
          aria-pressed="false"
        >
          <iconify-icon icon="lucide:eye" aria-hidden="true"></iconify-icon>
        </button>
      </div>
      @error('password')
        <div class="invalid-feedback d-block">{{ $message }}</div>
      @enderror
    </div>

    <div class="auth-field">
      <label class="auth-field__label" for="password_confirmation">Confirm password</label>
      <div class="auth-field__control">
        <iconify-icon class="auth-field__icon" icon="lucide:lock" aria-hidden="true"></iconify-icon>
        <input
          type="password"
          name="password_confirmation"
          id="password_confirmation"
          placeholder="Re-enter your new password"
          required
          autocomplete="new-password"
          class="form-control @error('password_confirmation') is-invalid @enderror"
        >
        <button
          type="button"
          class="auth-field__toggle"
          data-toggle-password="#password_confirmation"
          aria-label="Show password"
          aria-pressed="false"
        >
          <iconify-icon icon="lucide:eye" aria-hidden="true"></iconify-icon>
        </button>
      </div>
      @error('password_confirmation')
        <div class="invalid-feedback d-block">{{ $message }}</div>
      @enderror
    </div>

    <button type="submit" class="btn btn-primary w-100 justify-content-center mt-1">
      <span>Reset password</span>
      <iconify-icon icon="lucide:arrow-right" aria-hidden="true" class="ms-1"></iconify-icon>
    </button>
  </form>
</x-auth-shell>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-toggle-password]').forEach(function (toggle) {
      toggle.addEventListener('click', function () {
        const target = document.querySelector(toggle.getAttribute('data-toggle-password'));
        if (!target) return;
        const shown = target.type === 'text';
        target.type = shown ? 'password' : 'text';
        toggle.setAttribute('aria-pressed', String(!shown));
        toggle.setAttribute('aria-label', shown ? 'Show password' : 'Hide password');
        const icon = toggle.querySelector('iconify-icon');
        if (icon) icon.setAttribute('icon', shown ? 'lucide:eye' : 'lucide:eye-off');
      });
    });
  });
</script>
@endpush
