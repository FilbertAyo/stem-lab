@php
    $shortlistDecision = $idea?->prototypeSubmission?->prototypeShortlist;
    $decisionStatus = $shortlistDecision?->status;
    $prototype = $idea?->prototypeSubmission;
    $badge = match ($decisionStatus) {
        'shortlisted' => ['success', 'Prototype shortlisted'],
        'waitlist' => ['warning', 'Prototype waitlisted'],
        'rejected' => ['danger', 'Prototype not selected'],
        default => ['secondary', 'Awaiting prototype review'],
    };
@endphp

<div id="prototype-shortlisting" class="row">
    <div class="col-12">
        <div class="card border shadow-none mb-4 competition-phase-card">
            <div class="card-header bg-light-soft">
                <h5 class="mb-0 fw-semibold">Prototype shortlisting</h5>
                <p class="text-muted small mb-0 mt-1">Judges are reviewing your prototype. No action is required from you right now.</p>
            </div>

            <div class="card-body">
                @if(!$teacherClub)
                    <div class="alert alert-warning mb-0">You must be an approved teacher in this club to view this competition.</div>
                @elseif(!$registration)
                    <p class="text-muted mb-0">No registration found for your school in this competition.</p>
                @elseif(! $idea || ! $prototype)
                    <div class="alert alert-info mb-0">
                        No prototype submission was found for your idea yet.
                    </div>
                @else
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-4 pb-3 border-bottom border-opacity-25 border-bottom">
                        <span class="badge bg-{{ $badge[0] }}">{{ $badge[1] }}</span>
                        @if($shortlistDecision?->feedback)
                            <span class="text-muted small">Judge feedback added.</span>
                        @endif
                    </div>

                    <div class="row g-4">
                        <div class="col-lg-7">
                            <h6 class="text-muted small fw-semibold mb-3">Your prototype (read only)</h6>

                            <dl class="row small mb-0">
                                <dt class="col-sm-4 text-muted fw-normal">Prototype title</dt>
                                <dd class="col-sm-8 mb-2">{{ $prototype->prototype_title ?: '—' }}</dd>

                                <dt class="col-sm-4 text-muted fw-normal">What you built</dt>
                                <dd class="col-sm-8 mb-2">{!! nl2br(e($prototype->prototype_concept ?: '—')) !!}</dd>

                                <dt class="col-sm-4 text-muted fw-normal">Why it matters</dt>
                                <dd class="col-sm-8 mb-2">{!! nl2br(e($prototype->prototype_objectives ?: '—')) !!}</dd>

                                <dt class="col-sm-4 text-muted fw-normal">How it works</dt>
                                <dd class="col-sm-8 mb-2">{!! nl2br(e($prototype->prototype_design_description ?: '—')) !!}</dd>

                                <dt class="col-sm-4 text-muted fw-normal">Materials</dt>
                                <dd class="col-sm-8 mb-2">{!! nl2br(e($prototype->prototype_materials ?: '—')) !!}</dd>

                                <dt class="col-sm-4 text-muted fw-normal">Build process</dt>
                                <dd class="col-sm-8 mb-2">{!! nl2br(e($prototype->prototype_build_process ?: '—')) !!}</dd>

                                <dt class="col-sm-4 text-muted fw-normal">Challenges</dt>
                                <dd class="col-sm-8 mb-2">{!! nl2br(e($prototype->prototype_challenges ?: '—')) !!}</dd>

                                <dt class="col-sm-4 text-muted fw-normal">Test / results</dt>
                                <dd class="col-sm-8 mb-2">{!! nl2br(e($prototype->prototype_test_results ?: '—')) !!}</dd>

                                <dt class="col-sm-4 text-muted fw-normal">Next steps</dt>
                                <dd class="col-sm-8 mb-2">{!! nl2br(e($prototype->prototype_next_steps ?: '—')) !!}</dd>
                            </dl>

                            @if($shortlistDecision?->feedback)
                                <div class="alert alert-light border mt-4 mb-0">
                                    <div class="fw-semibold mb-1">Judge feedback</div>
                                    <div class="small text-body-secondary">{!! nl2br(e($shortlistDecision->feedback)) !!}</div>
                                </div>
                            @endif
                        </div>

                        <div class="col-lg-5">
                            <h6 class="text-muted small fw-semibold mb-3">Submission details</h6>

                            <ul class="list-unstyled mb-0 small">
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">Submitted at</span>
                                    <span>{{ $prototype->submitted_at?->timezone(config('app.timezone'))->format('j M Y, H:i') ?? '—' }}</span>
                                </li>
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">Submitted by</span>
                                    <span>{{ $prototype->submittedBy?->name ?? '—' }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

