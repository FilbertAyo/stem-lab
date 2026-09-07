@props([
    'brandTitle' => 'Learn, build, discover.',
    'brandLead' => 'Adilisha STEM Labs brings clubs, labs and contests together in one friendly workspace for teachers and students.',
    'showBack' => true,
    'backFallback' => null,
])

@php
    $backFallback = $backFallback ?? url('/');
@endphp

@push('head')
<style>
  :root { --auth-primary: #0d78b8; --auth-primary-dark: #075585; }

  .auth-shell {
    position: relative;
    display: flex;
    min-height: 100vh;
    background: #fff;
  }

  /* ---------- Left brand panel ---------- */
  .auth-shell__brand {
    display: none;
    flex: 1 1 55%;
    position: relative;
    overflow: hidden;
    padding: 3rem 3.5rem;
    color: #fff;
    background:
      radial-gradient(circle at 22% 18%, rgba(255, 255, 255, 0.16) 0, transparent 34%),
      radial-gradient(circle at 82% 78%, rgba(255, 255, 255, 0.10) 0, transparent 32%),
      linear-gradient(135deg, var(--auth-primary) 0%, var(--auth-primary-dark) 100%);
  }

  .auth-shell__brand-inner {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 100%;
    gap: 3rem;
  }

  .auth-shell__logo {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    color: inherit;
  }

  .auth-shell__logo-img {
    height: 40px;
    width: auto;
  }

  .auth-shell__heading {
    font-size: clamp(1.85rem, 2.6vw, 2.5rem);
    font-weight: 700;
    line-height: 1.15;
    max-width: 22ch;
    margin: 0 0 0.85rem;
    color: #fff;
    animation: authRise 0.55s ease both;
  }

  .auth-shell__lead {
    font-size: 1rem;
    line-height: 1.55;
    color: rgba(255, 255, 255, 0.86);
    margin: 0;
    max-width: 42ch;
    animation: authRise 0.55s ease 0.05s both;
  }

  .auth-shell__brand,
  .auth-shell__brand-inner,
  .auth-shell__brand p,
  .auth-shell__brand h1 { color: #fff; }

  .auth-shell__badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 1.4rem;
    animation: authRise 0.55s ease 0.1s both;
  }

  .auth-shell__badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.4rem 0.8rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.14);
    backdrop-filter: blur(4px);
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.02em;
  }

  .auth-shell__badge iconify-icon {
    font-size: 0.9rem;
  }

  .auth-shell__meta {
    font-size: 0.8rem;
    opacity: 0.7;
    margin: 0;
  }

  /* Floating decorative shapes */
  .auth-shell__shape {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.08);
    filter: blur(2px);
    z-index: 1;
    pointer-events: none;
    animation: authFloat 9s ease-in-out infinite;
  }

  .auth-shell__shape--a { width: 240px; height: 240px; top: -70px; right: -60px; }
  .auth-shell__shape--b { width: 170px; height: 170px; bottom: 40px; left: -50px; animation-delay: -3s; }
  .auth-shell__shape--c { width: 90px;  height: 90px;  bottom: 34%; right: 22%; animation-delay: -6s; }

  @keyframes authFloat {
    0%, 100% { transform: translateY(0) scale(1); }
    50%      { transform: translateY(-16px) scale(1.05); }
  }

  /* ---------- Right form panel ---------- */
  .auth-shell__form {
    flex: 1 1 45%;
    display: flex;
    flex-direction: column;
    padding: 1.25rem 1.25rem 2rem;
    background: #fff;
  }

  .auth-shell__top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    min-height: 44px;
  }

  .auth-shell__back {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #fff;
    border: 1px solid #e5e7eb;
    color: #212529;
    text-decoration: none;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.15s ease;
    cursor: pointer;
    padding: 0;
    font-size: 1.05rem;
  }

  .auth-shell__back:hover,
  .auth-shell__back:focus-visible {
    border-color: #212529;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    outline: none;
    transform: translateX(-1px);
  }

  .auth-shell__mobile-logo { display: inline-flex; text-decoration: none; }
  .auth-shell__mobile-logo-img { height: 32px; width: auto; }

  .auth-shell__body {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem 0 2rem;
  }

  .auth-shell__card {
    width: 100%;
    max-width: 440px;
    animation: authRise 0.4s ease both;
  }

  .auth-shell__eyebrow {
    display: inline-block;
    text-transform: uppercase;
    letter-spacing: 0.09em;
    font-size: 0.7rem;
    font-weight: 700;
    color: var(--auth-primary);
    margin-bottom: 0.55rem;
  }

  .auth-shell__title {
    font-size: 1.65rem;
    font-weight: 700;
    color: #111827;
    line-height: 1.2;
    margin: 0 0 0.4rem;
  }

  .auth-shell__subtitle {
    color: #6b7280;
    margin: 0 0 1.6rem;
    font-size: 0.95rem;
    line-height: 1.5;
  }

  .auth-shell__footer {
    margin-top: 1.5rem;
    font-size: 0.9rem;
    color: #6b7280;
  }

  .auth-shell__footer a {
    color: var(--auth-primary);
    font-weight: 600;
    text-decoration: none;
  }

  .auth-shell__footer a:hover { text-decoration: underline; }

  @keyframes authRise {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  @media (min-width: 992px) {
    .auth-shell__brand { display: block; }
    .auth-shell__form { padding: 2rem 3rem; }
    .auth-shell__mobile-logo { display: none; }
  }

  /* ---------- Shared form control polish ---------- */
  .auth-shell .form-control {
    border-radius: 10px;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    border: 1.5px solid #e5e7eb;
    background: #fff;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
  }

  .auth-shell .form-control:focus {
    border-color: var(--auth-primary);
    box-shadow: 0 0 0 4px rgba(var(--bs-primary-rgb), 0.12);
    background: #fff;
  }

  .auth-shell .form-control.is-invalid:focus {
    box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.15);
  }

  .auth-shell .btn-primary {
    border-radius: 10px;
    padding: 0.75rem 1.25rem;
    font-weight: 600;
    letter-spacing: 0.01em;
    box-shadow: 0 4px 14px rgba(var(--bs-primary-rgb), 0.20);
    transition: transform 0.15s ease, box-shadow 0.2s ease, background-color 0.2s ease;
  }

  .auth-shell .btn-primary:hover,
  .auth-shell .btn-primary:focus-visible {
    transform: translateY(-1px);
    box-shadow: 0 10px 22px rgba(var(--bs-primary-rgb), 0.28);
  }

  .auth-shell .invalid-feedback {
    font-size: 0.8125rem;
    margin-top: 0.35rem;
  }

  .auth-shell .form-check-input:focus {
    border-color: var(--auth-primary);
    box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), 0.15);
  }

  .auth-shell .form-check-input:checked {
    background-color: var(--auth-primary);
    border-color: var(--auth-primary);
  }

  /* ---------- Auth form field (label + input + icon + toggle) ---------- */
  .auth-field { display: flex; flex-direction: column; gap: 0.35rem; }

  .auth-field__label {
    font-size: 0.8125rem;
    font-weight: 600;
    color: #374151;
    margin: 0;
  }

  .auth-field__control {
    position: relative;
  }

  .auth-field__control .form-control {
    padding-left: 2.6rem;
    padding-right: 1rem;
  }

  .auth-field__control .form-control[type="password"],
  .auth-field__control:has(.auth-field__toggle) .form-control {
    padding-right: 2.75rem;
  }

  .auth-field__icon {
    position: absolute;
    left: 0.9rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 1.05rem;
    color: #9ca3af;
    pointer-events: none;
    line-height: 1;
    z-index: 2;
    transition: color 0.2s ease;
  }

  .auth-field__control:focus-within .auth-field__icon {
    color: var(--auth-primary);
  }

  .auth-field__toggle {
    position: absolute;
    right: 0.5rem;
    top: 50%;
    transform: translateY(-50%);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border: 0;
    background: transparent;
    color: #9ca3af;
    border-radius: 8px;
    cursor: pointer;
    z-index: 2;
    transition: color 0.2s ease, background 0.2s ease;
  }

  .auth-field__toggle:hover,
  .auth-field__toggle:focus-visible {
    color: var(--auth-primary);
    background: rgba(var(--bs-primary-rgb), 0.08);
    outline: none;
  }
</style>
@endpush

<div class="page-wrapper overflow-hidden">
  <div class="auth-shell">
    {{-- Left brand panel (desktop / tablet) --}}
    <aside class="auth-shell__brand" aria-hidden="true">
      <span class="auth-shell__shape auth-shell__shape--a"></span>
      <span class="auth-shell__shape auth-shell__shape--b"></span>
      <span class="auth-shell__shape auth-shell__shape--c"></span>

      <div class="auth-shell__brand-inner">
        <a href="{{ url('/') }}" class="auth-shell__logo">
          <img
            src="{{ asset('images/logo/logo-white.png') }}"
            alt="{{ config('app.name') }}"
            class="auth-shell__logo-img"
          >
        </a>

        <div>
          <h1 class="auth-shell__heading">{{ $brandTitle }}</h1>
          <p class="auth-shell__lead">{{ $brandLead }}</p>

          <div class="auth-shell__badges">
            <span class="auth-shell__badge">
              <iconify-icon icon="lucide:beaker"></iconify-icon>
              Labs
            </span>
            <span class="auth-shell__badge">
              <iconify-icon icon="lucide:users"></iconify-icon>
              Clubs
            </span>
            <span class="auth-shell__badge">
              <iconify-icon icon="lucide:trophy"></iconify-icon>
              Contests
            </span>
          </div>
        </div>

        <p class="auth-shell__meta">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
      </div>
    </aside>

    {{-- Right form panel --}}
    <div class="auth-shell__form">
      <div class="auth-shell__top">
        @if ($showBack)
          <button
            type="button"
            class="auth-shell__back"
            data-auth-back
            data-fallback="{{ $backFallback }}"
            aria-label="Go back"
          >
            <iconify-icon icon="lucide:arrow-left"></iconify-icon>
          </button>
        @else
          <span></span>
        @endif

        <a href="{{ url('/') }}" class="auth-shell__mobile-logo" aria-label="{{ config('app.name') }} home">
          <img
            src="{{ asset('images/logo/logo-dark.png') }}"
            alt="{{ config('app.name') }}"
            class="auth-shell__mobile-logo-img"
          >
        </a>
      </div>

      <div class="auth-shell__body">
        <div class="auth-shell__card">
          {{ $slot }}
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  (function () {
    document.addEventListener('DOMContentLoaded', function () {
      var backBtn = document.querySelector('[data-auth-back]');
      if (!backBtn) return;

      backBtn.addEventListener('click', function () {
        if (typeof window.handleAuthBack === 'function' && window.handleAuthBack()) return;
        var fallback = backBtn.dataset.fallback || '/';
        if (window.history.length > 1) { history.back(); return; }
        window.location.href = fallback;
      });
    });
  })();
</script>
@endpush
