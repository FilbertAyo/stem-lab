@extends('layouts.client.guest')

@section('title', 'Sign In | ' . config('app.name'))
@section('page', 'signin')

@section('content')
@php
    $loginMethod = old(
        'login_method',
        $errors->has('login_id') ? 'student_id' : request('method', 'email')
    );
    if (! in_array($loginMethod, ['email', 'student_id'], true)) {
        $loginMethod = 'email';
    }
    $isStudentId = $loginMethod === 'student_id';
@endphp

<x-auth-shell
  brand-title="Welcome back."
  brand-lead="Sign in to keep exploring clubs, labs and contests with your school."
>
  <span class="auth-shell__eyebrow">Sign in</span>
  <h2 class="auth-shell__title">Great to see you again</h2>
  <p class="auth-shell__subtitle">Pick how you want to sign in </p>

  @if (session('status'))
    <div class="alert alert-warning py-2 px-3 small mb-3" role="alert">
      {{ session('status') }}
    </div>
  @endif

  {{-- Modern segmented tabs --}}
  <div
    class="auth-tabs"
    role="tablist"
    aria-label="Login method"
    data-active="{{ $loginMethod }}"
  >
    <span class="auth-tabs__thumb" aria-hidden="true"></span>
    <button
      type="button"
      class="auth-tabs__btn {{ $isStudentId ? '' : 'is-active' }}"
      id="login-method-email"
      data-login-method="email"
      role="tab"
      aria-selected="{{ $isStudentId ? 'false' : 'true' }}"
    >
      <iconify-icon icon="lucide:mail" aria-hidden="true"></iconify-icon>
      <span>Email</span>
    </button>
    <button
      type="button"
      class="auth-tabs__btn {{ $isStudentId ? 'is-active' : '' }}"
      id="login-method-student-id"
      data-login-method="student_id"
      role="tab"
      aria-selected="{{ $isStudentId ? 'true' : 'false' }}"
    >
      <iconify-icon icon="lucide:id-card" aria-hidden="true"></iconify-icon>
      <span>Student ID</span>
    </button>
  </div>

  <form class="d-flex flex-column gap-3" method="POST" action="{{ route('login') }}" id="login-form" novalidate>
    @csrf
    <input type="hidden" name="login_method" id="login_method" value="{{ $loginMethod }}">

    {{-- Email field --}}
    <div class="auth-field {{ $isStudentId ? 'd-none' : '' }}" id="login-email-field">
      <label class="auth-field__label" for="email">Email</label>
      <div class="auth-field__control">
        <iconify-icon class="auth-field__icon" icon="lucide:mail" aria-hidden="true"></iconify-icon>
        <input
          type="{{ $isStudentId ? 'text' : 'email' }}"
          class="form-control @error('email') is-invalid @enderror"
          id="email"
          name="email"
          value="{{ old('email', request('email')) }}"
          placeholder="you@example.com"
          @unless($isStudentId) required @endunless
          @unless($isStudentId) autofocus @endunless
          autocomplete="username"
          @if($isStudentId) disabled @endif
        >
      </div>
      @error('email')
        <div class="invalid-feedback d-block">{{ $message }}</div>
      @enderror
    </div>

    {{-- Student ID field --}}
    <div class="auth-field {{ $isStudentId ? '' : 'd-none' }}" id="login-id-field">
      <label class="auth-field__label" for="login_id">Admission number</label>
      <div class="auth-field__control">
        <iconify-icon class="auth-field__icon" icon="lucide:id-card" aria-hidden="true"></iconify-icon>
        <input
          type="text"
          class="form-control @error('login_id') is-invalid @enderror"
          id="login_id"
          name="login_id"
          value="{{ old('login_id') }}"
          placeholder="Admission number"
          @if($isStudentId) required autofocus @endif
          autocomplete="username"
          inputmode="text"
          autocapitalize="characters"
          spellcheck="false"
          @unless($isStudentId) disabled @endunless
        >
      </div>
      @error('login_id')
        <div class="invalid-feedback d-block">{{ $message }}</div>
      @enderror
    </div>

    {{-- Password / PIN field --}}
    <div class="auth-field">
      <label class="auth-field__label" for="password" id="password-label">
        {{ $isStudentId ? 'PIN' : 'Password' }}
      </label>
      <div class="auth-field__control">
        <iconify-icon class="auth-field__icon" icon="lucide:lock" aria-hidden="true"></iconify-icon>
        <input
          type="password"
          class="form-control @error('password') is-invalid @enderror"
          id="password"
          name="password"
          placeholder="{{ $isStudentId ? '6-digit PIN' : 'Your password' }}"
          required
          autocomplete="current-password"
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

    {{-- Remember + forgot row --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div class="form-check mb-0" id="login-remember-field" @if($isStudentId) hidden @endif>
        <input
          type="checkbox"
          class="form-check-input"
          id="remember"
          name="remember"
          value="1"
          {{ old('remember') ? 'checked' : '' }}
          @if($isStudentId) disabled @endif
        >
        <label class="form-check-label small fw-medium" for="remember">Remember me</label>
      </div>

      @if (Route::has('password.request'))
        <a
          class="small fw-semibold text-primary text-decoration-none {{ $isStudentId ? 'd-none' : '' }}"
          href="{{ route('password.request') }}"
          id="login-forgot-password"
        >
          Forgot password?
        </a>
      @endif
    </div>

    <button type="submit" class="btn btn-primary w-100 justify-content-center mt-2">
      <span>Sign in</span>
      <iconify-icon icon="lucide:arrow-right" aria-hidden="true" class="ms-1"></iconify-icon>
    </button>
  </form>

  <p class="auth-shell__footer text-center mt-4 mb-0">
    New to {{ config('app.name') }}?
    <a href="{{ route('register') }}">Create an account</a>
  </p>
</x-auth-shell>

@push('head')
<style>
  /* Segmented pill tabs */
  .auth-tabs {
    position: relative;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
    padding: 4px;
    background: #f3f4f6;
    border-radius: 12px;
    margin-bottom: 1.5rem;
  }

  .auth-tabs__thumb {
    position: absolute;
    top: 4px;
    bottom: 4px;
    left: 4px;
    width: calc(50% - 4px);
    background: #fff;
    border-radius: 9px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 1;
  }

  .auth-tabs[data-active="student_id"] .auth-tabs__thumb {
    transform: translateX(100%);
  }

  .auth-tabs__btn {
    position: relative;
    z-index: 2;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    padding: 0.65rem 0.75rem;
    border: 0;
    background: transparent;
    font-weight: 600;
    color: #6b7280;
    font-size: 0.9rem;
    border-radius: 9px;
    cursor: pointer;
    transition: color 0.25s ease;
  }

  .auth-tabs__btn iconify-icon {
    font-size: 1.05rem;
    transition: transform 0.25s ease;
  }

  .auth-tabs__btn.is-active {
    color: var(--auth-primary);
  }

  .auth-tabs__btn.is-active iconify-icon {
    transform: scale(1.08);
  }

  .auth-tabs__btn:focus-visible {
    outline: 2px solid rgba(var(--bs-primary-rgb), 0.4);
    outline-offset: 2px;
  }

  /* Animate the currently visible identifier field */
  #login-email-field:not(.d-none),
  #login-id-field:not(.d-none) {
    animation: authFieldFade 0.28s ease both;
  }

  @keyframes authFieldFade {
    from { opacity: 0; transform: translateY(4px); }
    to   { opacity: 1; transform: translateY(0); }
  }
</style>
@endpush

<script>
  (function () {
    var tabs = document.querySelector('.auth-tabs');
    var methodInput = document.getElementById('login_method');
    var emailWrap = document.getElementById('login-email-field');
    var idWrap = document.getElementById('login-id-field');
    var emailInput = document.getElementById('email');
    var loginIdInput = document.getElementById('login_id');
    var passwordInput = document.getElementById('password');
    var passwordLabel = document.getElementById('password-label');
    var rememberWrap = document.getElementById('login-remember-field');
    var rememberInput = document.getElementById('remember');
    var forgotLink = document.getElementById('login-forgot-password');
    var emailToggle = document.getElementById('login-method-email');
    var studentToggle = document.getElementById('login-method-student-id');
    var passwordToggle = document.querySelector('[data-toggle-password]');

    function setMethod(method, options) {
      options = options || {};
      var isStudentId = method === 'student_id';
      methodInput.value = method;

      if (tabs) tabs.setAttribute('data-active', method);

      emailWrap.classList.toggle('d-none', isStudentId);
      idWrap.classList.toggle('d-none', !isStudentId);

      emailInput.disabled = isStudentId;
      emailInput.required = !isStudentId;
      emailInput.type = isStudentId ? 'text' : 'email';

      loginIdInput.disabled = !isStudentId;
      loginIdInput.required = isStudentId;

      passwordInput.placeholder = isStudentId ? '6-digit PIN' : 'Your password';
      if (passwordLabel) passwordLabel.textContent = isStudentId ? 'PIN' : 'Password';

      if (rememberWrap) rememberWrap.hidden = isStudentId;
      if (rememberInput) {
        rememberInput.disabled = isStudentId;
        if (isStudentId) rememberInput.checked = false;
      }
      if (forgotLink) forgotLink.classList.toggle('d-none', isStudentId);

      emailToggle.classList.toggle('is-active', !isStudentId);
      emailToggle.setAttribute('aria-selected', String(!isStudentId));
      studentToggle.classList.toggle('is-active', isStudentId);
      studentToggle.setAttribute('aria-selected', String(isStudentId));

      if (options.focus !== false) {
        (isStudentId ? loginIdInput : emailInput).focus();
      }
    }

    emailToggle.addEventListener('click', function (event) {
      event.preventDefault();
      setMethod('email');
    });
    studentToggle.addEventListener('click', function (event) {
      event.preventDefault();
      setMethod('student_id');
    });

    // Keyboard navigation between tabs
    [emailToggle, studentToggle].forEach(function (btn) {
      btn.addEventListener('keydown', function (event) {
        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
          event.preventDefault();
          setMethod(btn === emailToggle ? 'student_id' : 'email');
        }
      });
    });

    // Password reveal toggle
    if (passwordToggle) {
      passwordToggle.addEventListener('click', function () {
        var target = document.querySelector(passwordToggle.getAttribute('data-toggle-password'));
        if (!target) return;
        var shown = target.type === 'text';
        target.type = shown ? 'password' : 'text';
        passwordToggle.setAttribute('aria-pressed', String(!shown));
        passwordToggle.setAttribute('aria-label', shown ? 'Show password' : 'Hide password');
        var icon = passwordToggle.querySelector('iconify-icon');
        if (icon) icon.setAttribute('icon', shown ? 'lucide:eye' : 'lucide:eye-off');
      });
    }

    // Ensure initial state matches the current method without stealing focus on load
    setMethod(methodInput.value || 'email', { focus: false });
  })();
</script>
@endsection
