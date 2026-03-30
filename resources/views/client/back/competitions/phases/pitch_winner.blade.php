@php
    $modification = $idea?->prototypeModification;
    $modShortlist = $modification?->modificationShortlist;
    $calledForPitch = $modShortlist?->status === 'shortlisted';
@endphp

<div id="pitch-winner" class="row">
    <div class="col-12">
        <div class="card border-0 shadow-none mb-4">
            <div class="card-header bg-transparent">
                <h4 class="mb-0 text-uppercase">Pitch & winner</h4>
                <p class="text-muted small mb-0 mt-1">Final stage: teams called for pitch present their prototypes.</p>
            </div>
            <div class="card-body">
                @if(!$teacherClub)
                    <div class="alert alert-warning mb-0">You must be an approved teacher in this club to view this competition.</div>
                @elseif(!$registration)
                    <p class="text-muted mb-0">No registration found for your school in this competition.</p>
                @elseif(! $modification || ! $modShortlist)
                    <div class="alert alert-info mb-0">No prototype modification shortlist decision found yet.</div>
                @else
                    @if($calledForPitch)
                        <div class="alert alert-success mb-3">
                            <strong>Congratulations!</strong> Your team has been called for pitch.
                        </div>
                    @else
                        <div class="alert alert-warning mb-3">
                            Your team is not called for pitch at this stage.
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-muted mb-1">Prototype title</div>
                                <div class="fw-semibold">{{ $modification->prototype_title ?: '—' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-muted mb-1">Decision status</div>
                                <div class="fw-semibold">{{ ucfirst((string) ($modShortlist->status ?? 'pending')) }}</div>
                            </div>
                        </div>
                    </div>

                    @if(filled($modShortlist->feedback))
                        <div class="alert alert-light border mt-3 mb-0">
                            <div class="fw-semibold mb-1">Judge feedback</div>
                            <div class="small text-body-secondary">{!! nl2br(e($modShortlist->feedback)) !!}</div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

