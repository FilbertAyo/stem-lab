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

        
        <section class="row mb-4">
        @if(Auth::user()->role === 'teacher' && $stats['total_competitions'] > 0)
          <div class="col-6 col-lg-3 col-md-6 mb-3">
            <div class="card border shadow-none h-100">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="flex-grow-1 ms-3">
                    <h6 class="text-muted mb-1 small">Competition tasks</h6>
                    <h4 class="mb-0 fw-bold">{{ $competitionNotificationCount }}</h4>
                  </div>
                </div>
                <a href="{{ route('client.competitions.index') }}" class="btn btn-primary text-white text-decoration-none mt-2">
                  Open competitions
                </a>
              </div>
            </div>
          </div>
          @foreach(collect($competitionShortcuts)->slice(1, 2) as $shortcut)
            <div class="col-6 col-lg-3 col-md-6 mb-3">
              <div class="card border shadow-none h-100">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <div class="flex-grow-1 ms-3">
                      <h6 class="text-muted mb-1 small">{{ $shortcut['label'] }}</h6>
                      <h4 class="mb-0 fw-bold">{{ $shortcut['value'] }}</h4>
                    </div>
                  </div>
                  <a href="{{ $shortcut['route'] }}" class="btn btn-primary text-white text-decoration-none mt-2">
                    View
                  </a>
                </div>
              </div>
            </div>
          @endforeach
        @endif

          <div class="col-6 col-lg-3 col-md-6 mb-3">
                <div class="card border shadow-none h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1 small">Models Completed</h6>
                                <h4 class="mb-0 fw-bold">{{ $stats['models_completed'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3 col-md-6 mb-3">
                <div class="card border shadow-none h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1 small">VR Assessments</h6>
                                <h4 class="mb-0 fw-bold">{{ $stats['completed_vr_attempts'] }}</h4>
                                @if($stats['total_vr_attempts'] > 0)
                                    <small class="text-muted">{{ $stats['passed_vr_attempts'] }} passed</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if(Auth::user()->role === 'teacher' && $stats['total_competitions'] > 0)
                <div class="col-6 col-lg-3 col-md-6 mb-3">
                    <div class="card border shadow-none h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1 small">Competitions</h6>
                                    <h4 class="mb-0 fw-bold">{{ $stats['total_competitions'] }}</h4>
                                    @if($stats['total_submissions'] > 0)
                                        <small class="text-muted">{{ $stats['total_submissions'] }} submissions</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if(Auth::user()->role === 'teacher' && $stats['total_students'] > 0)
                <div class="col-6 col-lg-3 col-md-6 mb-3">
                    <div class="card border shadow-none h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1 small">My Students</h6>
                                    <h4 class="mb-0 fw-bold">{{ $stats['total_students'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </section>

        @include('client.back.partials.package')

          </div>

</x-app-layout>