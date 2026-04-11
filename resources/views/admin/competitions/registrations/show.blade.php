<x-admin-layout>
    @php
        $regRegionName = $registration->region?->name;
        $schoolRegionName = $registration->school?->regionModel?->name;
        $schoolRegionLegacy = $registration->school?->region;
        $regionDisplay = $regRegionName ?: $schoolRegionName ?: $schoolRegionLegacy;
        $repNeed = max(1, (int) ($competition->representative_count ?? 10));
        $repCount = $registration->representativeStudents->count();
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <nav aria-label="breadcrumb" class="mb-1">
                        <ol class="breadcrumb mb-0 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.competitions.show', $competition) }}">{{ $competition->name }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.competitions.registrations.index', $competition) }}">Registrations</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $registration->school_name }}</li>
                        </ol>
                    </nav>
                    <h5 class="mb-0">Registration — {{ $registration->school_name }}</h5>
                    <p class="text-muted mb-0 small">Teacher snapshot, school details, and student pool for this competition.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.competitions.registrations.index', $competition) }}" class="btn btn-outline-secondary">Back to list</a>
                    @if($registration->status === 'pending')
                        <form method="POST" action="{{ route('admin.competitions.registrations.approve', [$competition, $registration]) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success">Approve</button>
                        </form>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectRegistrationModal">Reject</button>
                    @endif
                </div>
            </div>

            

            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header"><h6 class="mb-0">Status</h6></div>
                        <div class="card-body">
                            @php
                                $regBadge = match ($registration->status) {
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'draft' => 'secondary',
                                    default => 'warning',
                                };
                            @endphp
                            <p class="mb-2">
                                <span class="badge bg-{{ $regBadge }}">{{ ucfirst($registration->status) }}</span>
                            </p>
                            @if($registration->submitted_at)
                                <p class="small mb-1"><strong>Submitted:</strong> {{ $registration->submitted_at->timezone(config('app.timezone'))->format('j M Y, H:i') }}</p>
                            @endif
                            @if($registration->approved_at)
                                <p class="small mb-1"><strong>Approved:</strong> {{ $registration->approved_at->timezone(config('app.timezone'))->format('j M Y, H:i') }}</p>
                            @endif
                            @if($registration->approver)
                                <p class="small mb-1"><strong>Decision by:</strong> {{ $registration->approver->name ?? $registration->approver->email }}</p>
                            @endif
                            @if($registration->status === 'rejected' && $registration->rejection_reason)
                                <div class="alert alert-danger small mb-0 mt-2">
                                    <strong>Reason:</strong> {{ $registration->rejection_reason }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header"><h6 class="mb-0">Teacher contact</h6></div>
                        <div class="card-body">
                            <dl class="row small mb-0">
                                <dt class="col-sm-4 text-muted fw-normal">Name</dt>
                                <dd class="col-sm-8 mb-1">{{ $registration->teacher_name ?: '—' }}</dd>
                                <dt class="col-sm-4 text-muted fw-normal">Email</dt>
                                <dd class="col-sm-8 mb-1">{{ $registration->teacher_email ?: '—' }}</dd>
                                <dt class="col-sm-4 text-muted fw-normal">Phone</dt>
                                <dd class="col-sm-8 mb-1">{{ $registration->teacher_phone ?: '—' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header"><h6 class="mb-0">Eligibility snapshot</h6></div>
                        <div class="card-body">
                            <p class="small mb-1"><strong>{{ $registration->student_count_snapshot ?? '—' }}</strong> students</p>
                            <p class="small mb-1"><strong>{{ $registration->girls_count_snapshot ?? '—' }}</strong> girls</p>
                            <p class="small mb-1"><strong>{{ $registration->girls_ratio_snapshot ?? '—' }}%</strong> girls</p>
                            <p class="small mb-0">Pool met rules:
                                @if($registration->eligibility_met === true)
                                    <span class="text-success">Yes</span>
                                @elseif($registration->eligibility_met === false)
                                    <span class="text-danger">No</span>
                                @else
                                    —
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0">School &amp; location</h6></div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-muted small mb-2">As submitted</h6>
                                    <dl class="row small mb-0">
                                        <dt class="col-sm-4 text-muted fw-normal">Region</dt>
                                        <dd class="col-sm-8 mb-1">{{ $regionDisplay ?: '—' }}</dd>
                                        <dt class="col-sm-4 text-muted fw-normal">School</dt>
                                        <dd class="col-sm-8 mb-1">{{ $registration->school_name ?: '—' }}</dd>
                                        <dt class="col-sm-4 text-muted fw-normal">District</dt>
                                        <dd class="col-sm-8 mb-1">{{ $registration->school_district ?: '—' }}</dd>
                                        <dt class="col-sm-4 text-muted fw-normal">Ward</dt>
                                        <dd class="col-sm-8 mb-1">{{ $registration->school_ward ?: '—' }}</dd>
                                    </dl>
                                </div>
                                @if($registration->school)
                                    <div class="col-md-6">
                                        <h6 class="text-uppercase text-muted small mb-2">School profile (on file)</h6>
                                        <dl class="row small mb-0">
                                            <dt class="col-sm-4 text-muted fw-normal">Name</dt>
                                            <dd class="col-sm-8 mb-1">{{ $registration->school->name ?: '—' }}</dd>
                                            <dt class="col-sm-4 text-muted fw-normal">Address</dt>
                                            <dd class="col-sm-8 mb-1 text-break">{{ $registration->school->address ?: '—' }}</dd>
                                            <dt class="col-sm-4 text-muted fw-normal">Phone</dt>
                                            <dd class="col-sm-8 mb-1">{{ $registration->school->phone ?: '—' }}</dd>
                                            <dt class="col-sm-4 text-muted fw-normal">Email</dt>
                                            <dd class="col-sm-8 mb-1">{{ $registration->school->email ?: '—' }}</dd>
                                            <dt class="col-sm-4 text-muted fw-normal">District</dt>
                                            <dd class="col-sm-8 mb-1">{{ $registration->school->district ?: '—' }}</dd>
                                            <dt class="col-sm-4 text-muted fw-normal">Ward</dt>
                                            <dd class="col-sm-8 mb-1">{{ $registration->school->ward ?? '—' }}</dd>
                                            <dt class="col-sm-4 text-muted fw-normal">Region</dt>
                                            <dd class="col-sm-8 mb-1">{{ $schoolRegionName ?: $schoolRegionLegacy ?: '—' }}</dd>
                                        </dl>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <h6 class="mb-0">Student pool (uploaded)</h6>
                            <span class="badge bg-secondary">{{ $poolStudents->count() }} {{ $poolStudents->count() === 1 ? 'student' : 'students' }}</span>
                        </div>
                        <div class="card-body">
                            @if($poolStudents->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-sm align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 3rem;">#</th>
                                                <th>Name</th>
                                                <th>Gender</th>
                                                <th>Class</th>
                                                <th>Age</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($poolStudents as $index => $student)
                                                <tr>
                                                    <td class="text-muted small">{{ $index + 1 }}</td>
                                                    <td>{{ $student->name ?? '—' }}</td>
                                                    <td>{{ ucfirst((string) $student->gender) }}</td>
                                                    <td>{{ $student->schoolClass->name ?? '—' }}</td>
                                                    <td>{{ $student->age ?? $student->date_of_birth?->diffInYears(now()) ?? '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted mb-0">No students in this competition pool.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header"><h6 class="mb-0">Representatives on registration</h6></div>
                        <div class="card-body">
                            <p class="small mb-2">
                                <strong>{{ $repCount }}</strong> / {{ $repNeed }} recorded
                                @if($repCount > 0 && $repCount !== $repNeed)
                                    <span class="text-warning">(expected {{ $repNeed }} for approval when representatives are used)</span>
                                @endif
                            </p>
                            @if($registration->representativeStudents->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Name</th>
                                                <th>Class</th>
                                                <th>Role</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($registration->representativeStudents->sortBy(fn ($rs) => $rs->student?->name) as $rs)
                                                <tr>
                                                    <td>{{ $rs->student->name ?? '—' }}</td>
                                                    <td>{{ $rs->student->schoolClass->name ?? '—' }}</td>
                                                    <td>{{ $rs->role ?: '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted small mb-0">No representative rows stored for this registration.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($registration->status === 'pending')
        <div class="modal fade" id="rejectRegistrationModal" tabindex="-1" aria-labelledby="rejectRegistrationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectRegistrationModalLabel">Reject registration</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('admin.competitions.registrations.reject', [$competition, $registration]) }}">
                        @csrf
                        <div class="modal-body">
                            <label class="form-label" for="rejection_reason">Reason</label>
                            <textarea class="form-control" name="rejection_reason" id="rejection_reason" rows="4" required></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Reject</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</x-admin-layout>
