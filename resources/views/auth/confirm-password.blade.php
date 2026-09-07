@extends('layouts.client.guest')

@section('title', 'Confirm Password | ' . config('app.name'))
@section('page', 'confirm-password')

@section('content')
<x-auth-shell
  brand-title="A quick security check."
  brand-lead="This is a protected part of your account. Confirm your password to continue."
>
  <span class="auth-shell__eyebrow">Security</span>
  <h2 class="auth-shell__title">Confirm your password</h2>
  <p class="auth-shell__subtitle">For your safety, please re-enter your password to continue.</p>

  <form class="d-flex flex-column gap-3" method="POST" action="{{ route('password.confirm') }}" novalidate>
    @csrf

    <div class="auth-field">
      <label class="auth-field__label" for="password">Password</label>
      <div class="auth-field__control">
        <iconify-icon class="auth-field__icon" icon="lucide:lock" aria-hidden="true"></iconify-icon>
        <input
          type="password"
          name="password"
          id="password"
          placeholder="Your password"
          required
          autofocus
          autocomplete="current-password"
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

    <button type="submit" class="btn btn-primary w-100 justify-content-center mt-1">
      <span>Confirm</span>
      <iconify-icon icon="lucide:shield-check" aria-hidden="true" class="ms-1"></iconify-icon>
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
