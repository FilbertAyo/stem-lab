@extends('layouts.landing')

@section('title', 'Sign Up | ' . config('app.name'))
@section('page', 'signup')

@section('content')
  <main>
    <!-- ===== SignUp Form Start ===== -->
    <section class="i pg fh rm ki xn vq gj qp gr hj rp hr">
      <!-- Bg Shapes -->
      <img src="{{ asset('images/shape-06.svg') }}" alt="Shape" class="h j k" />

      <div class="animate_top bb af i va sg hh sm vk xm yi _n jp hi ao kp">
        <!-- Bg Border -->
        <span class="rc h r s zd/2 od zg gh"></span>
        <span class="rc h r q zd/2 od xg mh"></span>


        <form class="sb" method="POST" action="{{ route('register') }}" id="register-form">
          @csrf
          <div class="wb">
            <label class="rc kk wm vb" for="name">Full name</label>
            <input
              type="text"
              name="name"
              id="name"
              value="{{ old('name') }}"
              placeholder="Full name"
              required
              autocomplete="name"
              class="vd hh rg zk _g ch hm dm fm pl/50 xi mi sm xm pm dn/40"
            />
            @error('name')
              <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div class="wb">
            <label class="rc kk wm vb" for="email">Email</label>
            <input
              type="email"
              name="email"
              id="email"
              value="{{ old('email') }}"
              placeholder="you@example.org"
              required
              autocomplete="email"
              class="vd hh rg zk _g ch hm dm fm pl/50 xi mi sm xm pm dn/40"
            />
            @error('email')
              <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div class="wb">
            <label class="rc kk wm vb" for="role">Who are you?</label>
            <select
              name="role"
              id="role"
              required
              class="vd hh rg zk _g ch hm dm fm pl/50 xi mi sm xm pm dn/40"
            >
              <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select identity</option>
              <option value="student" {{ old('role') === 'student' ? 'selected' : '' }}>Student</option>
              <option value="teacher" {{ old('role') === 'teacher' ? 'selected' : '' }}>Teacher</option>
            </select>
            @error('role')
              <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div class="wb">
            <label class="rc kk wm vb" for="password">Password</label>
            <div class="relative">
              <input
                type="password"
                name="password"
                id="password"
                placeholder="**************"
                required
                autocomplete="new-password"
                class="vd hh rg zk _g ch hm dm fm pl/50 xi mi sm xm pm dn/40 pr-10"
              />
              <button
                type="button"
                id="toggle-password"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none cursor-pointer z-10"
                aria-label="Toggle password visibility"
              >
                <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                <svg id="eye-off-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                </svg>
              </button>
            </div>
            @error('password')
              <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
            @if($errors->has('password'))
              @foreach($errors->get('password') as $error)
                <p class="mt-2 text-sm text-red-600">{{ $error }}</p>
              @endforeach
            @endif
          </div>

          <div class="wb">
            <label class="rc kk wm vb" for="password_confirmation">Confirm Password</label>
            <div class="relative">
              <input
                type="password"
                name="password_confirmation"
                id="password_confirmation"
                placeholder="**************"
                required
                autocomplete="new-password"
                class="vd hh rg zk _g ch hm dm fm pl/50 xi mi sm xm pm dn/40 pr-10"
              />
              <button
                type="button"
                id="toggle-password-confirmation"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none cursor-pointer z-10"
                aria-label="Toggle password visibility"
              >
                <svg id="eye-icon-confirmation" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                <svg id="eye-off-icon-confirmation" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                </svg>
              </button>
            </div>
          </div>

          <button type="submit" class="vd rj ek rc rg gh lk ml il _l gi hi">
            Sign Up
          </button>

          <div
            id="register-loading-indicator"
            class="mt-6"
            style="display: none; align-items: center; justify-content: center; gap: 0.75rem;"
            role="status"
            aria-live="polite"
          >
            <span class="register-spinner" aria-hidden="true"></span>
            <span class="sr-only">Creating your account...</span>
          </div>

          <p class="sj hk xj rj ob">
            Already have an account?
            <a class="mk" href="{{ route('login') }}"> Sign In </a>
          </p>
        </form>
      </div>
    </section>
    <!-- ===== SignUp Form End ===== -->
  </main>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const passwordConfirmationInput = document.getElementById('password_confirmation');

    // Password visibility toggle for password field
    const togglePasswordBtn = document.getElementById('toggle-password');
    const eyeIcon = document.getElementById('eye-icon');
    const eyeOffIcon = document.getElementById('eye-off-icon');
    
    if (togglePasswordBtn && passwordInput) {
      togglePasswordBtn.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        if (eyeIcon && eyeOffIcon) {
          eyeIcon.classList.toggle('hidden');
          eyeOffIcon.classList.toggle('hidden');
        }
      });
    }

    // Password visibility toggle for confirmation field
    const togglePasswordConfirmationBtn = document.getElementById('toggle-password-confirmation');
    const eyeIconConfirmation = document.getElementById('eye-icon-confirmation');
    const eyeOffIconConfirmation = document.getElementById('eye-off-icon-confirmation');
    
    if (togglePasswordConfirmationBtn && passwordConfirmationInput) {
      togglePasswordConfirmationBtn.addEventListener('click', function() {
        const type = passwordConfirmationInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordConfirmationInput.setAttribute('type', type);
        if (eyeIconConfirmation && eyeOffIconConfirmation) {
          eyeIconConfirmation.classList.toggle('hidden');
          eyeOffIconConfirmation.classList.toggle('hidden');
        }
      });
    }
  });
</script>
@endpush
