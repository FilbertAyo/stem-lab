<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">All Competition Participants</h5>
                    <p class="text-muted mb-0">Manage participants across all competitions</p>
                </div>
                <a href="{{ route('admin.competitions.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left"></i> Back to Competitions
                </a>
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
                        <div class="col-md-3">
                            
                            <input type="text" name="search" class="form-control" placeholder="Team, Teacher, School, Competition..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="competition_id" class="form-select">
                                <option value="">All Competitions</option>
                                @foreach($competitions as $competition)
                                    <option value="{{ $competition->id }}" {{ request('competition_id') == $competition->id ? 'selected' : '' }}>
                                        {{ $competition->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="disqualified" {{ request('status') == 'disqualified' ? 'selected' : '' }}>Disqualified</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="ti ti-filter"></i> Filter
                            </button>
                            <a href="{{ route('admin.competitions.all-participants') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-x"></i> Clear
                            </a>
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
                                    <th>Competition</th>
                                    <th>Team / School</th>
                                    <th>Teacher</th>
                                    <th class="text-center">Students</th>
                                    <th class="text-center">Submissions</th>
                                    <th>Status</th>
                                    <th>Registered</th>
                                    <th class="text-center" style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($participants as $participant)
                                    <tr>
                                        <td>{{ $loop->iteration + ($participants->currentPage() - 1) * $participants->perPage() }}</td>
                                        <td>
                                            <a href="{{ route('admin.competitions.show', $participant->competition) }}" class="text-primary fw-semibold">
                                                {{ Str::limit($participant->competition->name, 25) }}
                                            </a>
                                            <br><small class="text-muted">{{ $participant->competition->club->name }}</small>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $participant->team_name ?: 'Team #'.$participant->id }}</div>
                                            <small class="text-muted">{{ $participant->teacherClub->school->name ?? '—' }}</small>
                                        </td>
                                        <td>{{ $participant->teacherClub->user->name ?? '—' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $participant->participantStudents->count() }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary">{{ $participant->submissions->count() }}</span>
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
                                                <a href="{{ route('admin.competitions.participants.show', [$participant->competition, $participant]) }}" 
                                                   class="btn btn-outline-info" title="View Details">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                @if($participant->status === 'pending')
                                                    <form action="{{ route('admin.competitions.participants.approve', [$participant->competition, $participant]) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-success" title="Approve">
                                                            <i class="ti ti-check"></i>
                                                        </button>
                                                    </form>
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
        </div>
    </div>
</x-admin-layout>

