@extends('layouts.client.guest')

@section('title', 'Complete Your Profile | ' . config('app.name'))
@section('page', 'student-onboarding')

@section('content')
@include('partials.auth-top-bar')

<div class="page-wrapper overflow-hidden">
  <section class="bg-light-gray d-flex align-items-center justify-content-center min-vh-100">
    <div class="container py-4">
      <div class="onboarding-card card mx-auto shadow-lg border-0">
        <div class="card-body p-4 p-lg-5">
          <div class="text-center mb-4">
            <p class="text-uppercase text-muted small fw-semibold mb-2 tracking-wide">Welcome, {{ $user->name }}</p>
            <h4 class="fw-semibold mb-1">Let&apos;s personalize your experience</h4>
        </div>

          <div class="onboarding-progress mb-4" aria-hidden="true">
            @foreach([1 => 'Gender', 2 => 'Birthday', 3 => 'Class'] as $num => $label)
              <div class="onboarding-progress__item {{ $step >= $num ? 'is-active' : '' }} {{ $step > $num ? 'is-done' : '' }}">
                <span class="onboarding-progress__dot">{{ $step > $num ? '✓' : $num }}</span>
                <span class="onboarding-progress__label">{{ $label }}</span>
              </div>
              @if($num < 3)
                <div class="onboarding-progress__line {{ $step > $num ? 'is-done' : '' }}"></div>
              @endif
            @endforeach
          </div>

          @if($step === 1)
            <div class="onboarding-step">
              <h5 class="fw-semibold text-center mb-1">How do you identify?</h5>
              <p class="text-muted small text-center mb-4">Select one option to continue.</p>

              <form method="POST" action="{{ route('student.onboarding.gender') }}" id="onboarding-gender-form">
                @csrf
                <input type="hidden" name="gender" id="gender-input" value="{{ old('gender', $user->gender) }}">

                <div class="row g-3 mb-4">
                  <div class="col-6">
                    <button
                      type="button"
                      class="onboarding-choice w-100 {{ old('gender', $user->gender) === 'male' ? 'is-selected' : '' }}"
                      data-gender="male"
                    >
                      <span class="onboarding-choice__icon">
                        <iconify-icon icon="lucide:user"></iconify-icon>
                      </span>
                      <span class="onboarding-choice__title">Male</span>
                    </button>
                  </div>
                  <div class="col-6">
                    <button
                      type="button"
                      class="onboarding-choice w-100 {{ old('gender', $user->gender) === 'female' ? 'is-selected' : '' }}"
                      data-gender="female"
                    >
                      <span class="onboarding-choice__icon onboarding-choice__icon--accent">
                        <iconify-icon icon="lucide:user-round"></iconify-icon>
                      </span>
                      <span class="onboarding-choice__title">Female</span>
                    </button>
                  </div>
                </div>

                @error('gender')
                  <div class="text-danger small text-center mb-3">{{ $message }}</div>
                @enderror

                <button type="submit" class="btn btn-primary w-100 py-2 fw-medium" id="gender-continue" disabled>
                  Continue
                </button>
              </form>
            </div>
          @endif

          @if($step === 2)
            <div class="onboarding-step">
              
              <form method="POST" action="{{ route('student.onboarding.date-of-birth') }}" class="d-flex flex-column gap-3">
                @csrf

                <div class="onboarding-date-wrap">
                  <label for="date_of_birth" class="onboarding-date-label">
                    <iconify-icon icon="lucide:calendar" class="me-2"></iconify-icon>
                    Date of birth
                  </label>
                  <input
                    type="date"
                    id="date_of_birth"
                    name="date_of_birth"
                    class="form-control onboarding-date-input @error('date_of_birth') is-invalid @enderror"
                    value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}"
                    max="{{ now()->toDateString() }}"
                    required
                    autofocus
                  >
                  @error('date_of_birth')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-medium mt-2">
                  Continue
                </button>
              </form>
            </div>
          @endif

          @if($step === 3)
            <div class="onboarding-step">
              <h5 class="fw-semibold text-center mb-1">What class are you in?</h5>
              <p class="text-muted small text-center mb-4">Pick your class, or skip if you&apos;re not sure yet.</p>

              <form method="POST" action="{{ route('student.onboarding.class') }}" id="onboarding-class-form">
                @csrf
                <input type="hidden" name="school_class_id" id="school-class-input" value="{{ old('school_class_id', $user->school_class_id) }}">

                <div class="onboarding-class-grid mb-4">
                  @forelse($schoolClasses as $class)
                    <button
                      type="button"
                      class="onboarding-class-chip {{ (string) old('school_class_id', $user->school_class_id) === (string) $class->id ? 'is-selected' : '' }}"
                      data-class-id="{{ $class->id }}"
                    >
                      {{ $class->name }}
                    </button>
                  @empty
                    <p class="text-muted small text-center mb-0">No classes available right now. You can skip this step.</p>
                  @endforelse
                </div>

                @error('school_class_id')
                  <div class="text-danger small text-center mb-3">{{ $message }}</div>
                @enderror

                <button type="submit" class="btn btn-primary w-100 py-2 fw-medium mb-3" id="class-continue" {{ $schoolClasses->isEmpty() ? '' : 'disabled' }}>
                  Finish setup
                </button>
              </form>

              <form method="POST" action="{{ route('student.onboarding.skip-class') }}" class="text-center">
                @csrf
                <button type="submit" class="badge badge-link btn-link bg-secondary text-white text-decoration-none small fw-medium">
                  <span class="badge badge-link">
                    <span class="badge-link-text">Skip for now</span>
                  </span>
                </button>
              </form>
            </div>
          @endif
        </div>
      </div>
    </div>
  </section>
</div>
@endsection

@push('head')
<style>
  .onboarding-card {
    max-width: 520px;
    border-radius: 20px;
  }

  .tracking-wide {
    letter-spacing: 0.08em;
  }

  .onboarding-progress {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0;
  }

  .onboarding-progress__item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.35rem;
    min-width: 72px;
  }

  .onboarding-progress__dot {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    font-size: 0.875rem;
    font-weight: 600;
    background: #f3f4f6;
    color: #9ca3af;
    border: 2px solid #e5e7eb;
    transition: all 0.2s ease;
  }

  .onboarding-progress__item.is-active .onboarding-progress__dot {
    background: #212529;
    color: #fff;
    border-color: #212529;
    box-shadow: 0 4px 14px rgba(33, 37, 41, 0.2);
  }

  .onboarding-progress__item.is-done .onboarding-progress__dot {
    background: #198754;
    color: #fff;
    border-color: #198754;
  }

  .onboarding-progress__label {
    font-size: 0.7rem;
    font-weight: 600;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  .onboarding-progress__item.is-active .onboarding-progress__label,
  .onboarding-progress__item.is-done .onboarding-progress__label {
    color: #212529;
  }

  .onboarding-progress__line {
    flex: 1;
    max-width: 48px;
    height: 2px;
    background: #e5e7eb;
    margin-bottom: 1.25rem;
    transition: background 0.2s ease;
  }

  .onboarding-progress__line.is-done {
    background: #198754;
  }

  .onboarding-choice {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    padding: 1.5rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 16px;
    background: #fff;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .onboarding-choice:hover,
  .onboarding-choice:focus-visible {
    border-color: #212529;
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    outline: none;
  }

  .onboarding-choice.is-selected {
    border-color: #212529;
    background: #f8f9fa;
    box-shadow: 0 8px 24px rgba(33, 37, 41, 0.12);
  }

  .onboarding-choice__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: #f3f4f6;
    font-size: 1.5rem;
    color: #212529;
  }

  .onboarding-choice__icon--accent {
    background: #fce7f3;
    color: #be185d;
  }

  .onboarding-choice.is-selected .onboarding-choice__icon {
    background: #212529;
    color: #fff;
  }

  .onboarding-choice.is-selected .onboarding-choice__icon--accent {
    background: #be185d;
    color: #fff;
  }

  .onboarding-choice__title {
    font-weight: 600;
    font-size: 1rem;
    color: #111827;
  }

  .onboarding-date-wrap {
    background: #f8f9fa;
    border: 2px solid #e5e7eb;
    border-radius: 16px;
    padding: 1.25rem;
    transition: border-color 0.2s ease;
  }

  .onboarding-date-wrap:focus-within {
    border-color: #212529;
  }

  .onboarding-date-label {
    display: flex;
    align-items: center;
    font-size: 0.8125rem;
    font-weight: 600;
    color: #6b7280;
    margin-bottom: 0.75rem;
  }

  .onboarding-date-input {
    border: none;
    background: #fff;
    border-radius: 12px;
    padding: 0.875rem 1rem;
    font-size: 1.0625rem;
    font-weight: 500;
  }

  .onboarding-date-input:focus {
    box-shadow: 0 0 0 3px rgba(33, 37, 41, 0.1);
  }

  .onboarding-class-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 0.625rem;
    justify-content: center;
    max-height: 240px;
    overflow-y: auto;
    padding: 0.25rem;
  }

  .onboarding-class-chip {
    padding: 0.625rem 1.125rem;
    border: 2px solid #e5e7eb;
    border-radius: 999px;
    background: #fff;
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    cursor: pointer;
    transition: all 0.15s ease;
  }

  .onboarding-class-chip:hover,
  .onboarding-class-chip:focus-visible {
    border-color: #212529;
    outline: none;
    transform: translateY(-1px);
  }

  .onboarding-class-chip.is-selected {
    background: #212529;
    border-color: #212529;
    color: #fff;
    box-shadow: 0 4px 12px rgba(33, 37, 41, 0.2);
  }

  .onboarding-step {
    animation: onboarding-fade-in 0.3s ease;
  }

  @keyframes onboarding-fade-in {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
  }
</style>
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const genderInput = document.getElementById('gender-input');
    const genderContinue = document.getElementById('gender-continue');
    const genderChoices = document.querySelectorAll('[data-gender]');

    genderChoices.forEach(function(choice) {
      choice.addEventListener('click', function() {
        genderChoices.forEach(function(c) { c.classList.remove('is-selected'); });
        choice.classList.add('is-selected');
        if (genderInput) {
          genderInput.value = choice.dataset.gender;
        }
        if (genderContinue) {
          genderContinue.disabled = false;
        }
      });
    });

    if (genderInput && genderInput.value && genderContinue) {
      genderContinue.disabled = false;
    }

    const classInput = document.getElementById('school-class-input');
    const classContinue = document.getElementById('class-continue');
    const classChips = document.querySelectorAll('[data-class-id]');

    classChips.forEach(function(chip) {
      chip.addEventListener('click', function() {
        classChips.forEach(function(c) { c.classList.remove('is-selected'); });
        chip.classList.add('is-selected');
        if (classInput) {
          classInput.value = chip.dataset.classId;
        }
        if (classContinue) {
          classContinue.disabled = false;
        }
      });
    });

    if (classInput && classInput.value && classContinue) {
      classContinue.disabled = false;
    }
  });
</script>
@endpush
