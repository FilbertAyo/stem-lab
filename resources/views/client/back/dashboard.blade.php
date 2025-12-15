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
    $latestModels = $latestModels ?? collect();
@endphp

<x-app-layout>
    <div class="page-heading">
        <h3>Dashboard Overview</h3>
        <p class="text-muted">Your learning progress and statistics</p>
    </div>
    <div class="page-content">
        <!-- Statistics Cards -->
        <section class="row mb-4">
       
     

            <!-- Models Completed Card -->
            <div class="col-6 col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
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

            <!-- VR Attempts Card -->
            <div class="col-6 col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
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

            <!-- Competitions Card -->
            @if($stats['total_competitions'] > 0 || $stats['total_students'] > 0)
                <div class="col-6 col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm h-100">
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

            <!-- Students Card (for teachers) -->
            @if($stats['total_students'] > 0)
                <div class="col-6 col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-sm h-100">
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

        <!-- Latest Models Section -->
        @if($latestModels->count() > 0)
            <section class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0 pb-0">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-clock-history me-2"></i>Latest 3D Models
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($latestModels as $model)
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card border h-100">
                                            <div class="card-body">
                                          
                                                <h6 class="fw-semibold mb-2">{{ $model->name }}</h6>
                                                <p class="text-muted small mb-2">
                                                    {{ Str::limit($model->description ?? 'No description provided.', 60) }}
                                                </p>
                                                <a href="{{ route('client.models.3d.show', $model) }}" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-arrow-right me-1"></i>View Model
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif
    </div>
</x-app-layout>