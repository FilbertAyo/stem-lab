<x-admin-layout>

    <div class="row">
        <div class="col-md-12 col-xl-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Students Management</h5>
            </div>

            @include('admin.users.partials.download-list', [
                'listRoute' => 'admin.users.students',
                'exportRoute' => 'admin.users.students.export',
                'entityLabel' => 'Students',
            ])

            <div class="card tbl-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>School</th>
                                    <th>Plan</th>
                                    <th>Status</th>
                                    <th>Email Verified</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $index => $user)
                                    <tr>
                                        <td>{{ $users->firstItem() + $index }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email ?: ($user->login_id ?: '—') }}</td>
                                        <td>{{ $user->resolvedSchoolLabel() }}</td>
                                        <td>
                                            <span class="badge {{ $user->platformPlanBadgeClass() }}">{{ $user->adminPlanLabel() }}</span>
                                        </td>
                                        <td>
                                            @if($user->is_active ?? true)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Disabled</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->email_verified_at)
                                                <span class="badge bg-success">Verified</span>
                                            @else
                                                <span class="badge bg-warning">Not Verified</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-info" title="View Details">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No students found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($users->hasPages() || request()->filled('q') || request()->filled('from_date') || request()->filled('to_date'))
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                @if($users->total() > 0)
                                    <div class="text-muted small">
                                        Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} results
                                    </div>
                                @else
                                    <div class="text-muted small">No students match your search.</div>
                                @endif
                                @if($users->hasPages())
                                    <div>
                                        {{ $users->links('pagination::bootstrap-5') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-admin-layout>
