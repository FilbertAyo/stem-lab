<header class="dashboard-header position-fixed start-0 top-0 w-100 border">
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
          @php($unreadNotificationCount = $isTeacher ? Auth::user()->unreadNotifications()->count() : 0)
          <a href="{{ $isTeacher ? route('client.my-students.index') : route('client.vr-assessments.index') }}"
             class="{{ request()->routeIs('client.my-students.*')
                        || request()->routeIs('client.notifications.*')
                        || request()->routeIs('client.weekly-sessions.*')
                        || request()->routeIs('client.clubs.*')
                        || request()->routeIs('client.student-club.*')
                        || request()->routeIs('client.vr-assessments.*')
                        || request()->routeIs('client.competitions.*')
                      ? 'active' : '' }}">
            Activities
          </a>
        </nav>
        <div class="d-flex align-items-center gap-3">

          @if($isTeacher)
            <div class="dropdown">
              <button type="button"
                      class="btn btn-link text-dark position-relative p-2 text-decoration-none dropdown-toggle"
                      id="teacherNotificationsDropdown"
                      data-bs-toggle="dropdown"
                      aria-expanded="false"
                      aria-label="Notifications">
                <i class="bi bi-bell fs-5"></i>
                @if($unreadNotificationCount > 0)
                  <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    {{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}
                  </span>
                @endif
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width: 320px;" aria-labelledby="teacherNotificationsDropdown">
                <li class="dropdown-header d-flex justify-content-between align-items-center">
                  <span class="fw-semibold">Notifications</span>
                  <a href="{{ route('client.notifications.index') }}" class="small">View all</a>
                </li>
                @forelse(Auth::user()->unreadNotifications()->limit(5)->get() as $notification)
                  @php($data = $notification->data)
                  <li>
                    <form method="POST" action="{{ route('client.notifications.read', $notification->id) }}">
                      @csrf
                      <button type="submit" class="dropdown-item text-wrap">
                        <span class="d-block small">{{ $data['message'] ?? 'Notification' }}</span>
                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                      </button>
                    </form>
                  </li>
                @empty
                  <li><span class="dropdown-item-text text-muted small">No new notifications</span></li>
                @endforelse
              </ul>
            </div>
          @endif

          @php($navUser = Auth::user())
          @php($navName = $navUser->name ?? 'User')
          @php($navInitials = collect(preg_split('/\s+/', trim($navName)))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') ?: 'U')

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
                    <span class="badge mt-2 {{ $navUser->platformPlanBadgeClass() }}">{{ $navUser->platformPlanLabel() }}</span>
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
                <a href="{{ route('client.billing.index') }}" class="dropdown-item d-flex align-items-center gap-2">
                  <i class="bi bi-credit-card"></i>
                  <span>My plan</span>
                </a>
              </li>
              <li>
                <a href="{{ route('client.billing.transactions') }}" class="dropdown-item d-flex align-items-center gap-2">
                  <i class="bi bi-receipt"></i>
                  <span>Billing history</span>
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
