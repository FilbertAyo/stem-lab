<div id="sidebar" class="active">
    <div class="sidebar-wrapper active">
        <div class="sidebar-header">
            <div class="d-flex justify-content-between">
                <div class="logo">
                    <a href="{{ route('dashboard') }}">
                        <img src="{{ asset('images/logo/logo-dark.png') }}" alt="Adilisha STEM Lab" style="height: 50px; width: auto;">
                    </a>
                </div>
                <div class="toggler">
                    <a href="#" class="sidebar-hide d-xl-none d-block">
                        <i class="bi bi-x bi-middle"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="sidebar-menu">
            <ul class="menu">

                <li class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}" class="sidebar-link">
                        <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
            

      
                <li class="sidebar-item {{  request()->routeIs('client.vr-experiments.*') ? 'active' : '' }}">
                    <a href="{{ route('client.vr-experiments.index') }}" class="sidebar-link">
                    <i class="bi bi-vr me-1"></i>
                        <span>Labs</span>
                    </a>
                </li>

                <li class="sidebar-item {{ request()->routeIs('client.models.3d.*') ? 'active' : '' }}">
                    <a href="{{ route('client.models.3d.index') }}" class="sidebar-link">
                        <i class="bi bi-box"></i>
                        <span>3D Models</span>
                    </a>
                </li>

                @if(auth()->user()->role === 'teacher')
                <li class="sidebar-item {{ request()->routeIs('client.weekly-sessions.*') ? 'active' : '' }}">
                        <a href="{{ route('client.weekly-sessions.index') }}" class="sidebar-link">
                            <i class="bi bi-calendar-week"></i>
                            <span>Weekly Sessions</span>
                        </a>
                    </li>
               
                    <li class="sidebar-item {{ request()->routeIs('client.clubs.*') ? 'active' : '' }}">
                        <a href="{{ route('client.clubs.index') }}" class="sidebar-link">
                            <i class="bi bi-house"></i>
                            <span>Clubs</span>
                        </a>
                    </li>
                    <li class="sidebar-item {{ request()->routeIs('client.students.*') ? 'active' : '' }}">
                        <a href="{{ route('client.students.index') }}" class="sidebar-link">
                            <i class="bi bi-people"></i>
                            <span>Members</span>
                        </a>
                    </li>
                   
                @endif

                <li class="sidebar-item {{ request()->routeIs('client.vr-assessments.*') ? 'active' : '' }}">
                    <a href="{{ route('client.vr-assessments.index') }}" class="sidebar-link">
                        <i class="bi bi-question-circle"></i>
                        <span>VR Quizzes</span>
                    </a>
                </li>

                @if(auth()->user()->role === 'teacher')
                    <li class="sidebar-item {{ request()->routeIs('client.competitions.*') ? 'active' : '' }}">
                        <a href="{{ route('client.competitions.index') }}" class="sidebar-link">
                        <i class="bi bi-trophy"></i>
                            <span>Competitions</span>
                    </a>
                </li>
                @endif


            </ul>
        </div>
        <button class="sidebar-toggler btn x"><i data-feather="x"></i></button>
    </div>
</div>
