<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Participants: {{ $competition->name }}</h5>
                    <p class="text-muted mb-0">Manage competition participants</p>
                </div>
                <div>
                    <a href="{{ route('admin.competitions.submissions.index', $competition) }}" class="btn btn-info me-2">
                        <i class="ti ti-file-text"></i> View Submissions
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

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Stats Cards -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="card bg-light-primary">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <span class="p-2 bg-primary rounded">
                                        <i class="ti ti-users text-white fs-4"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="mb-0">{{ $stats['total'] }}</h4>
                                    <p class="mb-0 text-muted small">Total Participants</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
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
                                    <p class="mb-0 text-muted small">Pending Approval</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light-success">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <span class="p-2 bg-success rounded">
                                        <i class="ti ti-check text-white fs-4"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="mb-0">{{ $stats['approved'] }}</h4>
                                    <p class="mb-0 text-muted small">Approved</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light-danger">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <span class="p-2 bg-danger rounded">
                                        <i class="ti ti-x text-white fs-4"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4 class="mb-0">{{ $stats['rejected'] }}</h4>
                                    <p class="mb-0 text-muted small">Rejected</p>
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
                        <div class="col-md-4">
                            <label class="form-label small">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Team, Teacher, School..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="disqualified" {{ request('status') == 'disqualified' ? 'selected' : '' }}>Disqualified</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="ti ti-filter"></i> Filter
                            </button>
                            <a href="{{ route('admin.competitions.participants.index', $competition) }}" class="btn btn-outline-secondary">
                                <i class="ti ti-x"></i> Clear
                            </a>
                        </div>
                        <div class="col-md-2 text-end">
                            @if($stats['pending'] > 0)
                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#bulkApproveModal">
                                    <i class="ti ti-checks"></i> Bulk Approve
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Participants Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Team / School</th>
                                    <th>Teacher</th>
                                    <th class="text-center">Students</th>
                                    <th class="text-center">Score</th>
                                    <th class="text-center">Rank</th>
                                    <th>Status</th>
                                    <th>Registered</th>
                                    <th class="text-center" style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($participants as $participant)
                                    <tr>
                                        <td>{{ $loop->iteration + ($participants->currentPage() - 1) * $participants->perPage() }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $participant->team_name ?: 'Team #'.$participant->id }}</div>
                                            <small class="text-muted">{{ $participant->teacherClub->school->name ?? '—' }}</small>
                                        </td>
                                        <td>{{ $participant->teacherClub->user->name ?? '—' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $participant->participantStudents->count() }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($participant->final_score !== null)
                                                <span class="fw-bold">{{ number_format($participant->final_score, 1) }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($participant->rank)
                                                <span class="badge bg-{{ $participant->rank <= 3 ? ['', 'warning', 'secondary', 'danger'][$participant->rank] : 'light text-dark' }}">
                                                    #{{ $participant->rank }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $participant->status_badge }}">
                                                {{ ucfirst($participant->status) }}
                                            </span>
                                            @if($participant->award)
                                                <br><small class="text-success"><i class="ti ti-trophy"></i> {{ $participant->award->title }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $participant->registered_at->format('d M Y, H:i') }}</small>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.competitions.participants.show', [$competition, $participant]) }}" 
                                                   class="btn btn-outline-info" title="View Details">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                @if($participant->status === 'pending')
                                                    <form action="{{ route('admin.competitions.participants.approve', [$competition, $participant]) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-success" title="Approve">
                                                            <i class="ti ti-check"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-outline-danger" title="Reject"
                                                            data-bs-toggle="modal" data-bs-target="#rejectModal"
                                                            data-action="{{ route('admin.competitions.participants.reject', [$competition, $participant]) }}"
                                                            data-team="{{ $participant->team_name ?: 'Team #'.$participant->id }}">
                                                        <i class="ti ti-x"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            No participants found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($participants->hasPages())
                        <div class="mt-3">
                            {{ $participants->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Auto-Assign Awards -->
            @if($competition->status === 'judging' || $competition->status === 'completed')
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Award Assignment</h6>
                                <p class="text-muted mb-0 small">Automatically assign awards based on final scores</p>
                            </div>
                            <form action="{{ route('admin.competitions.participants.auto-assign-awards', $competition) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-warning" onclick="return confirm('This will reassign all awards based on current scores. Continue?')">
                                    <i class="ti ti-trophy"></i> Auto-Assign Awards
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Participant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>You are rejecting <strong id="rejectTeamName"></strong></p>
                        <div class="mb-0">
                            <label class="form-label">Reason for rejection <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" class="form-control" rows="3" required 
                                      placeholder="Explain why this participant is being rejected..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Approve Modal -->
    <div class="modal fade" id="bulkApproveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Approve Participants</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.competitions.participants.bulk-approve', $competition) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Select participants to approve:</p>
                        <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                            @foreach($participants->where('status', 'pending') as $p)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="participant_ids[]" 
                                           value="{{ $p->id }}" id="bulk_{{ $p->id }}">
                                    <label class="form-check-label" for="bulk_{{ $p->id }}">
                                        {{ $p->team_name ?: 'Team #'.$p->id }} - {{ $p->teacherClub->school->name ?? '' }}
                                        <small class="text-muted">({{ $p->participantStudents->count() }} students)</small>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve Selected</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const rejectModal = document.getElementById('rejectModal');
                rejectModal?.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    document.getElementById('rejectForm').action = button.getAttribute('data-action');
                    document.getElementById('rejectTeamName').textContent = button.getAttribute('data-team');
                });
            });
        </script>
    @endpush
</x-admin-layout>

