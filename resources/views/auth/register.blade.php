@extends('layouts.client.guest')

@section('title', 'Sign Up | ' . config('app.name'))
@section('page', 'signup')

@section('content')
@php
  $hasErrors = $errors->any();
  $selectedRole = old('role');
  $startOnDetails = $hasErrors || $selectedRole;
@endphp

@include('partials.auth-top-bar')

<div class="page-wrapper overflow-hidden">
  <section class="bg-light-gray d-flex align-items-center justify-content-center min-vh-100">
    <div class="container py-3">
      <div class="sign-in card mx-auto shadow-lg">
        <div class="card-body py-8 px-lg-5">
          {{-- Step 1: Role selection --}}
          <div id="register-step-role" class="{{ $startOnDetails ? 'd-none' : '' }}">
            <h4 class="text-center fw-semibold mb-3">Who are you?</h4>
          
            <div class="d-flex flex-column gap-3" role="radiogroup" aria-label="Who are you?">

            <button
                type="button"
                class="role-card"
                data-role="teacher"
                aria-pressed="false"
              >
                <span class="role-card__icon" aria-hidden="true">
                  <iconify-icon icon="lucide:book-open"></iconify-icon>
                </span>
                <span class="role-card__content">
                  <span class="role-card__title">Teacher</span>
                  <span class="role-card__desc">Manage clubs and guide students</span>
                </span>
                <iconify-icon icon="lucide:chevron-right" class="role-card__arrow" aria-hidden="true"></iconify-icon>
              </button>

              <button
                type="button"
                class="role-card"
                data-role="student"
                aria-pressed="false"
              >
                <span class="role-card__icon" aria-hidden="true">
                  <iconify-icon icon="lucide:graduation-cap"></iconify-icon>
                </span>
                <span class="role-card__content">
                  <span class="role-card__title">Student</span>
                  <span class="role-card__desc">Join clubs, compete, and learn</span>
                </span>
                <iconify-icon icon="lucide:chevron-right" class="role-card__arrow" aria-hidden="true"></iconify-icon>
              </button>

              <button
                type="button"
                class="role-card"
                data-role="parent"
                aria-pressed="false"
              >
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
          </div>

          {{-- Step 2: Account details --}}
          <div id="register-step-details" class="{{ $startOnDetails ? '' : 'd-none' }}">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-4">
              <div>
                <h4 class="fw-semibold mb-0">Create your account</h4>
                <p class="text-muted small mb-0">Signing up as <span id="selected-role-label" class="fw-medium text-dark">{{ ucfirst($selectedRole ?? '') }}</span></p>
              </div>
              <span id="selected-role-badge" class="role-badge">{{ ucfirst($selectedRole ?? '') }}</span>
            </div>

            <form class="d-flex flex-column gap-3" method="POST" action="{{ route('register') }}" id="register-form">
              @csrf
              <input type="hidden" name="role" id="role" value="{{ $selectedRole }}">

              <div>
                <input
                  type="text"
                  id="name"
                  name="name"
                  class="form-control border px-2 @error('name') is-invalid @enderror"
                  placeholder="Name"
                  value="{{ old('name') }}"
                  required
                  autocomplete="name"
                >
                @error('name')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div>
                <input
                  type="email"
                  class="form-control border px-2 @error('email') is-invalid @enderror"
                  id="email"
                  name="email"
                  placeholder="Email"
                  value="{{ old('email') }}"
                  required
                  autocomplete="email"
                >
                @error('email')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div>
                <input
                  type="tel"
                  id="phone"
                  name="phone"
                  class="form-control border px-2 @error('phone') is-invalid @enderror"
                  placeholder="Phone Number"
                  value="{{ old('phone') }}"
                  required
                  autocomplete="tel"
                >
                @error('phone')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div>
                <input
                  type="password"
                  class="form-control border px-2 @error('password') is-invalid @enderror"
                  id="password"
                  name="password"
                  placeholder="Password"
                  required
                  autocomplete="new-password"
                >
                @error('password')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div>
                <input
                  type="password"
                  class="form-control border px-2 @error('password_confirmation') is-invalid @enderror"
                  id="password_confirmation"
                  name="password_confirmation"
                  placeholder="Confirm Password"
                  required
                  autocomplete="new-password"
                >
                @error('password_confirmation')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <button type="submit" class="btn btn-primary w-100 justify-content-center fw-medium my-3">
                Sign Up
              </button>

              <div
                id="register-loading-indicator"
                class="mt-2"
                style="display: none; align-items: center; justify-content: center; gap: 0.75rem;"
                role="status"
                aria-live="polite"
              >
                <span class="register-spinner" aria-hidden="true"></span>
                <span class="text-primary small">Creating your account...</span>
              </div>
            </form>

            <p class="text-center mb-3 d-block fw-medium mt-4">
              By creating an account, you agree with our
              <a class="text-primary" href="{{ url('/privacy-policy') }}">Privacy Policy</a>
              and
              <a class="text-primary" href="{{ url('/terms-conditions') }}">Terms &amp; Conditions</a>.
            </p>

            <p class="mb-0 fw-medium text-center">
              Already have an account?
              <a class="text-primary" href="{{ route('login') }}">Sign In</a>
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
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
    transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.15s ease;
    cursor: pointer;
  }

  .role-card:hover,
  .role-card:focus-visible {
    border-color: #212529;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
    transform: translateY(-1px);
    outline: none;
  }

  .role-card__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: #f3f4f6;
    color: #212529;
    font-size: 1.35rem;
    flex-shrink: 0;
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
    font-size: 0.8125rem;
    color: #6b7280;
    line-height: 1.35;
    margin-top: 0.125rem;
  }

  .role-card__arrow {
    font-size: 1.125rem;
    color: #9ca3af;
    flex-shrink: 0;
  }

  .role-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.75rem;
    border-radius: 999px;
    background: #f3f4f6;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: capitalize;
    color: #212529;
    white-space: nowrap;
  }

  .register-spinner {
    width: 1.25rem;
    height: 1.25rem;
    border: 2px solid #e5e7eb;
    border-top-color: #212529;
    border-radius: 50%;
    animation: register-spin 0.7s linear infinite;
  }

  @keyframes register-spin {
    to { transform: rotate(360deg); }
  }

  #register-step-details,
  #register-step-role {
    animation: register-fade-in 0.25s ease;
  }

  @keyframes register-fade-in {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
  }
</style>
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
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
        if (nameField) {
          nameField.focus();
        }
      } else {
        stepDetails.classList.add('d-none');
        stepRole.classList.remove('d-none');
      }
    }

    function selectRole(role) {
      roleInput.value = role;
      const label = roleLabels[role] || role;
      roleLabel.textContent = label;
      roleBadge.textContent = label;
      showStep('details');
    }

    roleCards.forEach(function(card) {
      card.addEventListener('click', function() {
        selectRole(card.dataset.role);
      });
    });

    window.handleAuthBack = function() {
      if (!stepDetails.classList.contains('d-none')) {
        showStep('role');
        return true;
      }
      return false;
    };

    if (form && loading) {
      form.addEventListener('submit', function() {
        loading.style.display = 'flex';
      });
    }
  });
</script>
@endpush
