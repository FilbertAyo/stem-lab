<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Registrations — {{ $competition->name }}</h5>
                    <p class="text-muted mb-0">Search, review details, then approve or reject school registration snapshots.</p>
                </div>
                <a href="{{ route('admin.competitions.show', $competition) }}" class="btn btn-outline-secondary">Back</a>
            </div>

            

            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.competitions.registrations.index', $competition) }}" class="row g-2 align-items-end">
                        <div class="col-md-5 col-lg-4">
                            <label class="form-label small mb-1" for="search">Search</label>
                            <input type="search" name="search" id="search" value="{{ request('search') }}" class="form-control" placeholder="School, teacher, email, phone, district…">
                        </div>
                        <div class="col-md-3 col-lg-2">
                            <label class="form-label small mb-1" for="status">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">All</option>
                                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                                <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                                <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                                <option value="ineligible" @selected(request('status') === 'ineligible')>Ineligible</option>
                            </select>
                        </div>
                        <div class="col-md-4 col-lg-3 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary">Apply</button>
                            <a href="{{ route('admin.competitions.registrations.index', $competition) }}" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>School</th>
                                    <th>Teacher</th>
                                    <th>Eligibility snapshot</th>
                                    <th>Representatives</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($registrations as $registration)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $registration->school_name }}</div>
                                            <small class="text-muted">{{ $registration->school_district ?? 'No district' }}</small>
                                        </td>
                                        <td>
                                            <div>{{ $registration->teacher_name }}</div>
                                            <small class="text-muted">{{ $registration->teacher_email }}</small>
                                        </td>
                                        <td>
                                            <div>{{ $registration->student_count_snapshot }} students</div>
                                            <small class="text-muted">{{ $registration->girls_ratio_snapshot }}% girls</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $registration->representativeStudents->count() }}/{{ max(1, (int) ($competition->representative_count ?? 10)) }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $badge = match ($registration->status) {
                                                    'approved' => 'success',
                                                    'rejected' => 'danger',
                                                    'ineligible' => 'dark',
                                                    default => 'warning',
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $badge }}">{{ ucfirst($registration->status) }}</span>
                                        </td>
                                        <td class="text-end text-nowrap">
                                            <a href="{{ route('admin.competitions.registrations.show', [$competition, $registration]) }}" class="btn btn-sm btn-outline-primary">View</a>
                                            @if($registration->status === 'pending')
                                                <form method="POST" action="{{ route('admin.competitions.registrations.approve', [$competition, $registration]) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                                </form>
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $registration->id }}">Reject</button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No registrations match your filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @foreach($registrations as $registration)
                        @if($registration->status === 'pending')
                            <div class="modal fade" id="rejectModal{{ $registration->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Reject registration</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="POST" action="{{ route('admin.competitions.registrations.reject', [$competition, $registration]) }}">
                                            @csrf
                                            <div class="modal-body">
                                                <p class="small text-muted mb-2">{{ $registration->school_name }}</p>
                                                <label class="form-label" for="rejection_reason_{{ $registration->id }}">Reason</label>
                                                <textarea class="form-control" name="rejection_reason" id="rejection_reason_{{ $registration->id }}" rows="4" required></textarea>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Reject</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach

                    <div class="mt-3">{{ $registrations->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
