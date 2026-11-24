@php
    $shortlist = $idea?->shortlist;
    $decisionStatus = $shortlist?->status;
    if ($decisionStatus === null && $idea && $idea->status === 'shortlisted') {
        $decisionStatus = 'shortlisted';
    }

    $prototype = $idea?->prototypeSubmission;
    $prototypeStatus = $prototype?->status;
    $repTotal = $registration?->representativeStudents->count() ?? 0;
    $requiredPrototypeFields = [
        'prototype_title' => 'Prototype title',
        'prototype_concept' => 'What you built',
        'prototype_objectives' => 'Why it matters',
        'prototype_design_description' => 'How it works',
        'prototype_materials' => 'Materials / parts you used',
        'prototype_build_process' => 'Build process',
    ];
@endphp

<div id="prototype-development" class="row">
    <div class="col-12">
        <div class="card border shadow-none mb-4 competition-phase-card">
            <div class="card-header bg-light-soft">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h5 class="mb-1 fw-semibold">Prototype development</h5>
                        <p class="text-muted mb-0">
                            Your idea’s problem details were already submitted. Here, write what you built and how it works.
                        </p>
                    </div>
                    @if($registration && $decisionStatus === 'shortlisted')
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#prototypeRepresentativesModal">
                            View representatives
                            @if($repTotal > 0)
                                ({{ $repTotal }})
                            @endif
                        </button>
                    @endif
                </div>
            </div>

            <div class="card-body">
                @if(filled($idea?->admin_feedback))
                    <div class="alert alert-primary border mb-4" role="status">
                        <h6 class="fw-semibold mb-2">Feedback on your idea</h6>
                        <div class="text-break">{!! nl2br(e($idea->admin_feedback)) !!}</div>
                    </div>
                @endif

                @if(!$teacherClub)
                    <div class="alert alert-warning mb-0">You must be an approved teacher in this club to view this competition.</div>
                @elseif(!$registration)
                    <p class="text-muted mb-0">No registration found for your school in this competition.</p>
                @elseif(! $idea || $idea->status === 'draft')
                    <div class="alert alert-info mb-0">
                        No submitted idea was found. If the idea submission phase is still open, complete your idea first.
                    </div>
                @elseif($decisionStatus !== 'shortlisted')
                    <div class="alert alert-warning mb-0">
                        Prototype development is available only for shortlisted teams.
                        <strong>{{ $decisionStatus ? ucfirst(str_replace('_', ' ', $decisionStatus)) : 'Not shortlisted yet' }}</strong>.
                    </div>
                @else
                    @php
                        $prototypeBadge = match ($prototypeStatus) {
                            'submitted' => ['success', 'Submitted'],
                            'draft' => ['secondary', 'Draft'],
                            default => ['secondary', 'Not started'],
                        };
                        $canEdit = $competition->isPhaseOpen('prototype_development');
                    @endphp

                    <div class="d-flex flex-wrap align-items-center gap-3 mb-4 pb-3 border-bottom">
                        <span class="badge bg-{{ $prototypeBadge[0] }} fs-6">{{ $prototypeBadge[1] }}</span>
                        @if($prototypeStatus === 'submitted')
                            <span>Submitted {{ $prototype?->submitted_at?->timezone(config('app.timezone'))->format('j M Y, H:i') }}</span>
                        @elseif($prototypeStatus === 'draft')
                            <span>Draft saved. This is not a final submission yet.</span>
                        @else
                            <span>Not submitted yet.</span>
                        @endif
                        @if($shortlist?->rank)
                            <span>Regional rank: <strong>{{ $shortlist->rank }}</strong></span>
                        @endif
                    </div>

                    @if(! $canEdit)
                        <div class="alert alert-info mb-4">
                            Prototype development is closed. The content below is read-only.
                        </div>
                    @elseif($prototypeStatus === 'submitted')
                        <div class="alert alert-success mb-4">
                            This prototype is submitted. You can still update the answers while this phase is open. Saving a draft keeps the submitted status.
                        </div>
                    @endif

                    @if($prototype && $prototypeStatus === 'submitted' && ! $canEdit)
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Prototype title</dt>
                            <dd class="col-sm-8">{{ $prototype->prototype_title ?: '—' }}</dd>
                            <dt class="col-sm-4">What you built</dt>
                            <dd class="col-sm-8 text-break">{!! nl2br(e($prototype->prototype_concept ?: '—')) !!}</dd>
                            <dt class="col-sm-4">Why it matters</dt>
                            <dd class="col-sm-8 text-break">{!! nl2br(e($prototype->prototype_objectives ?: '—')) !!}</dd>
                            <dt class="col-sm-4">How it works</dt>
                            <dd class="col-sm-8 text-break">{!! nl2br(e($prototype->prototype_design_description ?: '—')) !!}</dd>
                            <dt class="col-sm-4">Materials</dt>
                            <dd class="col-sm-8 text-break">{!! nl2br(e($prototype->prototype_materials ?: '—')) !!}</dd>
                            <dt class="col-sm-4">Build process</dt>
                            <dd class="col-sm-8 text-break">{!! nl2br(e($prototype->prototype_build_process ?: '—')) !!}</dd>
                            <dt class="col-sm-4">Challenges</dt>
                            <dd class="col-sm-8 text-break">{!! nl2br(e($prototype->prototype_challenges ?: '—')) !!}</dd>
                            <dt class="col-sm-4">Test / results</dt>
                            <dd class="col-sm-8 text-break">{!! nl2br(e($prototype->prototype_test_results ?: '—')) !!}</dd>
                            <dt class="col-sm-4">Next steps</dt>
                            <dd class="col-sm-8 text-break mb-0">{!! nl2br(e($prototype->prototype_next_steps ?: '—')) !!}</dd>
                        </dl>
                        <div class="border rounded p-3 p-md-4 mt-4">
                            <h6 class="fw-semibold mb-3">Video and photos</h6>
                            @include('partials.prototype-media-display', ['media' => $prototype])
                        </div>
                    @else
                        <form
                            method="POST"
                            action="{{ route('client.competitions.prototype.store', $competition) }}"
                            id="prototype-form"
                            enctype="multipart/form-data"
                            data-prototype-media
                            data-already-submitted="{{ $prototypeStatus === 'submitted' ? '1' : '0' }}"
                        >
                            @csrf
                            <input type="hidden" name="submit_intent" id="prototype-submit-intent" value="draft">

                            <div class="mb-3">
                                <label class="form-label">Prototype title <span class="text-danger">*</span></label>
                                <input type="text"
                                       name="prototype_title"
                                       class="form-control @error('prototype_title') is-invalid @enderror"
                                       value="{{ old('prototype_title', $prototype?->prototype_title) }}"
                                       @if(! $canEdit) disabled @endif
                                       maxlength="255">
                                @error('prototype_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">What you built (prototype overview) <span class="text-danger">*</span></label>
                                <textarea name="prototype_concept"
                                          class="form-control @error('prototype_concept') is-invalid @enderror"
                                          rows="4"
                                          @if(! $canEdit) disabled @endif>{{ old('prototype_concept', $prototype?->prototype_concept) }}</textarea>
                                @error('prototype_concept')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Why it matters (goals of the prototype) <span class="text-danger">*</span></label>
                                <textarea name="prototype_objectives"
                                          class="form-control @error('prototype_objectives') is-invalid @enderror"
                                          rows="4"
                                          @if(! $canEdit) disabled @endif>{{ old('prototype_objectives', $prototype?->prototype_objectives) }}</textarea>
                                @error('prototype_objectives')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">How it works (mechanism/steps) <span class="text-danger">*</span></label>
                                <textarea name="prototype_design_description"
                                          class="form-control @error('prototype_design_description') is-invalid @enderror"
                                          rows="5"
                                          @if(! $canEdit) disabled @endif>{{ old('prototype_design_description', $prototype?->prototype_design_description) }}</textarea>
                                @error('prototype_design_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Materials / parts you used <span class="text-danger">*</span></label>
                                <textarea name="prototype_materials"
                                          class="form-control @error('prototype_materials') is-invalid @enderror"
                                          rows="4"
                                          @if(! $canEdit) disabled @endif>{{ old('prototype_materials', $prototype?->prototype_materials) }}</textarea>
                                @error('prototype_materials')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Build process (steps & assembly) <span class="text-danger">*</span></label>
                                <textarea name="prototype_build_process"
                                          class="form-control @error('prototype_build_process') is-invalid @enderror"
                                          rows="5"
                                          @if(! $canEdit) disabled @endif>{{ old('prototype_build_process', $prototype?->prototype_build_process) }}</textarea>
                                @error('prototype_build_process')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Challenges / limitations (what didn’t work fully)</label>
                                <textarea name="prototype_challenges"
                                          class="form-control @error('prototype_challenges') is-invalid @enderror"
                                          rows="4"
                                          @if(! $canEdit) disabled @endif>{{ old('prototype_challenges', $prototype?->prototype_challenges) }}</textarea>
                                @error('prototype_challenges')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Test results (optional)</label>
                                <textarea name="prototype_test_results"
                                          class="form-control @error('prototype_test_results') is-invalid @enderror"
                                          rows="4"
                                          @if(! $canEdit) disabled @endif>{{ old('prototype_test_results', $prototype?->prototype_test_results) }}</textarea>
                                @error('prototype_test_results')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Next steps / improvements (optional)</label>
                                <textarea name="prototype_next_steps"
                                          class="form-control @error('prototype_next_steps') is-invalid @enderror"
                                          rows="4"
                                          @if(! $canEdit) disabled @endif>{{ old('prototype_next_steps', $prototype?->prototype_next_steps) }}</textarea>
                                @error('prototype_next_steps')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            @include('partials.prototype-media-fields', [
                                'media' => $prototype,
                                'disabled' => ! $canEdit,
                                'fieldId' => 'prototypeDevelopment',
                            ])

                            @if($canEdit)
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <button type="submit" class="btn btn-dark" name="submit_final" value="0" data-submit-intent="draft">Save draft</button>
                                    <button type="submit" class="btn btn-primary text-white" name="submit_final" value="1" data-submit-intent="submit">Submit prototype</button>
                                </div>
                            @endif
                        </form>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

@if($registration && $decisionStatus === 'shortlisted')
    <div class="modal fade" id="prototypeRepresentativesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Student representatives</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">These are the representatives selected for your idea.</p>
                    @if($registration->representativeStudents->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Gender</th>
                                        <th>Class</th>
                                        <th>Role</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($registration->representativeStudents as $rs)
                                        <tr>
                                            <td>{{ $rs->student?->name ?? '—' }}</td>
                                            <td>{{ $rs->student?->gender ? ucfirst((string) $rs->student->gender) : '—' }}</td>
                                            <td>{{ $rs->student?->schoolClass?->name ?? '—' }}</td>
                                            <td>{{ $rs->role ?: '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">No representatives are stored for this registration.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('prototype-form');
            if (!form) return;

            const intentInput = document.getElementById('prototype-submit-intent');
            const requiredNames = @json(array_keys($requiredPrototypeFields));
            const requiredLabels = @json($requiredPrototypeFields);
            let submitting = false;

            form.querySelectorAll('button[type="submit"][data-submit-intent]').forEach(function (button) {
                button.addEventListener('click', function (event) {
                    const intent = button.getAttribute('data-submit-intent') || 'draft';
                    if (intentInput) {
                        intentInput.value = intent;
                    }

                    if (intent !== 'submit') {
                        return;
                    }

                    const missing = requiredNames.filter(function (name) {
                        const field = form.elements[name];
                        return !field || String(field.value || '').trim() === '';
                    }).map(function (name) {
                        return requiredLabels[name] || name;
                    });

                    if (missing.length) {
                        event.preventDefault();
                        window.alert('Fill in the required fields before submitting: ' + missing.join(', ') + '.');
                        return;
                    }

                    const alreadySubmitted = form.dataset.alreadySubmitted === '1';
                    const prompt = alreadySubmitted
                        ? 'Submit these answers again? This keeps the prototype marked as submitted.'
                        : 'Submit this prototype? This marks it as submitted.';
                    if (!window.confirm(prompt)) {
                        event.preventDefault();
                    }
                });
            });

            form.addEventListener('submit', function (event) {
                if (submitting) {
                    event.preventDefault();
                    return;
                }
                submitting = true;
                window.setTimeout(function () {
                    form.querySelectorAll('button[type="submit"]').forEach(function (button) {
                        button.disabled = true;
                    });
                }, 0);
            });
        });
    </script>
@endpush
