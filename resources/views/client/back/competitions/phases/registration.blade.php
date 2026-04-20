<div class="row">
            <div class="col-lg-12">
                <div class="card border-0 shadow-none mb-4">
                    <div class="card-header bg-transparent d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <h4 class="mb-0 text-uppercase">Your registration submission</h4>
                        @if($registration && $registration->submitted_at)
                            <span class="text-muted small">Submitted {{ $registration->submitted_at->timezone(config('app.timezone'))->format('j M Y, H:i') }}</span>
                        @endif
                    </div>
                    <div class="card-body">
                        @if(!$teacherClub)
                            <div class="alert alert-warning mb-0">You are not yet an approved teacher in this club.</div>
                        @elseif(!$registration)
                            <p class="text-muted">No registration snapshot submitted yet.</p>
                            @if($competition->isPhaseOpen('registration'))
                                <a href="{{ route('client.competitions.register', $competition) }}" class="badge bg-primary border-0">Complete registration (participants)</a>
                            @endif
                        @else
                            @php
                                $regShowBadge = match ($registration->status) {
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'draft' => 'secondary',
                                    default => 'warning',
                                };
                            @endphp
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                <span class="badge bg-{{ $regShowBadge }}">{{ ucfirst($registration->status) }}</span>
                                @if($registration->status === 'approved' && $registration->approved_at)
                                    <span class="text-muted small">Approved {{ $registration->approved_at->timezone(config('app.timezone'))->format('j M Y, H:i') }}</span>
                                @endif
                            </div>

                            @if($registration->status === 'rejected' && $registration->rejection_reason)
                                <div class="alert alert-danger small mb-3">
                                    <strong>Reason:</strong> {{ $registration->rejection_reason }}
                                </div>
                            @endif

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-muted small mb-2">Teacher contact (as submitted)</h6>
                                    <dl class="row small mb-0">
                                        <dt class="col-sm-4 text-muted fw-normal">Name</dt>
                                        <dd class="col-sm-8 mb-1">{{ $registration->teacher_name ?: '—' }}</dd>
                                        <dt class="col-sm-4 text-muted fw-normal">Email</dt>
                                        <dd class="col-sm-8 mb-1">{{ $registration->teacher_email ?: '—' }}</dd>
                                        <dt class="col-sm-4 text-muted fw-normal">Phone</dt>
                                        <dd class="col-sm-8 mb-1">{{ $registration->teacher_phone ?: '—' }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    @php
                                        $regRegionName = $registration->region?->name;
                                        $schoolRegionName = $registration->school?->regionModel?->name;
                                        $schoolRegionLegacy = $registration->school?->region;
                                        $regionDisplay = $regRegionName ?: $schoolRegionName ?: $schoolRegionLegacy;
                                    @endphp
                                  
                                    @if($registration->school)
                                        <h6 class="text-uppercase text-muted small mb-2 mt-3">School profile (as Submitted)</h6>
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
                                    @endif
                                </div>
                            </div>

                            <hr class="my-3 opacity-25">

                            <h5 class="text-uppercase mb-2">Eligibility snapshot</h5>
                            <p class="mb-2">
                                <strong>{{ $registration->student_count_snapshot ?? '—' }}</strong> students in pool
                                @if($registration->girls_count_snapshot !== null)
                                    (<strong>{{ $registration->girls_count_snapshot }}</strong> girls)
                                @endif
                                @if($registration->girls_ratio_snapshot !== null)
                                    — <strong>{{ $registration->girls_ratio_snapshot }}%</strong> girls
                                @endif
                            </p>
                            <p class="mb-2 mb-0">
                                Pool met rules at submission:
                                @if($registration->eligibility_met === true)
                                    <span class="text-success">Yes</span>
                                @elseif($registration->eligibility_met === false)
                                    <span class="text-danger">No</span>
                                @else
                                    —
                                @endif
                            </p>

                            <h6 class="text-uppercase mb-2">Students in your pool</h6>
                            @if($poolStudents->isNotEmpty())
                                <div class="table-responsive border rounded">
                                    <table class="table table-sm table-bordered table-light-soft align-middle mb-0 small">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 50px;">#</th>
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
                                <p class="text-muted small mb-0">No students in your pool.</p>
                            @endif

                            @if($registration->status === 'pending')
                                <p class="text-muted small mb-0 mt-3">Your participant list and school details are with the admin for review. You will be able to continue with the next steps after approval.</p>
                            @elseif($registration->status === 'rejected' && $competition->isPhaseOpen('registration'))
                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <a href="{{ route('client.competitions.register', $competition) }}" class="btn btn-sm btn-primary text-white">Update and resubmit</a>
                                </div>
                            @elseif($registration->status === 'draft' && $competition->isPhaseOpen('registration'))
                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <a href="{{ route('client.competitions.register', $competition) }}" class="btn btn-sm btn-primary text-white">Continue registration</a>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>