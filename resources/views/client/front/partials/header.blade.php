<header class="header position-fixed start-0 top-0 w-100">
    <div class="container px-3">
      <div class="header-wrapper d-flex align-items-center justify-content-between">
        <div class="logo">
          <a href="{{ url('/') }}" class="logo-white">
            <img src="{{ asset('images/logo/logo-white.png') }}" alt="{{ config('app.name') }} Logo" class="img-fluid" style="height:45px; width:auto;">
          </a>
          <a href="{{ url('/') }}" class="logo-dark">
            <img src="{{ asset('images/logo/logo-dark.png') }}" alt="{{ config('app.name') }} Logo" class="img-fluid" style="height:45px; width:auto;">
          </a>
        </div>
        
        <!-- Desktop Navigation - Visible on large screens -->
        <nav class="d-none d-lg-flex align-items-center gap-3">
          <ul class="header-menu list-unstyled mb-0 d-flex align-items-center gap-3">
            <li class="header-item">
              <a href="{{ url('/#labs') }}" class="header-link text-white fw-semibold">Labs</a>
            </li>
           
            <li class="header-item">
              <a href="{{ url('/#insights') }}" class="header-link text-white fw-semibold">Insights</a>
            </li>
            <li class="header-item">
              <a href="{{ route('competitions.public') }}" class="header-link text-white fw-semibold">Competitions</a>
            </li>
          </ul>
          <div class="d-flex align-items-center">
            <a href="{{ route('login') }}" class="btn btn-primary border-0 text-white fs-2 px-4 py-2">Login</a>
          </div>
        </nav>

        <!-- Mobile Menu Button - Visible on small screens -->
        <div class="d-flex d-lg-none align-items-center">
          <div class="btn-group">
            <button
              class="btn btn-secondary toggle-menu round-45 p-2 d-flex align-items-center justify-content-center bg-white bg-opacity-20 rounded-circle"
              type="button" data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
              <iconify-icon icon="solar:hamburger-menu-line-duotone" class="menu-icon fs-8 text-white"></iconify-icon>
            </button>
            <ul class="dropdown-menu dropdown-menu-end p-4">
              <div class="d-flex flex-column gap-6">
                <div class="hstack justify-content-between border-bottom pb-6">
                  <p class="mb-0 fs-5 text-dark">Menu</p>
                  <button type="button" class="btn-close opacity-75" aria-label="Close"></button>
                </div>
                <div class="d-flex flex-column gap-3">
                  <ul class="header-menu list-unstyled mb-0 d-flex flex-column gap-2">
                    <li class="header-item">
                      <a href="{{ url('/#labs') }}" class="header-link hstack gap-2 fs-7 fw-bold text-dark"><img
                          src="{{ asset('client/assets/images/svgs/secondary-leaf.svg') }}" alt="" width="20" height="20"
                          class="img-fluid animate-spin">Labs</a>
                    </li>
                    <li class="header-item">
                      <a href="{{ url('/#impact') }}" class="header-link hstack gap-2 fs-7 fw-bold text-dark"><img
                          src="{{ asset('client/assets/images/svgs/secondary-leaf.svg') }}" alt="" width="20" height="20"
                          class="img-fluid animate-spin">Impact</a>
                    </li>
                    <li class="header-item">
                      <a href="{{ url('/#insights') }}" class="header-link hstack gap-2 fs-7 fw-bold text-dark"><img
                          src="{{ asset('client/assets/images/svgs/secondary-leaf.svg') }}" alt="" width="20" height="20"
                          class="img-fluid animate-spin">Insights</a>
                    </li>
                    <li class="header-item">
                      <a href="{{ route('competitions.public') }}" class="header-link hstack gap-2 fs-7 fw-bold text-dark"><img
                          src="{{ asset('client/assets/images/svgs/secondary-leaf.svg') }}" alt="" width="20" height="20"
                          class="img-fluid animate-spin">Competitions</a>
                    </li>
                  </ul>
                  <div class="hstack">
                    <a href="{{ route('login') }}"
                      class="btn btn-dark text-white fs-6 bg-dark px-4 py-2 w-100 hstack justify-content-center">Get Started</a>
                  </div>
                </div>
                <div>
                  <a class="text-dark" href="tel:+1-212-456-7890">+255 282 561 724</a>
                </div>
              </div>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </header>