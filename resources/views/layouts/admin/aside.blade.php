
<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <a href="{{ route('admin.dashboard') }}" class="text-primary">
          <img src="{{ asset('images/logo/logo-dark.png') }}" class="img-fluid logo-lg" alt="Adilisha STEM Lab" style="height: 60px;padding: 5px 26px;">
        </a>

      <div class="navbar-content">
        <ul class="pc-navbar">
          <li class="pc-item">
            <a href="{{ route('admin.dashboard') }}" class="pc-link">
              <span class="pc-micon"><i class="ti ti-dashboard"></i></span>
              <span class="pc-mtext">Dashboard</span>
            </a>
          </li>

          <li class="pc-item pc-caption">
            <label>Managements</label>
            <i class="ti ti-dashboard"></i>
          </li>
          <li class="pc-item pc-hasmenu">
            <a href="#!" class="pc-link">
              <span class="pc-micon"><i class="ti ti-book"></i></span>
              <span class="pc-mtext">Classes & Subjects</span>
              <span class="pc-arrow"><i class="ti ti-chevron-right"></i></span>
            </a>
            <ul class="pc-submenu">
              <li class="pc-item">
                <a href="{{ route('admin.classes.index') }}" class="pc-link">
                  <span class="pc-mtext">Classes</span>
                </a>
              </li>
              <li class="pc-item">
                <a href="{{ route('subjects.index') }}" class="pc-link">
                  <span class="pc-mtext">Subjects</span>
                </a>
              </li>
            </ul>
          </li>
          <li class="pc-item pc-hasmenu">
            <a href="#!" class="pc-link">
              <span class="pc-micon"><i class="ti ti-home"></i></span>
              <span class="pc-mtext">Schools & Clubs</span>
              <span class="pc-arrow"><i class="ti ti-chevron-right"></i></span>
            </a>
            <ul class="pc-submenu">
            <li class="pc-item">
                <a href="{{ route('admin.clubs.index') }}" class="pc-link">
                  <span class="pc-mtext">Clubs</span>
                </a>
              </li>
              <li class="pc-item">
                <a href="{{ route('admin.schools.index') }}" class="pc-link">
                  <span class="pc-mtext">Schools</span>
                </a>
              </li>
             
            </ul>
          </li>
          <li class="pc-item pc-hasmenu">
            <a href="#!" class="pc-link">
              <span class="pc-micon"><i class="ti ti-package"></i></span>
              <span class="pc-mtext">3D & VR Resources</span>
              <span class="pc-arrow"><i class="ti ti-chevron-right"></i></span>
            </a>
            <ul class="pc-submenu">
              <li class="pc-item">
                <a href="{{ route('admin.models.3d.index') }}" class="pc-link">
                  <span class="pc-mtext">3D Models</span>
                </a>
              </li>
              <li class="pc-item">
                <a href="{{ route('admin.vr-experiments.index') }}" class="pc-link">
                  <span class="pc-mtext">VR Experiments</span>
                </a>
              </li>
            </ul>
          </li>

          <li class="pc-item pc-caption">
            <label>Assessment & Competition</label>
            <i class="ti ti-chart-bar"></i>
          </li>

          <li class="pc-item pc-hasmenu">
            <a href="#!" class="pc-link">
              <span class="pc-micon"><i class="ti ti-clipboard-check"></i></span>
              <span class="pc-mtext">VR Assessment</span>
              <span class="pc-arrow"><i class="ti ti-chevron-right"></i></span>
            </a>
            <ul class="pc-submenu">
              <li class="pc-item">
                <a href="{{ route('admin.vr-assessments.question-banks.index') }}" class="pc-link">
                  <span class="pc-mtext">Question Banks</span>
                </a>
              </li>
              <li class="pc-item">
                <a href="{{ route('admin.vr-assessments.results.index') }}" class="pc-link">
                  <span class="pc-mtext">Assessment Results</span>
                </a>
              </li>
            </ul>
          </li>

          <li class="pc-item pc-hasmenu">
            <a href="#!" class="pc-link">
              <span class="pc-micon"><i class="ti ti-trophy"></i></span>
              <span class="pc-mtext">Competitions</span>
              <span class="pc-arrow"><i class="ti ti-chevron-right"></i></span>
            </a>
            <ul class="pc-submenu">
              <li class="pc-item">
                <a href="{{ route('admin.competitions.index') }}" class="pc-link">
                  <span class="pc-mtext">All Competitions</span>
                </a>
              </li>
              <li class="pc-item">
                <a href="{{ route('admin.competitions.create') }}" class="pc-link">
                  <span class="pc-mtext">Create Competition</span>
            </a>
              </li>
              <li class="pc-item">
                <a href="{{ route('admin.competitions.all-participants') }}" class="pc-link">
                  <span class="pc-mtext">Manage Participants</span>
                </a>
              </li>
            </ul>
          </li>

          <li class="pc-item">
            <a href="{{ route('admin.progress.index') }}" class="pc-link">
              <span class="pc-micon"><i class="ti ti-box"></i></span>
              <span class="pc-mtext">3D Model Results</span>
            </a>
          </li>

          <li class="pc-item pc-caption">
            <label>Settings</label>
            <i class="ti ti-settings"></i>
          </li>

          <li class="pc-item pc-hasmenu">
            <a href="#!" class="pc-link">
              <span class="pc-micon"><i class="ti ti-users"></i></span>
              <span class="pc-mtext">User Management</span>
              <span class="pc-arrow"><i class="ti ti-chevron-right"></i></span>
            </a>
            <ul class="pc-submenu">
              <li class="pc-item">
                <a href="{{ route('admin.users.index') }}" class="pc-link">
                  <span class="pc-micon"><i class="ti ti-user"></i></span>
                  <span class="pc-mtext">Admin Users</span>
                </a>
              </li>
              <li class="pc-item">
                <a href="{{ route('admin.users.students') }}" class="pc-link">
                  <span class="pc-micon"><i class="ti ti-school"></i></span>
                  <span class="pc-mtext">Students & Teachers</span>
                </a>
              </li>
            </ul>
          </li>

          <li class="pc-item">
            <a href="{{ route('admin.database-backup.index') }}" class="pc-link">
              <span class="pc-micon"><i class="ti ti-database-export"></i></span>
              <span class="pc-mtext">Database Backup</span>
            </a>
          </li>

        </ul>

      </div>
    </div>
  </nav>
