
<header class="mb-3 d-flex align-items-center justify-content-between">
    <a href="#" class="burger-btn d-block d-xl-none">
        <i class="bi bi-list fs-3"></i>
    </a>

    <div class="dropdown ms-auto">
        <a class="d-flex align-items-center text-decoration-none dropdown-toggle" href="#" id="userDropdown"
            data-bs-toggle="dropdown" aria-expanded="false">
            <div class="me-2 text-end">
                <span class="fw-semibold">{{ Auth::user()->name ?? 'User' }}</span>
            </div>
            <div class="avatar avatar-md bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                <i class="bi bi-person-fill fs-5"></i>
            </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end mt-3 p-3 shadow" aria-labelledby="userDropdown" style="min-width: 250px;">
            <li class="mb-2">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                        <i class="bi bi-person-fill fs-4"></i>
                    </div>
                    <div>
                        <span class="fw-semibold d-block">{{ Auth::user()->name ?? 'User' }}</span>
                        <small class="text-muted">{{ Auth::user()->email ?? 'user@example.com' }}</small>
                    </div>
                </div>
            </li>
            <li><hr class="dropdown-divider"></li>
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
</header>
