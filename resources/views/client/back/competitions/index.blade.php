<x-app-layout>
    <div class="col-12">
        <div class="dashboard-content-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h2 class="title mb-1">Competitions</h2>
            </div>
      </div>

        <section class="row">
            <div class="col-12">
             @include('elements.client-validation')

                @if($competitions->isEmpty())
                    <div class="card border-0 shadow-none">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-trophy display-4 text-muted mb-3"></i>
                            <h5 class="fw-semibold mb-2">No competitions available</h5>
                            <p class="text-muted mb-3">
                                There are no active competitions for your clubs at the moment. Check back later!
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
                            <div class="col-12 col-lg-12">
                                <div class="card h-100 shadow-none {{ ($highlightCompetitionUlid ?? null) === $competition->ulid ? 'border border-primary border-2' : 'border-0' }}">
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
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <span class="badge bg-light text-dark border">{{ $competition->club->name }}</span>
                                                    @if($registration)
                                                        @php($regBadge = $registration->status === 'approved' ? 'success' : ($registration->status === 'rejected' ? 'danger' : ($registration->status === 'draft' ? 'secondary' : 'warning')))
                                                        <span class="badge bg-{{ $regBadge }}">
                                                            {{ ucfirst($registration->status) }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">Not registered</span>
                                                    @endif
                                                </div>

                                                @if(($highlightCompetitionUlid ?? null) === $competition->ulid)
                                                    <div class="small text-primary fw-semibold mb-1">Continue here — next steps for this competition</div>
                                                @endif

                                                <h5 class="card-title fw-semibold mt-3">{{ $competition->name }}</h5>
                                                <p class="card-text flex-grow-1">
                                                    {{ Str::limit(Str::squish(strip_tags((string) ($competition->description ?? ''))), 300) }}
                                                </p>

                                                @if(! $teacherClub)
                                                    <div class="alert alert-warning small py-2 mb-2">
                                                        Join <strong>{{ $competition->club->name }}</strong> to continue with this competition.
                                                        <a href="{{ route('client.clubs.join.form', $competition->club) }}" class="alert-link">Join club</a>
                                                    </div>
                                                @elseif($teacherClub->status === 'pending')
                                                    <div class="alert alert-warning small py-2 mb-2">
                                                        <strong>Club membership:</strong> waiting for approval from your school leader or admin. You cannot submit competition registration until approved.
                                                        <a href="{{ route('client.clubs.index') }}" class="alert-link d-block mt-1">View club status</a>
                                                    </div>
                                                @elseif($teacherClub->status === 'rejected')
                                                    <div class="alert alert-danger small py-2 mb-2">
                                                        Your request to join this club was not approved. Contact support or your school leader if this is a mistake.
                                                    </div>
                                                @elseif($teacherClub->status === 'approved')
                                                    @if(! $registration)
                                                        @if(! $regPhaseOpen)
                                                            <div class="alert alert-info small py-2 mb-2">
                                                                <strong>Registration phase is closed.</strong> When it opens, upload participants, confirm school details, and submit for admin approval.
                                                            </div>
                                                        @else
                                                            <div class="alert alert-danger small py-2 mb-2">
                                                                <strong>Next step:</strong> upload participants (CSV), confirm school details, and submit for admin approval.
                                                                <a href="{{ route('client.competitions.register', $competition) }}" class="badge bg-danger text-white">
                                                                    Complete registration
                                                                </a>
                                                            </div>
                                                          
                                                        @endif
                                                    @elseif($registration->status === 'draft')
                                                        <div class="alert alert-info small py-2 mb-2">
                                                            <strong>In progress:</strong> finish your participant list and school details, then submit for admin approval.
                                                        </div>
                                                        <a href="{{ route('client.competitions.register', $competition) }}" class="btn btn-sm btn-primary text-white mb-2">
                                                            Continue registration
                                                        </a>
                                                    @elseif($registration->status === 'pending')
                                                        <div class="alert alert-warning small py-2 mb-2">
                                                            Registration submitted — <strong>awaiting admin approval</strong>. You will be notified when it is reviewed.
                                                        </div>
                                                    @elseif($registration->status === 'rejected')
                                                        <div class="alert alert-danger small py-2 mb-2">
                                                            Your competition registration was not approved. Check messages from admin or contact support.
                                                        </div>
                                                        @if($regPhaseOpen)
                                                            <a href="{{ route('client.competitions.register', $competition) }}" class="btn btn-sm btn-primary text-white mb-2">Update and resubmit</a>
                                                        @endif
                                                    @endif
                                                @endif

                                                <div class="my-3">
                                                    <div class="d-flex gap-3 text-muted small flex-wrap">
                                                        @if($competition->competition_start)
                                                            <span><i class="bi bi-calendar-event"></i> {{ $competition->competition_start->format('d M Y') }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="mt-1">
                                                        <i class="bi bi-signpost"></i>
                                                       Current phase: <strong>{{ strtoupper($currentPhase?->name ?? 'No active phase') }}</strong>
                                                    </div>
                                                </div>

                                                <a href="{{ route('client.competitions.show', $competition) }}" class="btn btn-primary text-white btn-sm">
                                                    View details <i class="bi bi-arrow-right"></i>
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
        </section>
    </div>
</x-app-layout>

