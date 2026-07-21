@php
    $shortlist = $idea?->shortlist;
    $repTotal = $registration?->representativeStudents->count() ?? 0;
    $decisionStatus = $shortlist?->status;
    if ($decisionStatus === null && $idea && $idea->status === 'shortlisted') {
        $decisionStatus = 'shortlisted';
    }
@endphp

<div id="idea-shortlisting" class="row">
    <div class="col-12">
        <div class="card border shadow-none mb-4 competition-phase-card">
            <div class="card-header bg-light-soft">
                <h5 class="mb-0 fw-semibold">Idea shortlisting</h5>
                <p class="text-muted small mb-0 mt-1">Your submission is with the judges. There is nothing to submit in this stage.</p>
            </div>
            <div class="card-body">
                @if(!$teacherClub)
                    <div class="alert alert-warning mb-0">You must be an approved teacher in this club to view this competition.</div>
                @elseif(!$registration)
                    <p class="text-muted mb-0">No registration found for your school in this competition.</p>
                @elseif(! $idea || $idea->status === 'draft')
                    <div class="alert alert-info mb-0">
                        No submitted idea was found. If the idea submission phase is still open, complete your idea there first.
                    </div>
                @else
                    @php
                        $decisionBadge = match ($decisionStatus) {
                            'shortlisted' => ['success', 'Shortlisted'],
                            'waitlist' => ['warning', 'Waitlist'],
                            'rejected' => ['danger', 'Not shortlisted'],
                            default => ['secondary', 'Awaiting decision'],
                        };
                    @endphp

                    <div class="d-flex flex-wrap align-items-center gap-2 mb-4 pb-3 border-bottom border-opacity-25">
                        <span class="badge bg-{{ $decisionBadge[0] }}">{{ $decisionBadge[1] }}</span>
                        @if($decisionStatus === null)
                            <span class="text-muted small">Judges are reviewing ideas. You will be notified when a decision is recorded.</span>
                        @elseif($decisionStatus === 'shortlisted' && $shortlist?->rank)
                            <span class="text-muted small">Regional rank: <strong>{{ $shortlist->rank }}</strong></span>
                        @endif
                    </div>

                    <div class="row g-4">
                        <div class="col-lg-7">
                            <h6 class="text-muted small fw-semibold mb-3">Your idea (read only)</h6>
                            <dl class="row small mb-0">
                                <dt class="col-sm-4 text-muted fw-normal">Problem title</dt>
                                <dd class="col-sm-8 mb-2">{{ $idea->problem_title ?: '—' }}</dd>
                                <dt class="col-sm-4 text-muted fw-normal">STEM discipline</dt>
                                <dd class="col-sm-8 mb-2">{{ $idea->stem_category ?: '—' }}</dd>
                                <dt class="col-sm-4 text-muted fw-normal">Community region</dt>
                                <dd class="col-sm-8 mb-2">{{ $idea->communityRegion?->name ?? '—' }}</dd>
                                <dt class="col-sm-4 text-muted fw-normal">Community district</dt>
                                <dd class="col-sm-8 mb-2">{{ $idea->communityDistrict?->name ?? ($idea->community_location ?: '—') }}</dd>
                            </dl>
                            <div class="mt-3">
                                <p class="text-muted small fw-semibold mb-1">Problem statement</p>
                                <div class="small text-body-secondary border rounded p-3 bg-light bg-opacity-50">{!! nl2br(e($idea->problem_statement ?? '')) !!}</div>
                            </div>
                            <div class="mt-3">
                                <p class="text-muted small fw-semibold mb-1">Proposed solution</p>
                                <div class="small text-body-secondary border rounded p-3 bg-light bg-opacity-50">{!! nl2br(e($idea->proposed_solution ?? '')) !!}</div>
                            </div>
                            @if(filled($idea->supporting_notes))
                                <div class="mt-3">
                                    <p class="text-muted small fw-semibold mb-1">Supporting notes</p>
                                    <div class="small text-body-secondary border rounded p-3 bg-light bg-opacity-50">{!! nl2br(e($idea->supporting_notes)) !!}</div>
                                </div>
                            @endif
                            @if($idea->submitted_at)
                                <p class="text-muted small mt-3 mb-0">Submitted {{ $idea->submitted_at->timezone(config('app.timezone'))->format('j M Y, H:i') }}</p>
                            @endif
                        </div>
                        <div class="col-lg-5">
                            <h6 class="text-muted small fw-semibold mb-3">Student representatives</h6>
                            <p class="mb-2">
                                <span class="fs-5 fw-semibold">{{ $repTotal }}</span>
                                <span class="text-muted small">student{{ $repTotal === 1 ? '' : 's' }} selected for this idea</span>
                                
                            </p>
                            @if($registration->representativeStudents->isNotEmpty())
                                <ul class="list-group list-group-flush border rounded small">
                                    @foreach($registration->representativeStudents as $rs)
                                        <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                            <span>{{ $rs->student?->name ?? '—' }}</span>
                                            <span class="text-muted">{{ ucfirst((string) ($rs->student?->gender ?? '')) }}</span>
                                            <span class="text-muted">{{ $rs->role ?: '—' }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted small mb-0">No representatives are stored for this registration.</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
