<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">{{ $weeklySession->title }}</h5>
                    <p class="text-muted mb-0">Weekly Session Details</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.weekly-sessions.edit', $weeklySession) }}" class="btn btn-primary">
                        <i class="ti ti-edit"></i> Edit
                    </a>
                    <a href="{{ route('admin.weekly-sessions.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left"></i> Back to List
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
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="ti ti-info-circle me-2"></i>Session Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Description / Objectives</label>
                                <div class="text-muted">
                                    {{ $weeklySession->description ?: 'No description provided.' }}
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Session Date</label>
                                    <div>
                                        <i class="ti ti-calendar-event me-2"></i>
                                        {{ $weeklySession->session_date->format('l, F d, Y') }}
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Status</label>
                                    <div>
                                        <span class="badge {{ $weeklySession->status_badge }}">
                                            {{ ucfirst($weeklySession->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="ti ti-users me-2"></i>Club & Classes</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Club</label>
                                <div>
                                    <span class="badge bg-info">{{ $weeklySession->club->name }}</span>
                                </div>
                            </div>

                            @if($weeklySession->subject)
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Subject</label>
                                <div>
                                    <span class="badge bg-secondary">{{ $weeklySession->subject->subject_name }}</span>
                                </div>
                            </div>
                            @endif

                            <div class="mb-0">
                                <label class="form-label fw-semibold">Classes</label>
                                <div>
                                    @if($weeklySession->classes->count() > 0)
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($weeklySession->classes as $class)
                                                <span class="badge bg-primary">{{ $class->name }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">No classes assigned</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="ti ti-package me-2"></i>Resources</h6>
                        </div>
                        <div class="card-body">
                            @if($weeklySession->vr_experiment_id)
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">VR Experiment</label>
                                    <div>
                                        <span class="badge bg-success">
                                            <i class="ti ti-vr"></i> {{ $weeklySession->vrExperiment->name }}
                                        </span>
                                        @if($weeklySession->vrExperiment->schoolClass)
                                            <div class="small text-muted mt-1">
                                                Class: {{ $weeklySession->vrExperiment->schoolClass->name }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="mb-0">
                                <label class="form-label fw-semibold">3D Models</label>
                                <div>
                                    @if($weeklySession->model3ds->count() > 0)
                                        <div class="d-flex flex-column gap-2">
                                            @foreach($weeklySession->model3ds as $model3d)
                                                <div>
                                                    <span class="badge bg-warning">
                                                        <i class="ti ti-cube"></i> {{ $model3d->name }}
                                                    </span>
                                                    @if($model3d->subject)
                                                        <div class="small text-muted mt-1">
                                                            Subject: {{ $model3d->subject->subject_name }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">No 3D models assigned</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="ti ti-info-circle me-2"></i>Metadata</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <small class="text-muted">Created by:</small>
                                <div>{{ $weeklySession->creator->name ?? 'System' }}</div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Created on:</small>
                                <div>{{ $weeklySession->created_at->format('M d, Y H:i') }}</div>
                            </div>
                            <div class="mb-0">
                                <small class="text-muted">Last updated:</small>
                                <div>{{ $weeklySession->updated_at->format('M d, Y H:i') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <form action="{{ route('admin.weekly-sessions.destroy', $weeklySession) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Are you sure you want to delete this session? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="ti ti-trash"></i> Delete Session
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
