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
          <a href="{{ route('client.projects.index') }}" class="{{ request()->routeIs('client.projects.*') ? 'active' : '' }}">Projects</a>

          @php($isTeacher = Auth::user()->role === 'teacher')
          <a href="{{ $isTeacher ? route('client.weekly-sessions.index') : route('client.vr-assessments.index') }}"
             class="{{ request()->routeIs('client.weekly-sessions.*')
                        || request()->routeIs('client.clubs.*')
                        || request()->routeIs('client.vr-assessments.*')
                      ? 'active' : '' }}">
            Activities
          </a>
          @if(Auth::user()->role === 'teacher')
          <a href="{{ route('client.competitions.index') }}" class="{{ request()->routeIs('client.competitions.*') ? 'active' : '' }}">Competitions</a>
          @endif
        </nav>
        <div class="d-flex align-items-center gap-3">

          @php($navUser = Auth::user())
          @php($navName = $navUser->name ?? 'User')
          @php($navInitials = collect(preg_split('/\s+/', trim($navName)))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') ?: 'U')
          @php($navRoleLabel = ($navUser->role ?? '') === 'teacher' ? 'Teacher' : 'Student')

          <div class="dropdown">
            <button
              type="button"
              class="user-nav-toggle dropdown-toggle d-flex align-items-center gap-2"
              id="userDropdown"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              <span class="user-nav-avatar" aria-hidden="true">{{ $navInitials }}</span>
              <span class="user-nav-name d-none d-md-inline text-truncate">{{ $navUser->name ?? 'User' }}</span>
            </button>

            <ul class="dropdown-menu dropdown-menu-end user-nav-dropdown shadow-sm" aria-labelledby="userDropdown">
              <li class="user-nav-dropdown-header">
                <div class="d-flex align-items-center gap-3">
                  <span class="user-nav-avatar user-nav-avatar-lg" aria-hidden="true">{{ $navInitials }}</span>
                  <div class="min-w-0">
                    <span class="fw-semibold d-block text-truncate">{{ $navUser->name ?? 'User' }}</span>
                    <small class="text-muted d-block text-truncate">{{ $navUser->email ?? 'user@example.com' }}</small>
                    <span class="badge bg-light text-dark border mt-2">{{ $navRoleLabel }}</span>
                  </div>
                </div>
              </li>
              <li><hr class="dropdown-divider my-0"></li>
              <li>
                <a href="{{ route('profile.edit') }}" class="dropdown-item d-flex align-items-center gap-2">
                  <i class="bi bi-person-circle"></i>
                  <span>Profile</span>
                </a>
              </li>
              <li>
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button type="submit" class="dropdown-item d-flex align-items-center gap-2">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
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
