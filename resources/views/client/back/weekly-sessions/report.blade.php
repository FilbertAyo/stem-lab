<x-app-layout>
    <div class="col-lg-9">

        <div class="dashboard-content-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('client.weekly-sessions.index') }}">Weekly Sessions</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('client.weekly-sessions.show', $weeklySession) }}">{{ $weeklySession->title }}</a></li>
                        <li class="breadcrumb-item active">{{ $report ? 'Edit Report' : 'Submit Report' }}</li>
                    </ol>
                </nav>
                <h2 class="title mb-1">{{ $report ? 'Edit Session Report' : 'Submit Session Report' }}</h2>
                <p class="text-muted mb-0">{{ $weeklySession->title }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('client.weekly-sessions.show', $weeklySession) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Session
                </a>
            </div>
        </div>

        <div class="page-content">
            @if ($errors->any())
                <div class="card border-0 shadow-none mb-4">
                    <div class="card-body alert alert-danger mb-0">
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ $report ? route('client.weekly-sessions.report.update', $weeklySession) : route('client.weekly-sessions.report.store', $weeklySession) }}" method="POST" enctype="multipart/form-data" id="assessmentForm">
                @csrf
                @if($report)
                    @method('PUT')
                @endif

                <!-- Progress Indicator -->
                <div class="card border-0 shadow-none mb-4">
                    <div class="card-body py-2">
                        <div class="d-flex align-items-center justify-content-between">
                            <small class="text-muted">Complete the sections below. All fields are optional.</small>
                            <small class="text-muted" id="progressText">Section 1 of 12</small>
                        </div>
                    </div>
                </div>

                <!-- Session Info (Read-only) -->
                <div class="card border-0 shadow-none mb-4">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3"><i class="bi bi-info-circle me-2"></i>Session Information</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <span class="text-muted small d-block">Title/Topic</span>
                                <span class="fw-semibold">{{ $weeklySession->title }}</span>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted small d-block">Date</span>
                                <span class="fw-semibold">{{ $weeklySession->session_date->format('M d, Y') }}</span>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted small d-block">Club</span>
                                <span class="badge bg-info">{{ $weeklySession->club->name }}</span>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted small d-block">Classes</span>
                                <div>
                                    @foreach($weeklySession->classes as $class)
                                        <span class="badge bg-primary me-1">{{ $class->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- The rest of the form is the same as before, just with card border-0 shadow-none -->
                @include('client.back.weekly-sessions.report-form-fields', ['weeklySession' => $weeklySession, 'report' => $report ?? null, 'totalStudentsRegistered' => $totalStudentsRegistered ?? 0])

                <!-- Submit Buttons -->
                <div class="card border-0 shadow-none mb-4">
                    <div class="card-body">
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle me-1"></i> {{ $report ? 'Update Report' : 'Submit Report' }}
                            </button>
                            <a href="{{ route('client.weekly-sessions.show', $weeklySession) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x me-1"></i> Cancel
                            </a>
                        </div>
                        <small class="text-muted d-block mt-2">
                            <i class="bi bi-info-circle me-1"></i>
                            All fields are optional. Fill in what you can - your feedback helps improve the program.
                        </small>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        @include('client.back.weekly-sessions.report-form-scripts')
    @endpush
</x-app-layout>

