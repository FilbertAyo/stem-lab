@php
    $shortlist = $idea?->shortlist;
    $decisionStatus = $shortlist?->status;
    if ($decisionStatus === null && $idea && $idea->status === 'shortlisted') {
        $decisionStatus = 'shortlisted';
    }

    $prototype = $idea?->prototypeSubmission;
    $prototypeStatus = $prototype?->status;
    $repTotal = $registration?->representativeStudents->count() ?? 0;

    // Identifier for the external Google Form (so we can match the evidence upload to this account).
    $teacherUserId = auth()->id();
    $prototypeEvidenceIdentifier = implode('-', array_filter([
        (string) $teacherUserId,
        (string) ($competition?->ulid ?? ''),
        (string) ($idea?->ulid ?? ''),
    ], fn (string $v): bool => $v !== ''));
@endphp

<div id="prototype-development" class="row">
    <div class="col-12">
        <div class="card border shadow-none mb-4 competition-phase-card">
            <div class="card-header bg-light-soft">
                <h5 class="mb-0 fw-semibold">Prototype development</h5>
                <p class="text-muted small mb-0 mt-1">
                    Your idea’s problem details were already submitted in the previous phase. Here, focus on what you built and how it works.
                </p>
            </div>

            <div class="card-body">
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

                    <div class="d-flex flex-wrap align-items-center gap-2 mb-4 pb-3 border-bottom border-opacity-25">
                        <span class="badge bg-{{ $prototypeBadge[0] }}">{{ $prototypeBadge[1] }}</span>
                        <span class="text-muted small">
                            Student reps selected for this idea: <strong>{{ $repTotal }}</strong>
                        </span>
                        @if($shortlist?->rank)
                            <span class="text-muted small">Regional rank: <strong>{{ $shortlist->rank }}</strong></span>
                        @endif
                    </div>

                    <div class="row g-4">
                        <div class="col-lg-7">
                            <h6 class="text-muted small fw-semibold mb-3">Prototype evidence form</h6>

                            @if(! $canEdit)
                                <div class="alert alert-info mb-4">
                                    Prototype development is closed. The submitted content below is read-only.
                                </div>
                            @endif

                            @if($prototype && $prototypeStatus === 'submitted' && ! $canEdit)
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

                                    {{-- Evidence (video/pictures) is uploaded separately via the organisers' Google Form. --}}
                                </dl>

                                @if(filled($prototypeEvidenceFormUrl))
                                    <div class="alert alert-light border mb-3">
                                        <div class="small text-muted mb-1"><strong>Google Form link</strong></div>
                                        <a href="{{ $prototypeEvidenceFormUrl }}" target="_blank" rel="noopener noreferrer">
                                            Open Google Form
                                        </a>
                                    </div>
                                    <div class="mb-3">
                                        <div class="small text-muted mb-1"><strong>Google Form identifier</strong></div>
                                        <span class="badge bg-primary">{{ $prototypeEvidenceIdentifier }}</span>
                                    </div>
                                @endif
                            @else
                                <form method="POST" action="{{ route('client.competitions.prototype.store', $competition) }}" id="prototype-form">
                                    @csrf

                                    <div class="mb-3">
                                        <label class="form-label">Prototype title <span class="text-danger">*</span></label>
                                        <input type="text"
                                               name="prototype_title"
                                               class="form-control @error('prototype_title') is-invalid @enderror"
                                               value="{{ old('prototype_title', $prototype?->prototype_title) }}"
                                               @if($canEdit) required @else disabled @endif
                                               maxlength="255">
                                        @error('prototype_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">What you built (prototype overview) <span class="text-danger">*</span></label>
                                        <textarea name="prototype_concept"
                                                  class="form-control @error('prototype_concept') is-invalid @enderror"
                                                  rows="3"
                                                  @if(! $canEdit) disabled @endif
                                                  required>{{ old('prototype_concept', $prototype?->prototype_concept) }}</textarea>
                                        @error('prototype_concept')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Why it matters (goals of the prototype) <span class="text-danger">*</span></label>
                                        <textarea name="prototype_objectives"
                                                  class="form-control @error('prototype_objectives') is-invalid @enderror"
                                                  rows="3"
                                                  @if(! $canEdit) disabled @endif
                                                  required>{{ old('prototype_objectives', $prototype?->prototype_objectives) }}</textarea>
                                        @error('prototype_objectives')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">How it works (mechanism/steps) <span class="text-danger">*</span></label>
                                        <textarea name="prototype_design_description"
                                                  class="form-control @error('prototype_design_description') is-invalid @enderror"
                                                  rows="4"
                                                  @if(! $canEdit) disabled @endif
                                                  required>{{ old('prototype_design_description', $prototype?->prototype_design_description) }}</textarea>
                                        @error('prototype_design_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Materials / parts you used <span class="text-danger">*</span></label>
                                        <textarea name="prototype_materials"
                                                  class="form-control @error('prototype_materials') is-invalid @enderror"
                                                  rows="3"
                                                  @if(! $canEdit) disabled @endif
                                                  required>{{ old('prototype_materials', $prototype?->prototype_materials) }}</textarea>
                                        @error('prototype_materials')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Build process (steps & assembly) <span class="text-danger">*</span></label>
                                        <textarea name="prototype_build_process"
                                                  class="form-control @error('prototype_build_process') is-invalid @enderror"
                                                  rows="4"
                                                  @if(! $canEdit) disabled @endif
                                                  required>{{ old('prototype_build_process', $prototype?->prototype_build_process) }}</textarea>
                                        @error('prototype_build_process')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Challenges / limitations (what didn’t work fully)</label>
                                        <textarea name="prototype_challenges"
                                                  class="form-control @error('prototype_challenges') is-invalid @enderror"
                                                  rows="3"
                                                  @if(! $canEdit) disabled @endif>{{ old('prototype_challenges', $prototype?->prototype_challenges) }}</textarea>
                                        @error('prototype_challenges')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Test results (optional)</label>
                                        <textarea name="prototype_test_results"
                                                  class="form-control @error('prototype_test_results') is-invalid @enderror"
                                                  rows="3"
                                                  @if(! $canEdit) disabled @endif>{{ old('prototype_test_results', $prototype?->prototype_test_results) }}</textarea>
                                        @error('prototype_test_results')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Next steps / improvements (optional)</label>
                                        <textarea name="prototype_next_steps"
                                                  class="form-control @error('prototype_next_steps') is-invalid @enderror"
                                                  rows="3"
                                                  @if(! $canEdit) disabled @endif>{{ old('prototype_next_steps', $prototype?->prototype_next_steps) }}</textarea>
                                        @error('prototype_next_steps')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <hr class="my-4 opacity-25">

                                    @if(filled($prototypeEvidenceFormUrl))
                                        <div class="alert alert-light border mb-4">
                                            <div class="small text-muted mb-1"><strong>Google Form link (upload video & pictures)</strong></div>
                                            <div class="d-flex flex-wrap align-items-center gap-2">
                                                <a
                                                    href="{{ $prototypeEvidenceFormUrl }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="btn btn-sm btn-primary text-white"
                                                >
                                                    Open Google Form
                                                </a>
                                                <span class="text-muted small">
                                                    Upload using the copied identifier below.
                                                </span>
                                            </div>
                                        </div>
                                    @else
                                        <div class="alert alert-info border mb-4">
                                            The organisers haven’t shared the Google Form link yet. Check back soon.
                                        </div>
                                    @endif

                                    <div class="alert alert-light border mb-4">
                                        <div class="small text-muted mb-1"><strong>Google Form identifier (copy & paste)</strong></div>
                                        <div class="small text-muted mb-2">
                                            Teacher User ID: <span class="fw-semibold">{{ $teacherUserId ?? '—' }}</span>
                                        </div>

                                        <div class="input-group mb-2" style="max-width: 720px;">
                                            <input
                                                id="prototypeEvidenceIdentifierInput"
                                                type="text"
                                                class="form-control font-monospace"
                                                value="{{ $prototypeEvidenceIdentifier }}"
                                                readonly
                                            >
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-dark"
                                                id="copyPrototypeEvidenceIdentifierBtn"
                                                @if(! $canEdit) disabled @endif
                                            >
                                                Copy ID
                                            </button>
                                        </div>

                                        <div class="form-text">
                                            The organisers will share a Google Form link to upload your video and pictures.
                                            Paste this identifier in that form so we can match your evidence to your account.
                                        </div>
                                        <div id="copyPrototypeEvidenceIdentifierFeedback" class="small mt-2" aria-live="polite"></div>
                                    </div>

                                    @if($canEdit)
                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                            <button type="submit" class="btn btn-dark btn-sm" name="submit_final" value="0">Save draft</button>
                                            <button type="submit" class="btn btn-primary text-white btn-sm" name="submit_final" value="1">Submit prototype</button>
                                        </div>
                                    @endif
                                </form>
                            @endif
                        </div>

                        <div class="col-lg-5">
                            <h6 class="text-muted small fw-semibold mb-3">Student representatives</h6>
                            <p class="small text-muted mb-3">
                                These are the representatives selected for your idea. Prototype development is expected to be led by this team.
                            </p>

                            @if($registration->representativeStudents->isNotEmpty())
                                <ul class="list-group list-group-flush border rounded small mb-0">
                                    @foreach($registration->representativeStudents as $rs)
                                        <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                            <span>{{ $rs->student?->name ?? '—' }}</span>
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('copyPrototypeEvidenceIdentifierBtn');
            const input = document.getElementById('prototypeEvidenceIdentifierInput');
            const feedback = document.getElementById('copyPrototypeEvidenceIdentifierFeedback');
            if (!btn || !input) return;

            function setFeedback(message, type) {
                if (!feedback) return;
                feedback.textContent = message;
                feedback.classList.remove('text-success', 'text-danger');
                feedback.classList.add(type === 'success' ? 'text-success' : 'text-danger');
            }

            btn.addEventListener('click', async function () {
                const token = input.value || '';
                if (!token) {
                    setFeedback('Nothing to copy.', 'error');
                    return;
                }

                try {
                    await navigator.clipboard.writeText(token);
                    setFeedback('Copied!', 'success');
                } catch (e) {
                    // Fallback for older browsers / restricted clipboard permissions
                    input.focus();
                    input.select();
                    try {
                        document.execCommand('copy');
                        setFeedback('Copied!', 'success');
                    } catch (err) {
                        setFeedback('Copy failed. Please copy manually.', 'error');
                    }
                    window.getSelection && window.getSelection().removeAllRanges();
                }
            });
        });
    </script>
@endpush

