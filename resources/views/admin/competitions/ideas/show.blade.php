@php
    $registration = $idea->registration;
    $school = $registration?->school;
@endphp

<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a href="{{ route('admin.competitions.index') }}">Competitions</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.competitions.show', $competition) }}">{{ $competition->name }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.competitions.ideas.index', $competition) }}">Ideas</a></li>
                            <li class="breadcrumb-item active">{{ $idea->problem_title ?: 'Idea' }}</li>
                        </ol>
                    </nav>
                    <h5 class="mb-0">{{ $idea->problem_title ?: 'Untitled idea' }}</h5>
                    <p class="text-muted small mb-0">
                        <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $idea->status)) }}</span>
                        @if($idea->ulid)
                            <span class="ms-2 font-monospace">ULID {{ $idea->ulid }}</span>
                        @endif
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.competitions.ideas.index', $competition) }}" class="btn btn-outline-secondary">Back to list</a>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scoreFromShowModal">Score</button>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#shortlistFromShowModal">Shortlist</button>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header py-2"><strong>School &amp; registration</strong></div>
                        <div class="card-body small">
                            <dl class="row mb-0">
                                <dt class="col-sm-4 text-muted fw-normal">School</dt>
                                <dd class="col-sm-8 mb-2">{{ $registration?->school_name ?? $school?->name ?? '—' }}</dd>
                                <dt class="col-sm-4 text-muted fw-normal">Teacher</dt>
                                <dd class="col-sm-8 mb-2">{{ $registration?->teacher_name ?? '—' }}</dd>
                                <dt class="col-sm-4 text-muted fw-normal">Teacher email</dt>
                                <dd class="col-sm-8 mb-2">{{ $registration?->teacher_email ?? '—' }}</dd>
                                <dt class="col-sm-4 text-muted fw-normal">Teacher phone</dt>
                                <dd class="col-sm-8 mb-2">{{ $registration?->teacher_phone ?? '—' }}</dd>
                                <dt class="col-sm-4 text-muted fw-normal">Registration region</dt>
                                <dd class="col-sm-8 mb-2">{{ $registration?->region?->name ?? '—' }}</dd>
                                <dt class="col-sm-4 text-muted fw-normal">Registration status</dt>
                                <dd class="col-sm-8 mb-2">{{ $registration ? ucfirst($registration->status) : '—' }}</dd>
                                @if($school)
                                    <dt class="col-sm-4 text-muted fw-normal">School address</dt>
                                    <dd class="col-sm-8 mb-2 text-break">{{ $school->address ?: '—' }}</dd>
                                    <dt class="col-sm-4 text-muted fw-normal">District / ward</dt>
                                    <dd class="col-sm-8 mb-2">{{ $school->district ?? '—' }} / {{ $school->ward ?? '—' }}</dd>
                                    <dt class="col-sm-4 text-muted fw-normal">School region</dt>
                                    <dd class="col-sm-8 mb-0">{{ $school->regionModel?->name ?? $school->region ?? '—' }}</dd>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header py-2"><strong>Idea meta</strong></div>
                        <div class="card-body small">
                            <dl class="row mb-0">
                                <dt class="col-sm-4 text-muted fw-normal">STEM discipline</dt>
                                <dd class="col-sm-8 mb-2">{{ $idea->stem_category ?: '—' }}</dd>
                                <dt class="col-sm-4 text-muted fw-normal">Community region</dt>
                                <dd class="col-sm-8 mb-2">{{ $idea->communityRegion?->name ?? '—' }}</dd>
                                <dt class="col-sm-4 text-muted fw-normal">Community / location</dt>
                                <dd class="col-sm-8 mb-2">{{ $idea->community_location ?: '—' }}</dd>
                                <dt class="col-sm-4 text-muted fw-normal">Submitted</dt>
                                <dd class="col-sm-8 mb-2">{{ $idea->submitted_at?->timezone(config('app.timezone'))->format('j M Y, H:i') ?? '—' }}</dd>
                                <dt class="col-sm-4 text-muted fw-normal">Shortlist</dt>
                                <dd class="col-sm-8 mb-2">
                                    @if($idea->shortlist)
                                        <span class="badge bg-{{ $idea->shortlist->status === 'shortlisted' ? 'success' : ($idea->shortlist->status === 'waitlist' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($idea->shortlist->status) }}
                                        </span>
                                        @if($idea->shortlist->rank)
                                            <span class="text-muted">Rank {{ $idea->shortlist->rank }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">Pending</span>
                                    @endif
                                </dd>
                                @if($idea->reopened_at)
                                    <dt class="col-sm-4 text-muted fw-normal">Reopened</dt>
                                    <dd class="col-sm-8 mb-0">
                                        {{ $idea->reopened_at->timezone(config('app.timezone'))->format('j M Y, H:i') }}
                                        @if($idea->reopenedBy)
                                            by {{ $idea->reopenedBy->name }}
                                        @endif
                                    </dd>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header py-2"><strong>Problem statement</strong></div>
                <div class="card-body">
                    <div class="small text-body-secondary">{!! nl2br(e($idea->problem_statement ?? '')) ?: '—' !!}</div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header py-2"><strong>Proposed solution</strong></div>
                <div class="card-body">
                    <div class="small text-body-secondary">{!! nl2br(e($idea->proposed_solution ?? '')) ?: '—' !!}</div>
                </div>
            </div>

            @if(filled($idea->supporting_notes))
                <div class="card mt-3">
                    <div class="card-header py-2"><strong>Supporting notes</strong></div>
                    <div class="card-body">
                        <div class="small text-body-secondary">{!! nl2br(e($idea->supporting_notes)) !!}</div>
                    </div>
                </div>
            @endif

            @if($idea->attachments_meta && count((array) $idea->attachments_meta) > 0)
                <div class="card mt-3">
                    <div class="card-header py-2"><strong>Attachments (meta)</strong></div>
                    <div class="card-body">
                        <pre class="small mb-0 bg-light p-3 rounded border">{{ json_encode($idea->attachments_meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
            @endif

            <div class="card mt-3">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <strong>Student representatives</strong>
                    <span class="badge bg-light text-dark">{{ $registration?->representativeStudents->count() ?? 0 }}</span>
                </div>
                <div class="card-body p-0">
                    @if($registration && $registration->representativeStudents->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Name</th>
                                        <th>Gender</th>
                                        <th>Class</th>
                                        <th>Role</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($registration->representativeStudents as $rs)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
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
                        <p class="text-muted small mb-0 p-3">No representatives linked.</p>
                    @endif
                </div>
            </div>

            <div class="card mt-3 mb-4">
                <div class="card-header py-2"><strong>Judge scores</strong></div>
                <div class="card-body p-0">
                    @if($idea->scores->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Judge</th>
                                        <th>STEM</th>
                                        <th>Potential</th>
                                        <th>Relevance</th>
                                        <th>Creativity</th>
                                        <th>Total</th>
                                        <th>Feedback</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($idea->scores as $score)
                                        <tr>
                                            <td>{{ $score->judge?->name ?? '—' }}</td>
                                            <td>{{ $score->stem_score }}</td>
                                            <td>{{ $score->product_potential_score }}</td>
                                            <td>{{ $score->community_relevance_score }}</td>
                                            <td>{{ $score->creativity_score }}</td>
                                            <td class="fw-semibold">{{ $score->total_score }}</td>
                                            <td class="small text-break" style="max-width: 14rem;">{{ $score->feedback ?: '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted small mb-0 p-3">No scores yet.</p>
                    @endif
                </div>
            </div>

            {{-- Same actions as list: score + shortlist modals --}}
            <div class="modal fade" id="scoreFromShowModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Score idea</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="{{ route('admin.competitions.ideas.score', [$competition, $idea]) }}">
                            @csrf
                            <div class="modal-body">
                                <div class="row g-2">
                                    
                                </div>
                                <div class="row g-2 mt-1">
                                    <div class="col-md-4">
                                        <label class="form-label">STEM</label>
                                        <select name="stem_score" class="form-select" required>
                                            @foreach([1,2,3,4,5] as $v)
                                                <option value="{{ $v }}">{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Potential</label>
                                        <select name="product_potential_score" class="form-select" required>
                                            @foreach([1,2,3,4,5] as $v)
                                                <option value="{{ $v }}">{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Relevance</label>
                                        <select name="community_relevance_score" class="form-select" required>
                                            @foreach([1,2,3,4,5] as $v)
                                                <option value="{{ $v }}">{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Creativity</label>
                                        <select name="creativity_score" class="form-select" required>
                                            @foreach([1,2,3,4,5] as $v)
                                                <option value="{{ $v }}">{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Feedback</label>
                                        <textarea class="form-control" name="feedback" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-primary" type="submit">Save score</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="shortlistFromShowModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Shortlist decision</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="{{ route('admin.competitions.ideas.shortlist', [$competition, $idea]) }}">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Decision</label>
                                    <select name="status" class="form-select" required>
                                        <option value="shortlisted" @selected($idea->shortlist?->status === 'shortlisted')>Shortlisted</option>
                                        <option value="waitlist" @selected($idea->shortlist?->status === 'waitlist')>Waitlist</option>
                                        <option value="rejected" @selected($idea->shortlist?->status === 'rejected')>Rejected</option>
                                    </select>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">Regional rank (optional)</label>
                                    <input class="form-control" type="number" name="rank" min="1" value="{{ $idea->shortlist?->rank }}">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-success" type="submit">Save decision</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
