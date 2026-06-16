<x-app-layout>
    <div class="col-12">
        <div class="dashboard-content-header mb-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('client.competitions.index') }}">Competitions</a></li>
                    <li class="breadcrumb-item active">{{ $competition->name }}</li>
                </ol>
            </nav>
            <h4 class="mb-1">{{ $competition->name }}</h4>
            <p class="text-muted small mb-0">Track phases, complete open tasks, and follow admin decisions from this hub.</p>
        </div>

        @include('elements.client-validation')

        @php
            $phaseLiveNow = $competition->phases->first(fn ($p) => $p->isOpenNow());
            $phaseModelCurrent = $competition->currentPhase();
            $wizardFocusPhase = $phaseLiveNow ?? $phaseModelCurrent ?? $competition->phases->firstWhere('status', 'upcoming');
            if (! $wizardFocusPhase && $competition->phases->isNotEmpty()) {
                $wizardFocusPhase = $competition->phases->last();
            }
            $hasUpcomingPhase = $competition->phases->contains(fn ($p) => $p->status === 'upcoming');

            // Progression gates: a team can only move to later phases after clearing the previous milestone.
            $registrationApproved = $registration && $registration->status === 'approved';
            $ideaSubmitted = $idea && $idea->status !== 'draft';

            $ideaDecisionStatus = $idea?->shortlist?->status;
            if ($ideaDecisionStatus === null && $idea && $idea->status === 'shortlisted') {
                $ideaDecisionStatus = 'shortlisted';
            }
            $ideaShortlisted = $ideaDecisionStatus === 'shortlisted';

            $prototypeSubmitted = $idea?->prototypeSubmission && $idea->prototypeSubmission->status === 'submitted';
            $prototypeShortlisted = $idea?->prototypeSubmission?->prototypeShortlist?->status === 'shortlisted';

            $modificationSubmitted = $idea?->prototypeModification && $idea->prototypeModification->status === 'submitted';
        @endphp

        <div class="card border shadow-none mb-4 phase-wizard-card">
            <div class="card-body py-3 px-3 px-md-4">
                <div class="d-flex flex-column flex-md-row flex-md-wrap align-items-md-center justify-content-md-between gap-3 mb-3">
                    <div>
                      
                        @if($phaseLiveNow)
                            <p class="mb-0 fs-5 fw-semibold d-flex align-items-center flex-wrap gap-2">
                                <span>{{ $phaseLiveNow->name }}</span>
                                <span class="badge bg-success">Active phase</span>
                            </p>
                            <p class="text-muted small mb-0 mt-1">This phase is open for actions now.</p>
                        @elseif($phaseModelCurrent)
                            <p class="mb-0 fs-5 fw-semibold d-flex align-items-center flex-wrap gap-2">
                                <span>{{ $phaseModelCurrent->name }}</span>
                                <span class="badge bg-primary rounded-pill">{{ ucfirst(str_replace('_', ' ', $phaseModelCurrent->status)) }}</span>
                            </p>
                            <p class="text-muted small mb-0 mt-1">Marked as the current phase (check dates below if actions are not yet available).</p>
                        @elseif($wizardFocusPhase)
                            <p class="mb-0 fs-5 fw-semibold d-flex align-items-center flex-wrap gap-2">
                                <span>{{ $wizardFocusPhase->name }}</span>
                                @if($hasUpcomingPhase)
                                    <span class="badge bg-secondary rounded-pill">Next up</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill">Timeline</span>
                                @endif
                            </p>
                            <p class="text-muted small mb-0 mt-1">
                                @if($hasUpcomingPhase)
                                    No phase is live yet — this is the next stage in the journey.
                                @else
                                    No phase is open for actions right now; use the steps below to see where things stand.
                                @endif
                            </p>
                        @else
                            <p class="mb-0 text-muted">No phases configured for this competition.</p>
                        @endif
                    </div>
                </div>

                @if($competition->phases->isNotEmpty())
                    <div class="phase-wizard-track position-relative pt-2">
                        <div class="d-flex align-items-stretch justify-content-between gap-0 flex-nowrap overflow-auto pb-1" style="scrollbar-width: thin;">
                            @foreach($competition->phases as $phase)
                                @php
                                    $isLive = $phase->isOpenNow();
                                    $isClosed = $phase->status === 'closed' || ($phase->closes_at && now()->gt($phase->closes_at));
                                    $isFocus = $wizardFocusPhase && (int) $wizardFocusPhase->id === (int) $phase->id;
                                @endphp
                                <div class="phase-wizard-step flex-grow-1 text-center px-1" style="min-width: 4.5rem; max-width: 9rem;">
                                    <div class="position-relative mx-auto mb-2" style="width: 2.5rem; height: 2.5rem;">
                                 
                                        <div class="phase-wizard-marker d-flex align-items-center justify-content-center fw-bold border border-2 mx-auto
                                            @if($isLive) bg-success text-white border-success
                                            @elseif($isClosed) bg-light text-muted border-secondary
                                            @elseif($isFocus) bg-primary text-white border-primary
                                            @else bg-white text-muted border-secondary @endif">
                                            @if($isClosed)
                                                <i class="bi bi-check-lg"></i>
                                            @else
                                                {{ $phase->sequence }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="small fw-semibold lh-sm text-truncate" title="{{ $phase->name }}">{{ $phase->name }}</div>
                                    <div class="text-muted" style="font-size: 0.65rem;">
                                        @if($phase->opens_at || $phase->closes_at)
                                            @if($phase->opens_at)
                                                <span>{{ $phase->opens_at->format('j M') }}</span>
                                            @endif
                                            @if($phase->opens_at && $phase->closes_at)
                                                <span>–</span>
                                            @endif
                                            @if($phase->closes_at)
                                                <span>{{ $phase->closes_at->format('j M') }}</span>
                                            @endif
                                        @else
                                            {{ ucfirst(str_replace('_', ' ', $phase->status)) }}
                                        @endif
                                    </div>
                                </div>
                                @if(!$loop->last)
                                    <div class="phase-wizard-connector flex-shrink-0 align-self-center border-top border-2 border-secondary mx-n1 opacity-25" style="width: 0.75rem; margin-top: 1.25rem;"></div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

@if($competition->isPhaseOpen('registration'))
       @include('client.back.competitions.phases.registration')
@endif

@if($competition->isPhaseOpen('idea_submission') && $registrationApproved)
    @include('client.back.competitions.phases.idea_submission')
@endif

@if($competition->isPhaseOpen('idea_shortlisting') && $registrationApproved && $ideaSubmitted)
    @include('client.back.competitions.phases.idea_shortlisting')
@endif

@if($competition->isPhaseOpen('prototype_development') && $registrationApproved && $ideaSubmitted && $ideaShortlisted)
    @include('client.back.competitions.phases.prototype_development')
@endif

@if($competition->isPhaseOpen('prototype_shortlisting') && $registrationApproved && $ideaSubmitted && $ideaShortlisted && $prototypeSubmitted)
    @include('client.back.competitions.phases.prototype_shortlisting')
@endif

@if($competition->isPhaseOpen('mentor_onboarding') && $registrationApproved && $ideaSubmitted && $ideaShortlisted && $prototypeSubmitted && $prototypeShortlisted)
    @include('client.back.competitions.phases.mentor_onboarding')
@endif

@if($competition->isPhaseOpen('prototype_modification') && $registrationApproved && $ideaSubmitted && $ideaShortlisted && $prototypeSubmitted && $prototypeShortlisted)
    @include('client.back.competitions.phases.prototype_modification')
@endif

@if($competition->isPhaseOpen('pitch_winner') && $registrationApproved && $ideaSubmitted && $ideaShortlisted && $prototypeSubmitted && $prototypeShortlisted && $modificationSubmitted)
    @include('client.back.competitions.phases.pitch_winner')
@endif

    </div>
</x-app-layout>

