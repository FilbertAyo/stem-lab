<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Submissions: {{ $competition->name }}</h5>
                    <p class="text-muted mb-0">Review and judge competition submissions</p>
                </div>
                <div>
                    <a href="{{ route('admin.competitions.participants.index', $competition) }}" class="btn btn-info me-2">
                        <i class="ti ti-users"></i> View Participants
                    </a>
                    <a href="{{ route('admin.competitions.show', $competition) }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left"></i> Back to Competition
                    </a>
                </div>
            </div>

            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Stats Cards -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card bg-light-primary">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <span class="p-2 bg-primary rounded">
                                        <i class="ti ti-file-text text-white fs-4"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="mb-0">{{ $stats['total'] }}</h4>
                                    <p class="mb-0 text-muted small">Total Submissions</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light-warning">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <span class="p-2 bg-warning rounded">
                                        <i class="ti ti-clock text-white fs-4"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="mb-0">{{ $stats['pending'] }}</h4>
                                    <p class="mb-0 text-muted small">Pending Review</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light-success">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <span class="p-2 bg-success rounded">
                                        <i class="ti ti-check text-white fs-4"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="mb-0">{{ $stats['reviewed'] }}</h4>
                                    <p class="mb-0 text-muted small">Reviewed</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-3">
                <div class="card-body py-3">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Title, description..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                                <option value="under_review" {{ request('status') == 'under_review' ? 'selected' : '' }}>Under Review</option>
                                <option value="reviewed" {{ request('status') == 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                                <option value="revision_requested" {{ request('status') == 'revision_requested' ? 'selected' : '' }}>Revision Requested</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Judged</label>
                            <select name="judged" class="form-select">
                                <option value="">All</option>
                                <option value="yes" {{ request('judged') == 'yes' ? 'selected' : '' }}>Judged</option>
                                <option value="no" {{ request('judged') == 'no' ? 'selected' : '' }}>Not Judged</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="ti ti-filter"></i> Filter
                            </button>
                            <a href="{{ route('admin.competitions.submissions.index', $competition) }}" class="btn btn-outline-secondary">
                                <i class="ti ti-x"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Submissions Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Submission</th>
                                    <th>Team / School</th>
                                    <th class="text-center">Attachments</th>
                                    <th class="text-center">Score</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                    <th class="text-center" style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($submissions as $submission)
                                    <tr>
                                        <td>{{ $loop->iteration + ($submissions->currentPage() - 1) * $submissions->perPage() }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $submission->title }}</div>
                                            <small class="text-muted">{{ Str::limit($submission->description, 60) }}</small>
                                        </td>
                                        <td>
                                            <div>{{ $submission->participant->team_name ?: 'Team #'.$submission->participant->id }}</div>
                                            <small class="text-muted">{{ $submission->participant->teacherClub->school->name ?? '—' }}</small>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $pdfs = $submission->attachments->where('type', 'pdf')->count();
                                                $images = $submission->attachments->where('type', 'image')->count();
                                            @endphp
                                            @if($pdfs > 0)
                                                <span class="badge bg-danger" title="PDFs"><i class="ti ti-file-type-pdf"></i> {{ $pdfs }}</span>
                                            @endif
                                            @if($images > 0)
                                                <span class="badge bg-success" title="Images"><i class="ti ti-photo"></i> {{ $images }}</span>
                                            @endif
                                            @if($pdfs == 0 && $images == 0)
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($submission->score !== null)
                                                <span class="badge bg-primary fs-6">{{ number_format($submission->score, 1) }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $submission->status_badge }}">
                                                {{ ucfirst(str_replace('_', ' ', $submission->status)) }}
                                            </span>
                                            @if($submission->judge)
                                                <br><small class="text-muted">by {{ $submission->judge->name }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $submission->submitted_at?->format('d M Y, H:i') ?? '—' }}</small>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.competitions.submissions.show', [$competition, $submission]) }}" 
                                               class="btn btn-sm btn-primary">
                                                <i class="ti ti-eye"></i> Review
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            No submissions found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($submissions->hasPages())
                        <div class="mt-3">
                            {{ $submissions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

