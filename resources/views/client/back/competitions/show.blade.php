<x-app-layout>
    <div class="col-lg-9">
        <div class="dashboard-content-header mb-3">
            <h4 class="mb-1">{{ $competition->name }}</h4>
        </div>

        @include('elements.client-validation')

        @php
            $accessiblePhaseKeys = $participantPhaseView['accessiblePhaseKeys'] ?? [];
            $selectedPhaseKey = $participantPhaseView['selectedPhaseKey'] ?? null;
            $selectedPhase = $participantPhaseView['selectedPhase'] ?? null;
            $hasUpcomingPhase = $competition->phases->contains(fn ($p) => $p->status === 'upcoming');
        @endphp

        <div class="card border shadow-none mb-4 phase-wizard-card">
            <div class="card-body py-3 px-3 px-md-4">
                <div class="d-flex flex-column flex-md-row flex-md-wrap align-items-md-center justify-content-md-between gap-3 mb-3">
                    <div>
                        @if($selectedPhase)
                            <p class="mb-0 fs-5 fw-semibold d-flex align-items-center flex-wrap gap-2">
                                <span>{{ $selectedPhase->name }}</span>
                                @if($selectedPhase->isOpenNow())
                                    <span class="badge bg-success">Active phase</span>
                                @else
                                    <span class="badge bg-primary rounded-pill">{{ ucfirst(str_replace('_', ' ', $selectedPhase->status)) }}</span>
                                @endif
                            </p>
                            <p class="text-muted small mb-0 mt-1">
                                @if($selectedPhase->isOpenNow())
                                    This phase is open for actions now.
                                @else
                                    Reviewing this stage. Select another step below if you need to go back.
                                @endif
                            </p>
                        @elseif($hasUpcomingPhase)
                            <p class="mb-0 fs-5 fw-semibold d-flex align-items-center flex-wrap gap-2">
                                <span>Competition journey</span>
                                <span class="badge bg-secondary rounded-pill">Next up</span>
                            </p>
                            <p class="text-muted small mb-0 mt-1">No phase is live for you yet — check the timeline below.</p>
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
                                    $isSelected = $selectedPhaseKey && $selectedPhaseKey === $phase->phase_key;
                                    $isAccessible = in_array($phase->phase_key, $accessiblePhaseKeys, true);
                                @endphp
                                <div class="phase-wizard-step flex-grow-1 text-center px-1" style="min-width: 4.5rem; max-width: 9rem;">
                                    <div class="position-relative mx-auto mb-2" style="width: 2.5rem; height: 2.5rem;">
                                        @if($isAccessible)
                                            <button type="button"
                                                class="phase-wizard-marker phase-wizard-step-btn d-flex align-items-center justify-content-center fw-bold border border-2 mx-auto
                                                    @if($isSelected) bg-primary text-white border-primary
                                                    @elseif($isLive) bg-success text-white border-success
                                                    @elseif($isClosed) bg-light text-muted border-secondary
                                                    @else bg-white text-muted border-secondary @endif"
                                                data-phase-key="{{ $phase->phase_key }}"
                                                title="View {{ $phase->name }}">
                                                @if($isClosed && ! $isSelected)
                                                    <i class="bi bi-check-lg"></i>
                                                @else
                                                    {{ $phase->sequence }}
                                                @endif
                                            </button>
                                        @else
                                            <div class="phase-wizard-marker d-flex align-items-center justify-content-center fw-bold border border-2 mx-auto
                                                @if($isClosed) bg-light text-muted border-secondary opacity-50
                                                @else bg-white text-muted border-secondary opacity-50 @endif">
                                                @if($isClosed)
                                                    <i class="bi bi-check-lg"></i>
                                                @else
                                                    {{ $phase->sequence }}
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    <div class="small fw-semibold lh-sm text-truncate @if($isSelected) text-primary @endif" title="{{ $phase->name }}">{{ $phase->name }}</div>
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

        @if(in_array('registration', $accessiblePhaseKeys, true))
            <div class="competition-phase-panel @if($selectedPhaseKey !== 'registration') d-none @endif" data-phase-key="registration" id="phase-panel-registration">
                @include('client.back.competitions.phases.registration')
            </div>
        @endif

        @if(in_array('idea_submission', $accessiblePhaseKeys, true))
            <div class="competition-phase-panel @if($selectedPhaseKey !== 'idea_submission') d-none @endif" data-phase-key="idea_submission" id="phase-panel-idea-submission">
                @include('client.back.competitions.phases.idea_submission')
            </div>
        @endif

        @if(in_array('idea_shortlisting', $accessiblePhaseKeys, true))
            <div class="competition-phase-panel @if($selectedPhaseKey !== 'idea_shortlisting') d-none @endif" data-phase-key="idea_shortlisting" id="phase-panel-idea-shortlisting">
                @include('client.back.competitions.phases.idea_shortlisting')
            </div>
        @endif

        @if(in_array('prototype_development', $accessiblePhaseKeys, true))
            <div class="competition-phase-panel @if($selectedPhaseKey !== 'prototype_development') d-none @endif" data-phase-key="prototype_development" id="phase-panel-prototype-development">
                @include('client.back.competitions.phases.prototype_development')
            </div>
        @endif

        @if(in_array('prototype_shortlisting', $accessiblePhaseKeys, true))
            <div class="competition-phase-panel @if($selectedPhaseKey !== 'prototype_shortlisting') d-none @endif" data-phase-key="prototype_shortlisting" id="phase-panel-prototype-shortlisting">
                @include('client.back.competitions.phases.prototype_shortlisting')
            </div>
        @endif

        @if(in_array('mentor_onboarding', $accessiblePhaseKeys, true))
            <div class="competition-phase-panel @if($selectedPhaseKey !== 'mentor_onboarding') d-none @endif" data-phase-key="mentor_onboarding" id="phase-panel-mentor-onboarding">
                @include('client.back.competitions.phases.mentor_onboarding')
            </div>
        @endif

        @if(in_array('prototype_modification', $accessiblePhaseKeys, true))
            <div class="competition-phase-panel @if($selectedPhaseKey !== 'prototype_modification') d-none @endif" data-phase-key="prototype_modification" id="phase-panel-prototype-modification">
                @include('client.back.competitions.phases.prototype_modification')
            </div>
        @endif

        @if(in_array('pitch_winner', $accessiblePhaseKeys, true))
            <div class="competition-phase-panel @if($selectedPhaseKey !== 'pitch_winner') d-none @endif" data-phase-key="pitch_winner" id="phase-panel-pitch-winner">
                @include('client.back.competitions.phases.pitch_winner')
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            (function () {
                var panels = document.querySelectorAll('.competition-phase-panel');
                var stepButtons = document.querySelectorAll('.phase-wizard-step-btn');
                if (!panels.length || !stepButtons.length) {
                    return;
                }

                var legacyHashMap = {
                    'idea-submission': 'idea_submission',
                    'prototype-development': 'prototype_development',
                    'prototype-modification': 'prototype_modification',
                    'idea-shortlisting': 'idea_shortlisting',
                    'prototype-shortlisting': 'prototype_shortlisting',
                    'mentor-onboarding': 'mentor_onboarding',
                    'pitch-winner': 'pitch_winner',
                    'registration': 'registration'
                };

                function resolvePhaseFromHash() {
                    var hash = (window.location.hash || '').replace(/^#/, '');
                    if (!hash) {
                        return null;
                    }
                    if (hash.indexOf('phase-') === 0) {
                        return hash.slice(6).replace(/-/g, '_');
                    }
                    return legacyHashMap[hash] || hash.replace(/-/g, '_');
                }

                function showPhase(phaseKey) {
                    panels.forEach(function (panel) {
                        panel.classList.toggle('d-none', panel.dataset.phaseKey !== phaseKey);
                    });

                    stepButtons.forEach(function (button) {
                        var isSelected = button.dataset.phaseKey === phaseKey;
                        button.classList.toggle('bg-primary', isSelected);
                        button.classList.toggle('text-white', isSelected);
                        button.classList.toggle('border-primary', isSelected);
                        if (isSelected) {
                            button.classList.remove('bg-success', 'border-success');
                        }
                    });
                }

                stepButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        var phaseKey = button.dataset.phaseKey;
                        showPhase(phaseKey);
                        var hash = 'phase-' + phaseKey.replace(/_/g, '-');
                        if (window.location.hash !== '#' + hash) {
                            history.replaceState(null, '', '#' + hash);
                        }
                    });
                });

                var hashPhase = resolvePhaseFromHash();
                if (hashPhase && document.querySelector('.competition-phase-panel[data-phase-key="' + hashPhase + '"]')) {
                    showPhase(hashPhase);
                }
            })();
        </script>
    @endpush
</x-app-layout>
