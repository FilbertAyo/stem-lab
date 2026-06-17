<x-app-layout>
    <div class="col-lg-9">
        <div class="dashboard-content-header mb-3">
            <h4 class="mb-1">Competitions</h4>
       </div>

        @include('elements.client-validation')

        @if($competitions->isEmpty())
            <div class="card border shadow-none">
                <div class="card-body text-center py-5">
                    <i class="bi bi-trophy display-4 text-muted mb-3"></i>
                    <h5 class="fw-semibold mb-2">No competitions available</h5>
                    <p class="text-muted mb-3">
                        There are no active competitions for your clubs at the moment. Check back later.
                    </p>
                    <a href="{{ route('dashboard') }}" class="btn btn-primary text-white btn-sm">
                        Return to dashboard
                    </a>
                </div>
            </div>
        @else
            <div class="row g-4">
                @foreach($competitions as $competition)
                    @php($registration = $registrations->get($competition->id))
                    @php($currentPhase = $competition->currentPhase())
                    @php($teacherClub = $teacherClubByClubId->get($competition->club_id))
                    @php($regPhaseOpen = $competition->isPhaseOpen('registration'))
                    <div class="col-12">
                        <div class="card h-100 border shadow-none {{ ($highlightCompetitionUlid ?? null) === $competition->ulid ? 'border-primary border-2' : '' }}">
                            <div class="row g-0 h-100">
                                <div class="col-md-4">
                                    @if($competition->cover_image)
                                        <img src="{{ Storage::url($competition->cover_image) }}"
                                             class="img-fluid rounded-start h-100"
                                             alt="{{ $competition->name }}"
                                             style="object-fit: cover; min-height: 200px;">
                                    @else
                                        <div class="bg-light rounded-start d-flex align-items-center justify-content-center h-100" style="min-height: 200px;">
                                            <i class="bi bi-trophy display-4 text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body d-flex flex-column h-100">
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                            <span class="badge bg-light text-dark border">{{ $competition->club->name }}</span>
                                            @if($registration)
                                                @php($regBadge = $registration->status === 'approved' ? 'success' : ($registration->status === 'rejected' ? 'danger' : ($registration->status === 'draft' ? 'secondary' : 'warning')))
                                                <span class="badge bg-{{ $regBadge }}">{{ ucfirst($registration->status) }}</span>
                                            @else
                                                <span class="badge bg-secondary">Not registered</span>
                                            @endif
                                        </div>

                                        @if(($highlightCompetitionUlid ?? null) === $competition->ulid)
                                            <p class="small text-primary fw-semibold mb-2">Continue here — next steps for this competition</p>
                                        @endif

                                        <h5 class="card-title fw-semibold">{{ $competition->name }}</h5>
                                        <p class="card-text text-muted small flex-grow-1 mb-3">
                                            {{ Str::limit(Str::squish(strip_tags((string) ($competition->description ?? ''))), 280) }}
                                        </p>

                                        @if(! $teacherClub)
                                            <div class="alert alert-warning small py-2 mb-3">
                                                Join <strong>{{ $competition->club->name }}</strong> to continue.
                                                <a href="{{ route('client.clubs.join.form', $competition->club) }}" class="alert-link">Join club</a>
                                            </div>
                                        @elseif($teacherClub->status === 'pending')
                                            <div class="alert alert-warning small py-2 mb-3">
                                                Club membership is pending approval.
                                                <a href="{{ route('client.clubs.index') }}" class="alert-link">View club status</a>
                                            </div>
                                        @elseif($teacherClub->status === 'rejected')
                                            <div class="alert alert-danger small py-2 mb-3">
                                                Your club request was not approved. Contact your school leader if this is a mistake.
                                            </div>
                                        @elseif($teacherClub->status === 'approved')
                                            @if($registration && $registration->status === 'draft')
                                                <div class="alert alert-info small py-2 mb-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                    <span>Finish your participant list and school details, then submit for approval.</span>
                                                    <a href="{{ route('client.competitions.register', $competition) }}" class="btn btn-sm btn-primary text-white">Complete registration</a>
                                                </div>
                                            @elseif(! $registration && $competition->allowsTeacherRegistration(null))
                                                <div class="alert alert-info small py-2 mb-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                    <span>Register your team for admin review.</span>
                                                    <a href="{{ route('client.competitions.register', $competition) }}" class="btn btn-sm btn-primary text-white">Register now</a>
                                                </div>
                                            @elseif($registration && $registration->status === 'pending')
                                                <div class="alert alert-warning small py-2 mb-3">
                                                    Registration submitted — awaiting admin approval.
                                                </div>
                                            @elseif($registration && $registration->status === 'rejected')
                                                <div class="alert alert-danger small py-2 mb-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                    <span>Registration was not approved.</span>
                                                    @if($competition->allowsTeacherRegistration($registration))
                                                        <a href="{{ route('client.competitions.register', $competition) }}" class="btn btn-sm btn-warning text-dark">Update and resubmit</a>
                                                    @endif
                                                </div>
                                            @elseif(! $registration && ! $regPhaseOpen)
                                                <div class="alert alert-info small py-2 mb-3">
                                                    Registration phase is closed.
                                                </div>
                                            @endif
                                        @endif

                                        <div class="d-flex flex-wrap gap-3 text-muted small mb-3">
                                            @if($competition->competition_start)
                                                <span><i class="bi bi-calendar-event me-1"></i>{{ $competition->competition_start->format('d M Y') }}</span>
                                            @endif
                                            <span><i class="bi bi-signpost me-1"></i>Phase: <strong>{{ $currentPhase?->name ?? 'No active phase' }}</strong></span>
                                        </div>

                                        <a href="{{ route('client.competitions.show', $competition) }}" class="btn btn-primary text-white btn-sm align-self-start">
                                            View details <i class="bi bi-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
