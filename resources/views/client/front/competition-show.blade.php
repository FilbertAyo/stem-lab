@extends('layouts.landing')

@section('title')
  @if($showTeacherSignupForm)
    Teacher sign-up — {{ $competition->name }} | {{ config('app.name') }}
  @else
    {{ $competition->name }} | {{ config('app.name') }}
  @endif
@endsection
@section('page', 'competitions')

@push('head')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush

@section('content')
<main>
@guest
  @php
    $competitionPagePath = route('competitions.public.show', $competition, absolute: false);
    $loginBackQuery = ['redirect' => $competitionPagePath];
    if (old('email')) {
      $loginBackQuery['email'] = old('email');
    }
    $loginWithReturn = route('login', $loginBackQuery, absolute: false);
  @endphp
@endguest

@if($competition->cover_image)
  <section class="banner-section banner-inner-section position-relative overflow-hidden d-flex align-items-end"
    style="background-image: url('{{ Storage::url($competition->cover_image) }}');">
    <div class="container">
      <div class="d-flex flex-column gap-4 pb-5 pb-xl-10 position-relative z-1">
        <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-lg-between gap-4">
          <h2 class="mb-0 fs-15 text-white lh-1">{{ $competition->name }}</h2>
        </div>
        @if($competition->club)
          <p class="mb-0 text-white text-opacity-90 fs-5">Hosted by {{ $competition->club->name }}</p>
        @endif
      </div>
    </div>
  </section>
@else
  <section class="banner-section banner-inner-section position-relative overflow-hidden d-flex align-items-end"
    style="background-image: url('/client/assets/images/backgrounds/competition.jpg');">
    <div class="container">
      <div class="d-flex flex-column gap-4 pb-5 pb-xl-10 position-relative z-1">
        <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-lg-between gap-4">
          <h2 class="mb-0 fs-15 text-white lh-1">{{ $competition->name }}</h2>
        </div>
        @if($competition->club)
          <p class="mb-0 text-white text-opacity-90 fs-5">Hosted by {{ $competition->club->name }}</p>
        @endif
      </div>
    </div>
  </section>
@endif

@if($showTeacherSignupForm)
  <section class="bg-light py-3 py-lg-3 py-xl-3">
    <div class="container py-3">
      <div class="sign-in card mx-auto shadow-lg competition-teacher-signup-card" style="max-width: 1080px;">
        <div class="card-body py-8 px-lg-5 px-xl-8">
          <h3 class="text-center">Teacher sign-up</h3>
          <p class="text-center text-muted small mb-6 mb-lg-8">Link your school and join this competition’s club.</p>

          @include('elements.client-validation')

          @if($errors->any())
            <div class="alert alert-danger small mb-4">
              <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form method="POST" action="{{ route('competitions.public.join-as-teacher', $competition) }}" id="competitionPublicJoinForm" class="d-flex flex-column gap-4">
            @csrf

            <div class="row g-3">
              <div class="col-6">
                <label class="form-label small mb-1" for="name">Full name</label>
                <input type="text" name="name" id="name" class="form-control border p-2 @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Full name" required autocomplete="name">
                @error('name')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-6">
                <label class="form-label small mb-1" for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control border p-2 @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Email" required autocomplete="email">
                @error('email')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                  @if(\Illuminate\Support\Str::contains(strtolower($message), ['already', 'log in', 'exists']))
                    <div class="small mt-2">
                      <a href="{{ $loginWithReturn }}" class="fw-medium text-dark">Sign in with this email</a> instead. After you sign in you will be sent back to this site to continue from your competition hub.
                    </div>
                  @endif
                @enderror
              </div>
              <div class="col-6">
                <label class="form-label small mb-1" for="phone">Phone number</label>
                <input type="tel" name="phone" id="phone" class="form-control border p-2 @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="+255765890921 or 0765890921" required autocomplete="tel">
                @error('phone')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-6">
                <label class="form-label small mb-1" for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control border p-2 @error('password') is-invalid @enderror" placeholder="Password" required autocomplete="new-password">
                @error('password')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-6">
                <label class="form-label small mb-1" for="password_confirmation">Confirm password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control border p-2" placeholder="Confirm password" required autocomplete="new-password">
              </div>
            </div>

            <div class="alert alert-light border small mb-0 text-start">
              <i class="bi bi-info-circle me-1"></i>
              If you are the first teacher from your school in this club, you may be approved automatically; otherwise a school leader may need to approve your request.
            </div>

            @include('partials.teacher-club-join-school-fields', [
              'schools' => $schools,
              'regions' => $regions,
              'existingSchool' => null,
            ])

            <button type="submit" class="btn btn-primary w-100 justify-content-center py-2 fw-medium my-3 fs-4 lh-lg">
              Create account &amp; join club
            </button>

            <h5 class="text-center text-muted mb-0">Already have an account? <a href="{{ $loginWithReturn }}" class="text-primary">Sign in</a>.</h5>
          </form>

          <a href="{{ $competitionPagePath }}" class="text-center mt-2 d-block text-dark fw-medium small text-decoration-none">← Back to competition details</a>
         
        </div>
      </div>
    </div>
  </section>
@endif

@unless($showTeacherSignupForm)
  <section class="project-detail py-5 py-lg-11 py-xl-12">
    <div class="container">
      <div class="d-flex flex-column gap-6">
      

    @include('elements.client-validation')

        <div class="mb-3">
          @if($allowPublicTeacherSignup)
            <span class="badge rounded-pill bg-success text-white">Registration open</span>
          @else
            <span class="badge rounded-pill bg-danger text-white">Registration closed</span>
        @endif
        </div>

        <div class="row g-4 fs-5 text-dark mb-3">
          @if($competition->registration_start)
            <div class="col-6 col-lg-3">
              <p class="mb-1 text-opacity-70 small text-uppercase">Registration opens</p>
              <p class="mb-0 fw-medium">{{ $competition->registration_start->format('j M Y, H:i') }}</p>
            </div>
          @endif
          @if($competition->registration_deadline)
            <div class="col-6 col-lg-3">
              <p class="mb-1 text-opacity-70 small text-uppercase">Registration closes</p>
              <p class="mb-0 fw-medium">{{ $competition->registration_deadline->format('j M Y, H:i') }}</p>
            </div>
          @endif
          @if($competition->competition_start)
            <div class="col-6 col-lg-3">
              <p class="mb-1 text-opacity-70 small text-uppercase">Competition starts</p>
              <p class="mb-0 fw-medium">{{ $competition->competition_start->format('j M Y, H:i') }}</p>
            </div>
          @endif
          @if($competition->competition_end)
            <div class="col-6 col-lg-3">
              <p class="mb-1 text-opacity-70 small text-uppercase">Competition ends</p>
              <p class="mb-0 fw-medium">{{ $competition->competition_end->format('j M Y, H:i') }}</p>
            </div>
          @endif
        </div>

        <div class="row gap-4 gap-lg-0">
          <div class="col-lg-4">
            <h2 class="fs-13 mb-0">About</h2>
          </div>
          <div class="col-lg-8">
            @php
              $aboutDescription = (string) ($competition->description ?? '');
              $aboutDescriptionHasContent = $aboutDescription !== '' && \Illuminate\Support\Str::of($aboutDescription)->stripTags()->trim()->isNotEmpty();
              $aboutDescriptionIsHtml = str_contains($aboutDescription, '<');
              $aboutInstructions = (string) ($competition->instructions ?? '');
              $aboutInstructionsHasContent = $aboutInstructions !== '' && \Illuminate\Support\Str::of($aboutInstructions)->stripTags()->trim()->isNotEmpty();
              $aboutInstructionsIsHtml = str_contains($aboutInstructions, '<');
              $aboutTerms = (string) ($competition->terms_and_conditions ?? '');
              $aboutTermsHasContent = $aboutTerms !== '' && \Illuminate\Support\Str::of($aboutTerms)->stripTags()->trim()->isNotEmpty();
              $aboutTermsIsHtml = str_contains($aboutTerms, '<');
            @endphp
            @if($aboutDescriptionHasContent)
              @if($aboutDescriptionIsHtml)
                <div class="fs-5 mb-3 competition-rich-text">{!! $aboutDescription !!}</div>
              @else
                <p class="fs-5 mb-3 competition-rich-text-plain" style="white-space: pre-line;">{{ $aboutDescription }}</p>
              @endif
            @else
              <p class="fs-5 mb-3">No description provided for this competition.</p>
            @endif
            @if($aboutInstructionsHasContent)
              <h4>How to participate</h4>
              @if($aboutInstructionsIsHtml)
                <div class="fs-5 mb-3 competition-rich-text">{!! $aboutInstructions !!}</div>
              @else
                <div class="fs-5 mb-3 competition-rich-text-plain" style="white-space: pre-line;">{{ $aboutInstructions }}</div>
              @endif
            @endif
            @if($aboutTermsHasContent)
              <h4>Terms and conditions</h4>
              @if($aboutTermsIsHtml)
                <div class="fs-5 mb-0 competition-rich-text">{!! $aboutTerms !!}</div>
              @else
                <div class="fs-5 mb-0 competition-rich-text-plain" style="white-space: pre-line;">{{ $aboutTerms }}</div>
              @endif
            @endif
          </div>
        </div>

        @if($competition->phases->isNotEmpty())
          <div class="row gap-4 gap-lg-0">
            <div class="col-lg-4">
              <h2 class="fs-13 mb-0">Schedule</h2>
            </div>
            <div class="col-lg-8">
              <div class="d-flex flex-column gap-3">
                @foreach($competition->phases as $phase)
                  <div class="py-3 border-bottom">
                    <span class="fs-5 fw-medium">{{ $phase->sequence }}. {{ $phase->name }}</span>
                    @if($phase->description)
                      <div class="text-opacity-70 small mt-1">{{ $phase->description }}</div>
                    @endif
                    @if($phase->opens_at || $phase->closes_at)
                      <p class="mb-0 small text-opacity-70 mt-2">
                        @if($phase->opens_at)
                          <span>From {{ $phase->opens_at->format('j M Y, H:i') }}</span>
                        @endif
                        @if($phase->closes_at)
                          <span class="ms-1">to {{ $phase->closes_at->format('j M Y, H:i') }}</span>
                        @endif
                      </p>
                    @endif
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        @endif

        @auth
          @if(auth()->user()->role === 'teacher')
            <div class="border rounded-3 p-4 bg-light">
              <h3 class="fs-5 mb-3">Teacher access</h3>
              <p class="text-opacity-70 mb-3 mb-0">
                @if($teacherClubForCompetition && $competitionRegistration)
                  You are set up for this competition. Open your competition hub to continue registration, ideas, or track approval.
                @elseif($teacherClubForCompetition)
                  Your club membership for this competition is recorded. Continue in the hub to complete competition registration when the phase is open.
                @else
                  Continue to your competition hub to join this competition’s club or finish setup.
                @endif
              </p>
              <a href="{{ route('client.competitions.index') }}" class="btn btn-primary mt-3">
                Continue to competition hub
              </a>
            </div>
          @endif
        @else
          @if($allowPublicTeacherSignup)
            <div class="border rounded-3 p-4 bg-light" id="teacher-signup-section">
              @if($showTeacherSignupHelp)
                <h3 class="fs-5 mb-2">Need help?</h3>
                <p class="text-opacity-70 small mb-3">
                  If you are a teacher at a participating school, you can continue to create an account and link your school. Otherwise you can explore this page or sign in if you already have an account.
                </p>
                <div class="d-flex flex-wrap gap-2 mb-3">
                  <a href="{{ route('competitions.public.show', [$competition, 'eligible' => 'yes']) }}" class="badge bg-primary text-white">I’m a teacher — continue to sign up</a>
                  <a href="{{ $loginWithReturn }}" class="badge bg-secondary text-white">I already have an account</a>
                </div>
                <p class="mb-0"><a href="{{ $competitionPagePath }}" class="small">← Back to competition details</a></p>
              @else
                <h3 class="fs-5 mb-2">Are you eligible?</h3>

                <div class="d-flex flex-wrap gap-2 mb-0">
                  <a href="{{ route('competitions.public.show', [$competition, 'eligible' => 'yes']) }}" class="badge bg-primary text-white">Yes, I’m eligible</a>
                  <a href="{{ route('competitions.public.show', [$competition, 'signup' => 'help']) }}" class="badge bg-secondary text-white">Not sure</a>
                </div>
                <p class="small text-muted mt-3 mb-0">Already registered? <a href="{{ $loginWithReturn }}" class="text-primary">Sign in</a>.</p>
              @endif
            </div>
          @else
            <div class="alert {{ $publicTeacherSignupNotice ? 'alert-info' : 'alert-secondary' }} mb-0">
              @if($publicTeacherSignupNotice)
                {{ $publicTeacherSignupNotice }}
              @else
                Registration is closed. This competition is not accepting new teacher sign-ups on this page.
              @endif
            </div>
          @endif
        @endauth

      </div>
    </div>
  </section>
@endunless
</main>
@endsection

@push('styles')
  <style>
    .competition-rich-text {
      word-break: break-word;
    }
    .competition-rich-text > :last-child {
      margin-bottom: 0 !important;
    }
    .competition-rich-text img {
      max-width: 100%;
      height: auto;
    }
    .competition-rich-text table {
      max-width: 100%;
    }
    .competition-rich-text a {
      word-break: break-word;
    }
  </style>
  @if($showTeacherSignupForm)
    <link rel="stylesheet" href="{{ asset('client/assets/vendors/choices.js/choices.min.css') }}">
    <style>
      .competition-teacher-signup-card .choices { margin-bottom: 0; }
      .competition-teacher-signup-card .choices__inner {
        min-height: calc(1.5em + 1rem + 2px);
        padding: 0.5rem 0.5rem !important;
        background-color: var(--bs-body-bg, #fff) !important;
        border: 1px solid var(--bs-border-color, #dee2e6) !important;
        border-radius: var(--bs-border-radius, 0.375rem) !important;
      }
      .competition-teacher-signup-card .choices__list--dropdown { z-index: 20; }
    </style>
  @endif
@endpush

@push('scripts')
  @if($showTeacherSignupForm)
  <script src="{{ asset('client/assets/vendors/choices.js/choices.min.js') }}"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const schoolSelect = document.getElementById('school_id');
      const schoolSearchInput = document.getElementById('school_search');
      const isNewSchoolCheckbox = document.getElementById('is_new_school');
      const newSchoolFields = document.getElementById('newSchoolFields');
      const schoolNameInput = document.getElementById('school_name');
      const form = document.getElementById('competitionPublicJoinForm');
      let choicesInstance = null;

      if (schoolSelect && isNewSchoolCheckbox && form) {
        const schoolOptions = Array.from(schoolSelect.options)
          .filter(option => option.value !== '')
          .map(option => ({ value: option.value, label: option.text }));

        if (typeof Choices !== 'undefined') {
          choicesInstance = new Choices(schoolSelect, {
            searchEnabled: true,
            searchChoices: true,
            itemSelectText: '',
            placeholder: true,
            placeholderValue: '-- Select a school --',
            searchPlaceholderValue: 'Search schools...',
          });
        }

        function applySchoolFilter(query) {
          const searchText = (query || '').trim().toLowerCase();
          const filteredOptions = schoolOptions.filter(option =>
            option.label.toLowerCase().includes(searchText)
          );
          if (choicesInstance) {
            choicesInstance.clearChoices();
            choicesInstance.setChoices(filteredOptions, 'value', 'label', true);
          } else {
            const currentValue = schoolSelect.value;
            schoolSelect.innerHTML = '<option value="">-- Select a school --</option>';
            filteredOptions.forEach(option => {
              const optionElement = document.createElement('option');
              optionElement.value = option.value;
              optionElement.textContent = option.label;
              if (option.value === currentValue) optionElement.selected = true;
              schoolSelect.appendChild(optionElement);
            });
          }
        }

        function toggleNewSchoolFields() {
          if (isNewSchoolCheckbox.checked) {
            newSchoolFields.style.display = 'block';
            if (choicesInstance) choicesInstance.disable();
            if (schoolSearchInput) schoolSearchInput.setAttribute('disabled', 'disabled');
            schoolSelect.removeAttribute('required');
            if (schoolNameInput) schoolNameInput.setAttribute('required', 'required');
          } else {
            newSchoolFields.style.display = 'none';
            if (choicesInstance) choicesInstance.enable();
            if (schoolSearchInput) schoolSearchInput.removeAttribute('disabled');
            schoolSelect.setAttribute('required', 'required');
            if (schoolNameInput) schoolNameInput.removeAttribute('required');
          }
        }

        toggleNewSchoolFields();

        if (schoolSearchInput) {
          schoolSearchInput.addEventListener('input', function () {
            applySchoolFilter(this.value);
          });
        }

        isNewSchoolCheckbox.addEventListener('change', toggleNewSchoolFields);

        form.addEventListener('submit', function (e) {
          if (isNewSchoolCheckbox.checked) {
            if (schoolNameInput && !schoolNameInput.value.trim()) {
              e.preventDefault();
              alert('Please enter a school name.');
              schoolNameInput.focus();
              return false;
            }
          } else {
            const selectedValue = choicesInstance ? choicesInstance.getValue(true) : schoolSelect.value;
            if (!selectedValue || selectedValue === '') {
              e.preventDefault();
              alert('Please select a school or check "Add new school" to register a new one.');
              return false;
            }
          }
        });
      }
    });
  </script>
  @endif
@endpush
