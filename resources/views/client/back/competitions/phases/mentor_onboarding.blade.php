@php
    $prototype = $idea?->prototypeSubmission;
    $prototypeShortlist = $prototype?->prototypeShortlist;
    $mentors = $prototypeShortlist?->mentors ?? collect();
    $decisionStatus = $prototypeShortlist?->status;

    $badge = match ($decisionStatus) {
        'shortlisted' => ['success', 'Shortlisted'],
        'waitlist' => ['warning', 'Waitlisted'],
        'rejected' => ['danger', 'Not selected'],
        default => ['secondary', 'Awaiting prototype decision'],
    };
@endphp

<div id="mentor-onboarding" class="row">
    <div class="col-12">
        <div class="card border-0 shadow-none mb-4">
            <div class="card-header bg-transparent">
                <h4 class="mb-0 text-uppercase">Mentor onboarding</h4>
                <p class="text-muted small mb-0 mt-1">
                    Judges selected prototypes in the previous stage. Here are your onboarded mentors.
                </p>
            </div>

            <div class="card-body">
                @if(!$teacherClub)
                    <div class="alert alert-warning mb-0">You must be an approved teacher in this club to view this competition.</div>
                @elseif(!$registration)
                    <p class="text-muted mb-0">No registration found for your school in this competition.</p>
                @elseif(! $prototype || ! $prototypeShortlist)
                    <div class="alert alert-info mb-0">No prototype submission was found yet for your idea.</div>
                @elseif($prototypeShortlist->status !== 'shortlisted')
                    <div class="alert alert-warning mb-0">
                        Mentor onboarding is available only for shortlisted teams.
                        <strong>{{ $badge[1] }}</strong>.
                    </div>
                @else
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-4 pb-3 border-bottom border-opacity-25">
                        <span class="badge bg-{{ $badge[0] }}">{{ $badge[1] }}</span>
                        <span class="text-muted small">Mentors onboarded: <strong>{{ $mentors->count() }}</strong></span>
                    </div>

                    <div class="row g-4">
                        <div class="col-lg-7">
                            <h6 class="text-uppercase text-muted small mb-3">Your prototype (from development phase)</h6>

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
                        </div>

                        <div class="col-lg-5">
                            <h6 class="text-uppercase text-muted small mb-3">Assigned mentors</h6>

                            @if($mentors->isEmpty())
                                <div class="alert alert-light border mb-0">
                                    No mentors have been added by admins yet. Check back soon.
                                </div>
                            @else
                                <div class="row g-3">
                                    @foreach($mentors as $mentor)
                                        <div class="col-12">
                                            <div class="border rounded p-3 h-100">
                                                <div class="d-flex gap-3 align-items-start">
                                                    <div style="width: 64px; height: 64px; border-radius: 14px; background: #f3f4f6; overflow: hidden;">
                                                        @if(filled($mentor->picture_url))
                                                            <img src="{{ $mentor->picture_url }}" alt="{{ $mentor->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                        @else
                                                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">—</div>
                                                        @endif
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="fw-semibold mb-1">{{ $mentor->name }}</div>
                                                        <div class="small text-muted mb-1">{{ $mentor->email ?: '—' }}</div>
                                                        <div class="small text-muted mb-0">{{ $mentor->phone ?: '—' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

