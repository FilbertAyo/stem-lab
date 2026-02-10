<x-app-layout>
    <div class="col-lg-9">

        <div class="dashboard-content-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('client.weekly-sessions.index') }}">Weekly Sessions</a></li>
                        <li class="breadcrumb-item active">{{ $weeklySession->title }}</li>
                    </ol>
                </nav>
                <h2 class="title mb-1">{{ $weeklySession->title }}</h2>
                <p class="text-muted mb-0">
                    <span class="badge {{ $weeklySession->status_badge }}">{{ ucfirst($weeklySession->status) }}</span>
                    <span class="ms-2">{{ $weeklySession->club->name }}</span>
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('client.weekly-sessions.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Sessions
                </a>
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Session Information -->
                <div class="card border-0 shadow-none mb-4">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3">Session Objectives</h5>
                        <p class="text-muted mb-0">
                            {{ $weeklySession->description ?: 'No description provided for this session.' }}
                        </p>
                    </div>
                </div>

                <!-- Resources Section -->
                <div class="card border-0 shadow-none mb-4">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bi bi-package me-2"></i>Session Resources</h6>
                    </div>
                    <div class="card-body">
                        @if($weeklySession->vr_experiment_id || $weeklySession->model3ds->count() > 0)
                            @if($weeklySession->vr_experiment_id)
                                <div class="mb-4">
                                    <div class="d-flex align-items-center justify-content-between p-3 border rounded">
                                        <div>
                                            <h6 class="fw-semibold mb-1">
                                                <i class="bi bi-vr text-success me-2"></i>{{ $weeklySession->vrExperiment->name }}
                                            </h6>
                                            @if($weeklySession->vrExperiment->description)
                                                <p class="text-muted small mb-0">{{ \Illuminate\Support\Str::limit($weeklySession->vrExperiment->description, 100) }}</p>
                                            @endif
                                        </div>
                                        <a href="{{ $weeklySession->vrExperiment->external_url }}"
                                           target="_blank"
                                           class="btn btn-success">
                                            <i class="bi bi-play-fill me-1"></i> Launch VR
                                        </a>
                                    </div>
                                </div>
                            @endif

                            @if($weeklySession->model3ds->count() > 0)
                                <div>
                                    <h6 class="fw-semibold mb-3">3D Models ({{ $weeklySession->model3ds->count() }})</h6>
                                    <div class="row g-3">
                                        @foreach($weeklySession->model3ds as $model3d)
                                            <div class="col-md-6">
                                                <div class="p-3 border rounded h-100">
                                                    <h6 class="fw-semibold mb-2">
                                                        <i class="bi bi-box text-warning me-2"></i>{{ $model3d->name }}
                                                    </h6>
                                                    @if($model3d->subject)
                                                        <span class="badge bg-light text-dark mb-2">{{ $model3d->subject->subject_name }}</span>
                                                    @endif
                                                    <div class="mt-2">
                                                        <a href="{{ route('client.models.3d.show', $model3d) }}"
                                                           target="_blank"
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-eye me-1"></i> View Model
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @else
                            <p class="text-muted mb-0">No resources assigned to this session.</p>
                        @endif
                    </div>
                </div>

                <!-- Assigned Classes -->
                <div class="card border-0 shadow-none mb-4">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bi bi-book me-2"></i>Assigned Classes</h6>
                    </div>
                    <div class="card-body">
                        @if($weeklySession->classes->count() > 0)
                            <div class="row g-2">
                                @foreach($weeklySession->classes as $class)
                                    <div class="col-auto">
                                        <span class="badge bg-primary">
                                            {{ $class->name }}
                                            @if(isset($studentCountsByClass[$class->id]))
                                                ({{ $studentCountsByClass[$class->id] }} students)
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0">No classes assigned to this session.</p>
                        @endif
                    </div>
                </div>

                <!-- Assigned Students -->
                @if($students->count() > 0)
                    <div class="card border-0 shadow-none mb-4">
                        <div class="card-header bg-transparent">
                            <h6 class="mb-0"><i class="bi bi-people me-2"></i>Assigned Students ({{ $students->count() }})</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Class</th>
                                            <th>Age</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($students as $student)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $student->name }}</td>
                                                <td>
                                                    <span class="badge bg-primary">{{ $student->schoolClass->name }}</span>
                                                </td>
                                                <td>{{ $student->age }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Session Details -->
                <div class="card border-0 shadow-none mb-4 sticky-top" style="top: 20px;">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Session Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <span class="text-muted small d-block">Session Date</span>
                            <span class="fw-semibold">
                                <i class="bi bi-calendar-event me-1"></i>
                                {{ $weeklySession->session_date->format('l, F d, Y') }}
                            </span>
                        </div>
                        <div class="mb-3">
                            <span class="text-muted small d-block">Club</span>
                            <span class="badge bg-info">{{ $weeklySession->club->name }}</span>
                        </div>
                        @if($weeklySession->subject)
                            <div class="mb-3">
                                <span class="text-muted small d-block">Subject</span>
                                <span class="badge bg-secondary">{{ $weeklySession->subject->subject_name }}</span>
                            </div>
                        @endif
                        <div class="mb-0">
                            <span class="text-muted small d-block">Status</span>
                            <span class="badge {{ $weeklySession->status_badge }}">{{ ucfirst($weeklySession->status) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Report Actions -->
                <div class="card border-0 shadow-none">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bi bi-file-text me-2"></i>Session Report</h6>
                    </div>
                    <div class="card-body">
                        @if($report)
                            <div class="mb-3">
                                <span class="badge bg-success mb-2">
                                    <i class="bi bi-check-circle me-1"></i>Report Submitted
                                </span>
                                <p class="text-muted small mb-0">
                                    Submitted on {{ $report->submitted_at->format('M d, Y H:i') }}
                                </p>
                                @if($report->pdf_file)
                                    <p class="text-muted small mb-0 mt-2">
                                        <i class="bi bi-file-pdf text-danger me-1"></i>PDF Report Uploaded
                                    </p>
                                @endif
                            </div>
                            <div class="d-grid gap-2">
                                @if($report->pdf_file)
                                    <a href="{{ asset('storage/' . $report->pdf_file) }}" target="_blank" class="btn btn-outline-danger">
                                        <i class="bi bi-file-pdf me-1"></i> View PDF Report
                                    </a>
                                    <a href="{{ route('client.weekly-sessions.report.edit', $weeklySession) }}" class="btn btn-primary">
                                        <i class="bi bi-upload me-1"></i> Replace with Online Form
                                    </a>
                                @else
                                    <a href="{{ route('client.weekly-sessions.report.edit', $weeklySession) }}" class="btn btn-primary">
                                        <i class="bi bi-pencil me-1"></i> Edit Report
                                    </a>
                                @endif
                            </div>
                        @else
                            <p class="text-muted small mb-3">Submit a report after conducting this session. Choose either option:</p>
                            <div class="d-grid gap-2">
                                <a href="{{ route('client.weekly-sessions.report.create', $weeklySession) }}" class="btn btn-success">
                                    <i class="bi bi-file-text me-1"></i> Fill Online Form
                                </a>
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#uploadPdfModal">
                                    <i class="bi bi-file-pdf me-1"></i> Upload PDF Report
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- PDF Upload Modal -->
                @if(!$report)
                    <div class="modal fade" id="uploadPdfModal" tabindex="-1" aria-labelledby="uploadPdfModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('client.weekly-sessions.report.upload-pdf', $weeklySession) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="uploadPdfModalLabel">
                                            <i class="bi bi-file-pdf text-danger me-2"></i>Upload PDF Report
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="pdf_file" class="form-label">PDF File <span class="text-danger">*</span></label>
                                            <input type="file" class="form-control @error('pdf_file') is-invalid @enderror"
                                                   id="pdf_file" name="pdf_file" accept=".pdf" required>
                                            @error('pdf_file')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Please upload a PDF file (max size: 10MB)</small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">
                                            <i class="bi bi-upload me-1"></i> Upload PDF
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

*** End Patch***"/>} forControlEvents:UIControlEventTouchUpInside];\n}\n```"]}]} ***!
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Session Information -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3">Session Objectives</h5>
                        <p class="text-muted mb-0">
                            {{ $weeklySession->description ?: 'No description provided for this session.' }}
                        </p>
                    </div>
                </div>

                <!-- Resources Section -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bi bi-package me-2"></i>Session Resources</h6>
                    </div>
                    <div class="card-body">
                        @if($weeklySession->vr_experiment_id || $weeklySession->model3ds->count() > 0)
                            @if($weeklySession->vr_experiment_id)
                                <div class="mb-4">
                                    <div class="d-flex align-items-center justify-content-between p-3 border rounded">
                                        <div>
                                            <h6 class="fw-semibold mb-1">
                                                <i class="bi bi-vr text-success me-2"></i>{{ $weeklySession->vrExperiment->name }}
                                            </h6>
                                            @if($weeklySession->vrExperiment->description)
                                                <p class="text-muted small mb-0">{{ \Illuminate\Support\Str::limit($weeklySession->vrExperiment->description, 100) }}</p>
                                            @endif
                                        </div>
                                        <a href="{{ $weeklySession->vrExperiment->external_url }}" 
                                           target="_blank" 
                                           class="btn btn-success">
                                            <i class="bi bi-play-fill me-1"></i> Launch VR
                                        </a>
                                    </div>
                                </div>
                            @endif

                            @if($weeklySession->model3ds->count() > 0)
                                <div>
                                    <h6 class="fw-semibold mb-3">3D Models ({{ $weeklySession->model3ds->count() }})</h6>
                                    <div class="row g-3">
                                        @foreach($weeklySession->model3ds as $model3d)
                                            <div class="col-md-6">
                                                <div class="p-3 border rounded h-100">
                                                    <h6 class="fw-semibold mb-2">
                                                        <i class="bi bi-box text-warning me-2"></i>{{ $model3d->name }}
                                                    </h6>
                                                    @if($model3d->subject)
                                                        <span class="badge bg-light text-dark mb-2">{{ $model3d->subject->subject_name }}</span>
                                                    @endif
                                                    <div class="mt-2">
                                                        <a href="{{ route('client.models.3d.show', $model3d) }}" 
                                                           target="_blank" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-eye me-1"></i> View Model
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @else
                            <p class="text-muted mb-0">No resources assigned to this session.</p>
                        @endif
                    </div>
                </div>

                <!-- Assigned Classes -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bi bi-book me-2"></i>Assigned Classes</h6>
                    </div>
                    <div class="card-body">
                        @if($weeklySession->classes->count() > 0)
                            <div class="row g-2">
                                @foreach($weeklySession->classes as $class)
                                    <div class="col-auto">
                                        <span class="badge bg-primary">
                                            {{ $class->name }}
                                            @if(isset($studentCountsByClass[$class->id]))
                                                ({{ $studentCountsByClass[$class->id] }} students)
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0">No classes assigned to this session.</p>
                        @endif
                    </div>
                </div>

                <!-- Assigned Students -->
                @if($students->count() > 0)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-transparent">
                            <h6 class="mb-0"><i class="bi bi-people me-2"></i>Assigned Students ({{ $students->count() }})</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Class</th>
                                            <th>Age</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($students as $student)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $student->name }}</td>
                                                <td>
                                                    <span class="badge bg-primary">{{ $student->schoolClass->name }}</span>
                                                </td>
                                                <td>{{ $student->age }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Session Details -->
                <div class="card border-0 shadow-sm mb-4 sticky-top" style="top: 20px;">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Session Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <span class="text-muted small d-block">Session Date</span>
                            <span class="fw-semibold">
                                <i class="bi bi-calendar-event me-1"></i>
                                {{ $weeklySession->session_date->format('l, F d, Y') }}
                            </span>
                        </div>
                        <div class="mb-3">
                            <span class="text-muted small d-block">Club</span>
                            <span class="badge bg-info">{{ $weeklySession->club->name }}</span>
                        </div>
                        @if($weeklySession->subject)
                        <div class="mb-3">
                            <span class="text-muted small d-block">Subject</span>
                            <span class="badge bg-secondary">{{ $weeklySession->subject->subject_name }}</span>
                        </div>
                        @endif
                        <div class="mb-0">
                            <span class="text-muted small d-block">Status</span>
                            <span class="badge {{ $weeklySession->status_badge }}">{{ ucfirst($weeklySession->status) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Report Actions -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bi bi-file-text me-2"></i>Session Report</h6>
                    </div>
                    <div class="card-body">
                        @if($report)
                            <div class="mb-3">
                                <span class="badge bg-success mb-2">
                                    <i class="bi bi-check-circle me-1"></i>Report Submitted
                                </span>
                                <p class="text-muted small mb-0">
                                    Submitted on {{ $report->submitted_at->format('M d, Y H:i') }}
                                </p>
                                @if($report->pdf_file)
                                    <p class="text-muted small mb-0 mt-2">
                                        <i class="bi bi-file-pdf text-danger me-1"></i>PDF Report Uploaded
                                    </p>
                                @endif
                            </div>
                            <div class="d-grid gap-2">
                                @if($report->pdf_file)
                                    <a href="{{ asset('storage/' . $report->pdf_file) }}" target="_blank" class="btn btn-outline-danger">
                                        <i class="bi bi-file-pdf me-1"></i> View PDF Report
                                    </a>
                                    <a href="{{ route('client.weekly-sessions.report.edit', $weeklySession) }}" class="btn btn-primary">
                                        <i class="bi bi-upload me-1"></i> Replace with Online Form
                                    </a>
                                @else
                                    <a href="{{ route('client.weekly-sessions.report.edit', $weeklySession) }}" class="btn btn-primary">
                                        <i class="bi bi-pencil me-1"></i> Edit Report
                                    </a>
                                @endif
                            </div>
                        @else
                            <p class="text-muted small mb-3">Submit a report after conducting this session. Choose either option:</p>
                            <div class="d-grid gap-2">
                                <a href="{{ route('client.weekly-sessions.report.create', $weeklySession) }}" class="btn btn-success">
                                    <i class="bi bi-file-text me-1"></i> Fill Online Form
                                </a>
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#uploadPdfModal">
                                    <i class="bi bi-file-pdf me-1"></i> Upload PDF Report
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- PDF Upload Modal -->
                @if(!$report)
                <div class="modal fade" id="uploadPdfModal" tabindex="-1" aria-labelledby="uploadPdfModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('client.weekly-sessions.report.upload-pdf', $weeklySession) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title" id="uploadPdfModalLabel">
                                        <i class="bi bi-file-pdf text-danger me-2"></i>Upload PDF Report
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="pdf_file" class="form-label">PDF File <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control @error('pdf_file') is-invalid @enderror" 
                                               id="pdf_file" name="pdf_file" accept=".pdf" required>
                                        @error('pdf_file')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Please upload a PDF file (max size: 10MB)</small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-upload me-1"></i> Upload PDF
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
