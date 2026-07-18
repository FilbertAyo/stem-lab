@php
    $stats = $stats ?? [
        'total_answers' => 0,
        'correct_answers' => 0,
        'models_completed' => 0,
        'accuracy' => 0,
        'total_vr_attempts' => 0,
        'completed_vr_attempts' => 0,
        'passed_vr_attempts' => 0,
        'total_competitions' => 0,
        'total_submissions' => 0,
        'total_students' => 0,
    ];
    $competitionNotificationCount = $competitionNotificationCount ?? 0;
    $competitionShortcuts = $competitionShortcuts ?? [];
    $clubStats = $clubStats ?? null;
    $isTeacher = Auth::user()->role === 'teacher';
@endphp

<x-app-layout>


 <div class="col-lg-12">

        <div class="dashboard-content-header">
            <h4 class="mb-0">Dashboard</h4>
        </div>

        @if(($showPhoneReminder ?? false) === true)
          <section class="row mb-3">
            <div class="col-12">
              <div class="alert alert-warning d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-0">
                <div class="small fw-medium">
                  Your phone number is required to complete your account setup. Please update your profile with a valid Tanzania number.
                </div>
                <a href="{{ route('profile.edit') }}" class="badge bg-warning border-0 text-dark fw-semibold">
                  Update phone number
                </a>
              </div>
            </div>
          </section>
        @endif

        
        <section class="row g-2 g-md-3 mb-4">
          @if($isTeacher && $clubStats)
            <div class="col-6 col-lg-3">
              <div class="dashboard-stat-card h-100">
                <span class="dashboard-stat-label">Registered students</span>
                <span class="dashboard-stat-value">{{ $clubStats['registered'] }}</span>
                <span class="dashboard-stat-meta">of {{ $clubStats['total_roster'] }} on roster</span>
                <a href="{{ route('client.my-students.index') }}" class="dashboard-stat-link">
                  My Students <iconify-icon icon="lucide:arrow-right"></iconify-icon>
                </a>
              </div>
            </div>
            <div class="col-6 col-lg-3">
              <div class="dashboard-stat-card h-100">
                <span class="dashboard-stat-label">Active this week</span>
                <span class="dashboard-stat-value">{{ $clubStats['active'] }}</span>
                <a href="{{ route('client.my-students.index', ['activity' => 'active']) }}" class="dashboard-stat-link">
                  View active <iconify-icon icon="lucide:arrow-right"></iconify-icon>
                </a>
              </div>
            </div>
            <div class="col-6 col-lg-3">
              <div class="dashboard-stat-card h-100">
                <span class="dashboard-stat-label">Needs attention</span>
                <span class="dashboard-stat-value text-danger">{{ $clubStats['needs_attention'] }}</span>
                <a href="{{ route('client.my-students.index', ['attention' => 'needs_support']) }}" class="dashboard-stat-link is-danger">
                  Review <iconify-icon icon="lucide:arrow-right"></iconify-icon>
                </a>
              </div>
            </div>
          @endif

          @if($isTeacher && $stats['total_competitions'] > 0)
            <div class="col-6 col-lg-3">
              <div class="dashboard-stat-card h-100">
                <span class="dashboard-stat-label">Competition tasks</span>
                <span class="dashboard-stat-value">{{ $competitionNotificationCount }}</span>
                <a href="{{ route('client.competitions.index') }}" class="dashboard-stat-link">
                  Open competitions <iconify-icon icon="lucide:arrow-right"></iconify-icon>
                </a>
              </div>
            </div>
            @foreach(collect($competitionShortcuts)->slice(1, 2) as $shortcut)
              <div class="col-6 col-lg-3">
                <div class="dashboard-stat-card h-100">
                  <span class="dashboard-stat-label">{{ $shortcut['label'] }}</span>
                  <span class="dashboard-stat-value">{{ $shortcut['value'] }}</span>
                  <a href="{{ $shortcut['route'] }}" class="dashboard-stat-link">
                    View <iconify-icon icon="lucide:arrow-right"></iconify-icon>
                  </a>
                </div>
              </div>
            @endforeach
            <div class="col-6 col-lg-3">
              <div class="dashboard-stat-card h-100">
                <span class="dashboard-stat-label">Competitions</span>
                <span class="dashboard-stat-value">{{ $stats['total_competitions'] }}</span>
                @if($stats['total_submissions'] > 0)
                  <span class="dashboard-stat-meta">{{ $stats['total_submissions'] }} submissions</span>
                @endif
              </div>
            </div>
          @endif

          @if(! $isTeacher)
            <div class="col-6 col-lg-3">
              <div class="dashboard-stat-card h-100">
                <span class="dashboard-stat-label">Models completed</span>
                <span class="dashboard-stat-value">{{ $stats['models_completed'] }}</span>
              </div>
            </div>
            <div class="col-6 col-lg-3">
              <div class="dashboard-stat-card h-100">
                <span class="dashboard-stat-label">VR assessments</span>
                <span class="dashboard-stat-value">{{ $stats['completed_vr_attempts'] }}</span>
                @if($stats['total_vr_attempts'] > 0)
                  <span class="dashboard-stat-meta">{{ $stats['passed_vr_attempts'] }} passed</span>
                @endif
              </div>
            </div>
          @endif
        </section>

        @include('client.back.partials.package')

          </div>

</x-app-layout>
