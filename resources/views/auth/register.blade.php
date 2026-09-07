@extends('layouts.client.guest')

@section('title', 'Sign Up | ' . config('app.name'))
@section('page', 'signup')

@section('content')
@php
    $hasErrors = $errors->any();
    $selectedRole = old('role');
    $startOnDetails = $hasErrors || $selectedRole;
@endphp

<x-auth-shell
  brand-title="Join Adilisha STEM Labs."
  brand-lead="Create your account to run clubs, follow contests and learn together with your school."
>
  {{-- Step 1: role selection --}}
  <div id="register-step-role" class="{{ $startOnDetails ? 'd-none' : '' }}">
    <span class="auth-shell__eyebrow">Get started</span>
    <h2 class="auth-shell__title">Who are you?</h2>
    <p class="auth-shell__subtitle">Pick the option that fits you best. You can change details later.</p>

    <div class="d-flex flex-column gap-3" role="radiogroup" aria-label="Who are you?">
      <button type="button" class="role-card" data-role="teacher" aria-pressed="false">
        <span class="role-card__icon" aria-hidden="true">
          <iconify-icon icon="lucide:book-open"></iconify-icon>
        </span>
        <span class="role-card__content">
          <span class="role-card__title">Teacher</span>
          <span class="role-card__desc">Run clubs and guide your students</span>
        </span>
        <iconify-icon icon="lucide:chevron-right" class="role-card__arrow" aria-hidden="true"></iconify-icon>
      </button>

      <button type="button" class="role-card" data-role="student" aria-pressed="false">
        <span class="role-card__icon" aria-hidden="true">
          <iconify-icon icon="lucide:graduation-cap"></iconify-icon>
        </span>
        <span class="role-card__content">
          <span class="role-card__title">Student</span>
          <span class="role-card__desc">Join clubs, compete and learn</span>
        </span>
        <iconify-icon icon="lucide:chevron-right" class="role-card__arrow" aria-hidden="true"></iconify-icon>
      </button>

      <button type="button" class="role-card" data-role="parent" aria-pressed="false">
        <span class="role-card__icon" aria-hidden="true">
          <iconify-icon icon="lucide:users"></iconify-icon>
        </span>
        <span class="role-card__content">
          <span class="role-card__title">Parent</span>
          <span class="role-card__desc">Support and follow your child&apos;s progress</span>
        </span>
        <iconify-icon icon="lucide:chevron-right" class="role-card__arrow" aria-hidden="true"></iconify-icon>
      </button>
    </div>

    @error('role')
      <div class="text-danger small mt-3 text-center">{{ $message }}</div>
    @enderror

    <p class="auth-shell__footer text-center mt-4 mb-0">
      Already have an account?
      <a href="{{ route('login') }}">Sign in</a>
    </p>
  </div>

  {{-- Step 2: account details --}}
  <div id="register-step-details" class="{{ $startOnDetails ? '' : 'd-none' }}">
    <div class="d-flex align-items-start justify-content-between gap-2 mb-4">
      <div>
        <span class="auth-shell__eyebrow">Almost there</span>
        <h2 class="auth-shell__title mb-1">Create your account</h2>
        <p class="text-muted small mb-0">Signing up as
          <span id="selected-role-label" class="fw-semibold text-dark">{{ ucfirst($selectedRole ?? '') }}</span>
        </p>
      </div>
      <span id="selected-role-badge" class="role-badge">{{ ucfirst($selectedRole ?? '') }}</span>
    </div>

    <form class="d-flex flex-column gap-3" method="POST" action="{{ route('register') }}" id="register-form" novalidate>
      @csrf
      <input type="hidden" name="role" id="role" value="{{ $selectedRole }}">

      <div class="auth-field">
        <label class="auth-field__label" for="name">Full name</label>
        <div class="auth-field__control">
          <iconify-icon class="auth-field__icon" icon="lucide:user" aria-hidden="true"></iconify-icon>
          <input
            type="text"
            id="name"
            name="name"
            class="form-control @error('name') is-invalid @enderror"
            placeholder="Your full name"
            value="{{ old('name') }}"
            required
            autocomplete="name"
          >
        </div>
        @error('name')
          <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
      </div>

      <div class="auth-field">
        <label class="auth-field__label" for="email">Email</label>
        <div class="auth-field__control">
          <iconify-icon class="auth-field__icon" icon="lucide:mail" aria-hidden="true"></iconify-icon>
          <input
            type="email"
            id="email"
            name="email"
            class="form-control @error('email') is-invalid @enderror"
            placeholder="you@example.com"
            value="{{ old('email') }}"
            required
            autocomplete="email"
          >
        </div>
        @error('email')
          <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
      </div>

      <div class="auth-field">
        <label class="auth-field__label" for="phone">Phone number</label>
        <div class="auth-field__control">
          <iconify-icon class="auth-field__icon" icon="lucide:phone" aria-hidden="true"></iconify-icon>
          <input
            type="tel"
            id="phone"
            name="phone"
            class="form-control @error('phone') is-invalid @enderror"
            placeholder="e.g. +254 700 000 000"
            value="{{ old('phone') }}"
            required
            autocomplete="tel"
          >
        </div>
        @error('phone')
          <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
      </div>

      <div class="auth-field">
        <label class="auth-field__label" for="password">Password</label>
        <div class="auth-field__control">
          <iconify-icon class="auth-field__icon" icon="lucide:lock" aria-hidden="true"></iconify-icon>
          <input
            type="password"
            id="password"
            name="password"
            class="form-control @error('password') is-invalid @enderror"
            placeholder="Create a password"
            required
            autocomplete="new-password"
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
            id="password_confirmation"
            name="password_confirmation"
            class="form-control @error('password_confirmation') is-invalid @enderror"
            placeholder="Re-enter your password"
            required
            autocomplete="new-password"
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

      <button type="submit" class="btn btn-primary w-100 justify-content-center mt-2">
        <span>Create account</span>
        <iconify-icon icon="lucide:arrow-right" aria-hidden="true" class="ms-1"></iconify-icon>
      </button>

      <div
        id="register-loading-indicator"
        class="mt-2 align-items-center justify-content-center gap-2"
        style="display: none;"
        role="status"
        aria-live="polite"
      >
        <span class="register-spinner" aria-hidden="true"></span>
        <span class="text-primary small">Creating your account...</span>
      </div>
    </form>

    <p class="small text-muted text-center mt-3 mb-2">
      By creating an account, you agree to our
      <a class="text-primary text-decoration-none" href="{{ url('/privacy-policy') }}">Privacy Policy</a>
      and
      <a class="text-primary text-decoration-none" href="{{ url('/terms-conditions') }}">Terms &amp; Conditions</a>.
    </p>

    <p class="auth-shell__footer text-center mb-0">
      Already have an account?
      <a href="{{ route('login') }}">Sign in</a>
    </p>
  </div>
</x-auth-shell>
@endsection

@push('head')
<style>
  .role-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    width: 100%;
    padding: 1rem 1.125rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 12px;
    background: #fff;
    text-align: left;
    cursor: pointer;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.15s ease, background 0.2s ease;
  }

  .role-card:hover,
  .role-card:focus-visible {
    border-color: var(--auth-primary);
    box-shadow: 0 8px 22px rgba(var(--bs-primary-rgb), 0.12);
    transform: translateY(-1px);
    outline: none;
  }

  .role-card__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 46px;
    height: 46px;
    border-radius: 12px;
    background: rgba(var(--bs-primary-rgb), 0.10);
    color: var(--auth-primary);
    font-size: 1.35rem;
    flex-shrink: 0;
    transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
  }

  .role-card:hover .role-card__icon,
  .role-card:focus-visible .role-card__icon {
    background: var(--auth-primary);
    color: #fff;
    transform: scale(1.06);
  }

  .role-card__content {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-width: 0;
  }

  .role-card__title {
    font-weight: 600;
    font-size: 1rem;
    color: #111827;
    line-height: 1.3;
  }

  .role-card__desc {
    font-size: 0.825rem;
    color: #6b7280;
    line-height: 1.35;
    margin-top: 0.125rem;
  }

  .role-card__arrow {
    font-size: 1.125rem;
    color: #9ca3af;
    flex-shrink: 0;
    transition: color 0.2s ease, transform 0.2s ease;
  }

  .role-card:hover .role-card__arrow,
  .role-card:focus-visible .role-card__arrow {
    color: var(--auth-primary);
    transform: translateX(2px);
  }

  .role-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.75rem;
    border-radius: 999px;
    background: rgba(var(--bs-primary-rgb), 0.10);
    color: var(--auth-primary);
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    white-space: nowrap;
  }

  .register-spinner {
    width: 1.15rem;
    height: 1.15rem;
    border: 2px solid rgba(var(--bs-primary-rgb), 0.15);
    border-top-color: var(--auth-primary);
    border-radius: 50%;
    animation: register-spin 0.7s linear infinite;
  }

  @keyframes register-spin { to { transform: rotate(360deg); } }

  #register-step-details,
  #register-step-role {
    animation: register-fade-in 0.3s ease both;
  }

  @keyframes register-fade-in {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
  }
</style>
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const stepRole = document.getElementById('register-step-role');
    const stepDetails = document.getElementById('register-step-details');
    const roleInput = document.getElementById('role');
    const roleLabel = document.getElementById('selected-role-label');
    const roleBadge = document.getElementById('selected-role-badge');
    const form = document.getElementById('register-form');
    const loading = document.getElementById('register-loading-indicator');
    const roleCards = document.querySelectorAll('.role-card');

    const roleLabels = {
      student: 'Student',
      parent: 'Parent',
      teacher: 'Teacher',
    };

    function showStep(step) {
      if (step === 'details') {
        stepRole.classList.add('d-none');
        stepDetails.classList.remove('d-none');
        const nameField = document.getElementById('name');
        if (nameField) nameField.focus();
      } else {
        stepDetails.classList.add('d-none');
        stepRole.classList.remove('d-none');
      }
    }

    function selectRole(role) {
      roleInput.value = role;
      const label = roleLabels[role] || role;
      if (roleLabel) roleLabel.textContent = label;
      if (roleBadge) roleBadge.textContent = label;
      showStep('details');
    }

    roleCards.forEach(function (card) {
      card.addEventListener('click', function () {
        selectRole(card.dataset.role);
      });
    });

    // Let the shell back button jump back from details step to role step
    window.handleAuthBack = function () {
      if (!stepDetails.classList.contains('d-none')) {
        showStep('role');
        return true;
      }
      return false;
    };

    if (form && loading) {
      form.addEventListener('submit', function () {
        loading.style.display = 'flex';
      });
    }

    // Password reveal
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
