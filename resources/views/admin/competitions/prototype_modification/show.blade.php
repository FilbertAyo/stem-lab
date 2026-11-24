<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Prototype Modification Review - {{ $competition->name }}</h5>
                    <p class="text-muted mb-0">School: {{ $modification->registration?->resolvedSchoolName() ?? '—' }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.competitions.prototype-modification.index', $competition) }}" class="btn btn-outline-secondary btn-sm">
                        Back to list
                    </a>
                </div>
            </div>

            

            @php
                $shortlist = $modification->modificationShortlist;
                $status = $shortlist?->status;
                $badge = $status === 'shortlisted'
                    ? 'success'
                    : ($status === 'waitlist'
                        ? 'warning'
                        : ($status === 'rejected' ? 'danger' : 'light'));

            @endphp

            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <span class="badge bg-{{ $badge }}">{{ $status ? ucfirst($status) : 'Pending' }}</span>
                        <span class="text-muted small">Submitted: {{ $modification->submitted_at?->format('d M Y, H:i') ?? '—' }}</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-lg-7">
                            <h6 class="text-uppercase text-muted small mb-3">Modification details</h6>
                            <dl class="row small mb-0">
                                <dt class="col-sm-4 text-muted fw-normal">Prototype title</dt>
                                <dd class="col-sm-8 mb-2">{{ $modification->prototype_title ?: '—' }}</dd>

                                <dt class="col-sm-4 text-muted fw-normal">What you built</dt>
                                <dd class="col-sm-8 mb-2">{!! nl2br(e($modification->prototype_concept ?: '—')) !!}</dd>

                                <dt class="col-sm-4 text-muted fw-normal">Why it matters</dt>
                                <dd class="col-sm-8 mb-2">{!! nl2br(e($modification->prototype_objectives ?: '—')) !!}</dd>

                                <dt class="col-sm-4 text-muted fw-normal">How it works</dt>
                                <dd class="col-sm-8 mb-2">{!! nl2br(e($modification->prototype_design_description ?: '—')) !!}</dd>

                                <dt class="col-sm-4 text-muted fw-normal">Materials</dt>
                                <dd class="col-sm-8 mb-2">{!! nl2br(e($modification->prototype_materials ?: '—')) !!}</dd>

                                <dt class="col-sm-4 text-muted fw-normal">Build process</dt>
                                <dd class="col-sm-8 mb-2">{!! nl2br(e($modification->prototype_build_process ?: '—')) !!}</dd>

                                <dt class="col-sm-4 text-muted fw-normal">Challenges</dt>
                                <dd class="col-sm-8 mb-2">{!! nl2br(e($modification->prototype_challenges ?: '—')) !!}</dd>

                                <dt class="col-sm-4 text-muted fw-normal">Test / results</dt>
                                <dd class="col-sm-8 mb-2">{!! nl2br(e($modification->prototype_test_results ?: '—')) !!}</dd>

                                <dt class="col-sm-4 text-muted fw-normal">Next steps</dt>
                                <dd class="col-sm-8 mb-2">{!! nl2br(e($modification->prototype_next_steps ?: '—')) !!}</dd>
                            </dl>
                        </div>

                        <div class="col-lg-5">
                            <h6 class="text-uppercase text-muted small mb-3">Prototype media</h6>
                            <div class="border rounded p-3 mb-3">
                                @include('partials.prototype-media-display', ['media' => $modification])
                            </div>

                            <h6 class="text-uppercase text-muted small mb-2">Shortlist decision</h6>
                            <form method="POST" action="{{ route('admin.competitions.prototype-modification.shortlist', [$competition, $modification]) }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label small mb-1">Status</label>
                                    <select name="status" class="form-select form-select-sm">
                                        @foreach(['shortlisted','waitlist','rejected'] as $opt)
                                            <option value="{{ $opt }}" @selected($opt === ($shortlist?->status ?? 'shortlisted'))>{{ ucfirst($opt) }}</option>
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
                                <button type="submit" class="btn btn-sm btn-dark">Save decision</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

