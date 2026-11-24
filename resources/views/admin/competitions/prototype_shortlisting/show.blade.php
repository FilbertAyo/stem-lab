<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Prototype Submission Review - {{ $competition->name }}</h5>
                    <p class="text-muted mb-0">
                        School: {{ $submission->registration?->resolvedSchoolName() ?? '—' }}
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.competitions.prototype-shortlisting.index', $competition) }}" class="btn btn-outline-secondary btn-sm">
                        Back to list
                    </a>
                </div>
            </div>

            

                <div class="alert alert-success alert-dismissible fade show" role="alert">


            @php
                $shortlist = $submission->prototypeShortlist;
                $status = $shortlist?->status;
                $badgeClass = $status === 'shortlisted'
                    ? 'success'
                    : ($status === 'waitlist'
                        ? 'warning'
                        : ($status === 'rejected' ? 'danger' : 'light'));

                $mentors = $submission->prototypeShortlist?->mentors ?? collect();
                $canAddMentors = ($status ?? null) === 'shortlisted';
            @endphp

            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <span class="badge bg-{{ $badgeClass }}">{{ $status ? ucfirst($status) : 'Pending' }}</span>
                        <span class="text-muted small">
                            Submitted: {{ $submission->submitted_at?->format('d M Y, H:i') ?? '—' }}
                        </span>
                    </div>

                    <div class="row g-3">
                        <div class="col-lg-7">
                            <h6 class="text-uppercase text-muted small mb-3">Prototype details</h6>
                            <dl class="row small mb-0">
                                <dt class="col-sm-4 text-muted fw-normal">Prototype title</dt>
                                <dd class="col-sm-8 mb-2">{{ $submission->prototype_title ?: '—' }}</dd>

                                <dt class="col-sm-4 text-muted fw-normal">Idea</dt>
                                <dd class="col-sm-8 mb-2">{{ $submission->idea?->problem_title ?: '—' }}</dd>

                                <dt class="col-sm-4 text-muted fw-normal">What you built</dt>
                                <dd class="col-sm-8 mb-2">{!! nl2br(e($submission->prototype_concept ?: '—')) !!}</dd>

                                <dt class="col-sm-4 text-muted fw-normal">Why it matters</dt>
                                <dd class="col-sm-8 mb-2">{!! nl2br(e($submission->prototype_objectives ?: '—')) !!}</dd>

                                <dt class="col-sm-4 text-muted fw-normal">How it works</dt>
                                <dd class="col-sm-8 mb-2">{!! nl2br(e($submission->prototype_design_description ?: '—')) !!}</dd>

                                <dt class="col-sm-4 text-muted fw-normal">Materials</dt>
                                <dd class="col-sm-8 mb-2">{!! nl2br(e($submission->prototype_materials ?: '—')) !!}</dd>

                                <dt class="col-sm-4 text-muted fw-normal">Build process</dt>
                                <dd class="col-sm-8 mb-2">{!! nl2br(e($submission->prototype_build_process ?: '—')) !!}</dd>

                                <dt class="col-sm-4 text-muted fw-normal">Challenges</dt>
                                <dd class="col-sm-8 mb-2">{!! nl2br(e($submission->prototype_challenges ?: '—')) !!}</dd>

                                <dt class="col-sm-4 text-muted fw-normal">Test / results</dt>
                                <dd class="col-sm-8 mb-2">{!! nl2br(e($submission->prototype_test_results ?: '—')) !!}</dd>

                                <dt class="col-sm-4 text-muted fw-normal">Next steps</dt>
                                <dd class="col-sm-8 mb-2">{!! nl2br(e($submission->prototype_next_steps ?: '—')) !!}</dd>
                            </dl>
                        </div>

                        <div class="col-lg-5">
                            <h6 class="text-uppercase text-muted small mb-3">Prototype media</h6>
                            <div class="border rounded p-3 mb-3">
                                @include('partials.prototype-media-display', ['media' => $submission])
                            </div>

                            @if($shortlist?->feedback)
                                <div class="alert alert-info mb-3">
                                    <div class="fw-semibold mb-1">Current judge feedback</div>
                                    <div class="small text-body-secondary">{!! nl2br(e($shortlist->feedback)) !!}</div>
                                </div>
                            @endif

                            <div class="card border-0 shadow-none">
                                <div class="card-header bg-transparent px-0 pb-2">
                                    <h6 class="mb-0 text-uppercase text-muted small">Shortlist decision</h6>
                                </div>
                                <div class="card-body px-0">
                                    <form method="POST" action="{{ route('admin.competitions.prototype-shortlisting.shortlist', [$competition, $submission]) }}">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label small mb-1">Status</label>
                                            <select name="status" class="form-select form-select-sm">
                                                @foreach(['shortlisted','waitlist','rejected'] as $opt)
                                                    <option value="{{ $opt }}" @selected($opt === ($shortlist?->status ?? 'shortlisted'))>
                                                        {{ ucfirst($opt) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small mb-1">Rank</label>
                                            <input type="number" name="rank" class="form-control form-control-sm" value="{{ old('rank', $shortlist?->rank) }}" min="1">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small mb-1">Feedback (optional)</label>
                                            <textarea name="feedback" class="form-control form-control-sm" rows="3">{{ old('feedback', $shortlist?->feedback) }}</textarea>
                                        </div>

                                        <button type="submit" class="btn btn-sm btn-dark">
                                            Save decision
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <div class="fw-semibold">Onboarded mentors</div>
                                        <div class="small text-muted">Added for this shortlisted prototype.</div>
                                    </div>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#addMentorModal"
                                        @if(! $canAddMentors) disabled @endif
                                    >
                                        Add mentor
                                    </button>
                                </div>

                                @if($mentors->isEmpty())
                                    <div class="alert alert-light border mb-0">
                                        No mentors added yet.
                                    </div>
                                @else
                                    <div class="row g-2">
                                        @foreach($mentors as $mentor)
                                            <div class="col-12">
                                                <div class="border rounded p-2 d-flex gap-2 align-items-start">
                                                    <div style="width: 56px; height: 56px; border-radius: 10px; background: #f3f4f6; overflow: hidden;">
                                                        @if(filled($mentor->picture_url))
                                                            <img src="{{ $mentor->picture_url }}" alt="{{ $mentor->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                        @else
                                                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">—</div>
                                                        @endif
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="fw-semibold">{{ $mentor->name }}</div>
                                                        <div class="small text-muted">
                                                            {{ filled($mentor->email) ? $mentor->email : '—' }}
                                                            @if(filled($mentor->phone))
                                                                • <x-whatsapp-phone :phone="$mentor->phone" />
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addMentorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Add mentor</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form
                    method="POST"
                    action="{{ route('admin.competitions.prototype-shortlisting.mentors.store', [$competition, $submission]) }}"
                >
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Picture URL (optional)</label>
                                <input
                                    type="text"
                                    name="picture_url"
                                    class="form-control @error('picture_url') is-invalid @enderror"
                                    placeholder="Paste a link to mentor picture (optional)"
                                >
                                @error('picture_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="alert alert-info mt-3 mb-0">
                            Mentors will appear in <strong>Mentor onboarding</strong> phase.
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add mentor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>

