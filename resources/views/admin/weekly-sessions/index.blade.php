<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Weekly Sessions</h5>
                    <p class="text-muted mb-0">Manage weekly club-based learning sessions</p>
                </div>
                <a href="{{ route('admin.weekly-sessions.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus"></i> Create Session
                </a>
            </div>

           

            <!-- Filters -->
            <div class="card mb-3">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('admin.weekly-sessions.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control" placeholder="Search by title, description, or club..." value="{{ request('search') }}">
                        </div>
                     
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="start_date" class="form-control" placeholder="Start Date" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="end_date" class="form-control" placeholder="End Date" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-outline-primary w-100">
                                <i class="ti ti-filter"></i> Filter
                            </button>
                        </div>
                        <div class="col-md-1">
                            <a href="{{ route('admin.weekly-sessions.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="ti ti-x"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card tbl-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Club</th>
                                    <th>Subject</th>
                                    <th>Classes</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Logs</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sessions as $session)
                                    <tr>
                                        <td>{{ $loop->iteration + ($sessions->currentPage() - 1) * $sessions->perPage() }}</td>
                                        <td>
                                            <div>
                                                <a href="{{ route('admin.weekly-sessions.show', $session) }}" class="fw-semibold text-dark">
                                                    {{ $session->title }}
                                                </a>
                                                @if($session->description)
                                                    <div class="small text-muted">{{ \Illuminate\Support\Str::limit($session->description, 50) }}</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            {{ $session->club->name }}
                                        </td>
                                        <td>
                                            @if($session->subject)
                                                <span class="badge bg-secondary">{{ $session->subject->subject_name }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($session->classes->count() > 0)
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($session->classes->take(2) as $class)
                                                        <span class="badge bg-primary">{{ $class->name }}</span>
                                                    @endforeach
                                                    @if($session->classes->count() > 2)
                                                        <span class="badge bg-secondary">+{{ $session->classes->count() - 2 }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="small">
                                                <i class="ti ti-calendar-event"></i> {{ $session->session_date->format('d M Y') }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $session->status_badge }}">
                                                {{ ucfirst($session->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @include('admin.partials.ownership-meta', ['record' => $session, 'compact' => true, 'class' => ''])
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.weekly-sessions.show', $session) }}" 
                                                   class="btn btn-sm btn-outline-info" 
                                                   title="View">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.weekly-sessions.edit', $session) }}" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="Edit">
                                                    <i class="ti ti-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.weekly-sessions.destroy', $session) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this session?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            No weekly sessions found. Use the "Create Session" button to create the first one.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($sessions->hasPages())
                        <div class="mt-3">
                            {{ $sessions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
