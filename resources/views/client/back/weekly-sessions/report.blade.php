<x-app-layout>
    <div class="page-heading d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('client.weekly-sessions.index') }}">Weekly Sessions</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('client.weekly-sessions.show', $weeklySession) }}">{{ $weeklySession->title }}</a></li>
                    <li class="breadcrumb-item active">{{ $report ? 'Edit Report' : 'Submit Report' }}</li>
                </ol>
            </nav>
            <h3 class="mb-1">{{ $report ? 'Edit Session Report' : 'Submit Session Report' }}</h3>
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
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form action="{{ $report ? route('client.weekly-sessions.report.update', $weeklySession) : route('client.weekly-sessions.report.store', $weeklySession) }}" method="POST">
                            @csrf
                            @if($report)
                                @method('PUT')
                            @endif

                            <!-- Session Info (Read-only) -->
                            <div class="mb-4 p-3 bg-light rounded">
                                <h6 class="fw-semibold mb-3">Session Information</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <span class="text-muted small d-block">Title</span>
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

                            <!-- Attendance Count -->
                            <div class="mb-4">
                                <label for="attendance_count" class="form-label fw-semibold">
                                    Attendance Count <span class="text-muted small">(optional)</span>
                                </label>
                                <input type="number" 
                                       class="form-control @error('attendance_count') is-invalid @enderror" 
                                       id="attendance_count" 
                                       name="attendance_count" 
                                       value="{{ old('attendance_count', $report->attendance_count ?? '') }}" 
                                       min="0"
                                       placeholder="Number of students present">
                                @error('attendance_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Enter the number of students who attended this session.</small>
                            </div>

                            <!-- Feedback -->
                            <div class="mb-4">
                                <label for="feedback" class="form-label fw-semibold">
                                    Feedback <span class="text-muted small">(optional)</span>
                                </label>
                                <textarea class="form-control @error('feedback') is-invalid @enderror" 
                                          id="feedback" 
                                          name="feedback" 
                                          rows="5"
                                          placeholder="What worked well? What didn't work? Any challenges encountered...">{{ old('feedback', $report->feedback ?? '') }}</textarea>
                                @error('feedback')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Share your observations about what worked and what didn't during this session.</small>
                            </div>

                            <!-- Engagement Notes -->
                            <div class="mb-4">
                                <label for="engagement_notes" class="form-label fw-semibold">
                                    Student Engagement Notes <span class="text-muted small">(optional)</span>
                                </label>
                                <textarea class="form-control @error('engagement_notes') is-invalid @enderror" 
                                          id="engagement_notes" 
                                          name="engagement_notes" 
                                          rows="5"
                                          placeholder="Observations about student engagement, participation, interest levels...">{{ old('engagement_notes', $report->engagement_notes ?? '') }}</textarea>
                                @error('engagement_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Note any observations about student engagement and participation.</small>
                            </div>

                            <!-- Conducted At -->
                            <div class="mb-4">
                                <label for="conducted_at" class="form-label fw-semibold">
                                    Session Conducted On <span class="text-muted small">(optional)</span>
                                </label>
                                <input type="date" 
                                       class="form-control @error('conducted_at') is-invalid @enderror" 
                                       id="conducted_at" 
                                       name="conducted_at" 
                                       value="{{ old('conducted_at', $report && $report->conducted_at ? $report->conducted_at->format('Y-m-d') : '') }}">
                                @error('conducted_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Leave blank to use current date/time.</small>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle me-1"></i> {{ $report ? 'Update Report' : 'Submit Report' }}
                                </button>
                                <a href="{{ route('client.weekly-sessions.show', $weeklySession) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x me-1"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
