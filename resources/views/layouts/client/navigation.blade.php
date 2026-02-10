<header class="dashboard-header position-fixed start-0 top-0 w-100 z-3 border">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between">
        <a href="{{ config('app.url', 'https://adilishastemlabs.com') }}" class="logo-dark">
          <img src="{{ asset('images/logo/logo-dark.png') }}" alt="STEM Labs" class="img-fluid" style="height: 35px;">
        </a>
        <nav class="dashboard-nav" id="dashboardNav">
          <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
          <a href="{{ route('client.vr-experiments.index') }}" class="{{ request()->routeIs('client.vr-experiments.*') ? 'active' : '' }}">Labs</a>
          <a href="{{ route('client.models.3d.index') }}" class="{{ request()->routeIs('client.models.3d.*') ? 'active' : '' }}">3D Models</a>
          <a href="{{ route('client.weekly-sessions.index') }}"
             class="{{ request()->routeIs('client.weekly-sessions.*')
                        || request()->routeIs('client.clubs.*')
                        || request()->routeIs('client.students.*')
                        || request()->routeIs('client.vr-assessments.*')
                        || request()->routeIs('client.competitions.*')
                      ? 'active' : '' }}">
            Activities
          </a>
        </nav>
        <div class="d-flex align-items-center gap-3">
     
          <!-- User Dropdown -->
          <div class="dropdown">
            <a class="d-flex align-items-center text-decoration-none dropdown-toggle" href="#" id="userDropdown"
            data-bs-toggle="dropdown" aria-expanded="false">
              <div class="me-2 text-end">
                  <span class="fw-semibold">{{ Auth::user()->name ?? 'User' }}</span>
              </div>
           
            </a>
            <ul class="dropdown-menu dropdown-menu-end mt-3 p-3 shadow" aria-labelledby="userDropdown" style="min-width: 250px;">
              <li class="mb-2">
                  <div class="d-flex align-items-center">
                     
                      <div>
                          <span class="fw-semibold d-block">{{ Auth::user()->name ?? 'User' }}</span>
                          <small class="text-muted">{{ Auth::user()->email ?? 'user@example.com' }}</small>
                      </div>
                  </div>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                  <a href="{{ route('profile.edit') }}" class='dropdown-item d-flex align-items-center'>
                      <i class='bi bi-person-circle me-2'></i>Profile
                  </a>
              </li>
              <li>
                  <form method="POST" action="{{ route('logout') }}">
                      @csrf
                      <button type="submit" class="dropdown-item d-flex align-items-center">
                          <i class="bi bi-box-arrow-right me-2"></i> Logout
                      </button>
                  </form>
              </li>
            </ul>
          </div>

          <button class="nav-toggle" type="button" id="navToggle" aria-label="Toggle menu">
            <iconify-icon icon="solar:hamburger-menu-line-duotone" class="fs-6 text-dark"></iconify-icon>
          </button>
        </div>
      </div>
    </div>
</header>
