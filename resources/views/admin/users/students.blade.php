<x-admin-layout>

    <div class="row">
        <div class="col-md-12 col-xl-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Students & Teachers Management</h5>
                <div class="d-flex gap-2">
                    <form method="GET" action="{{ route('admin.users.students') }}" class="d-flex gap-2">
                        <select name="role" class="form-select" style="width: auto;" onchange="this.form.submit()">
                            <option value="all" {{ $selectedRole === 'all' ? 'selected' : '' }}>All Users</option>
                            <option value="student" {{ $selectedRole === 'student' ? 'selected' : '' }}>Students</option>
                            <option value="teacher" {{ $selectedRole === 'teacher' ? 'selected' : '' }}>Teachers</option>
                        </select>
                    </form>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card tbl-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Email Verified</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $index => $user)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @if($user->role === 'teacher')
                                                <span class="badge bg-info">{{ ucfirst($user->role) }}</span>
                                            @else
                                                <span class="badge bg-success">{{ ucfirst($user->role) }}</span>
                                            @endif
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
                                        <td>{{ $user->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info"
                                                data-bs-toggle="modal"
                                                data-bs-target="#viewUserModal"
                                                data-user-id="{{ $user->id }}"
                                                data-user-name="{{ $user->name }}"
                                                data-user-email="{{ $user->email }}"
                                                data-user-role="{{ $user->role }}"
                                                data-user-status="{{ $user->is_active ?? true ? 'Active' : 'Disabled' }}"
                                                data-user-email-verified="{{ $user->email_verified_at ? 'Yes' : 'No' }}"
                                                data-user-email-verified-at="{{ $user->email_verified_at ? $user->email_verified_at->format('M d, Y h:i A') : 'Not Verified' }}"
                                                data-user-created-at="{{ $user->created_at->format('M d, Y h:i A') }}"
                                                data-user-updated-at="{{ $user->updated_at->format('M d, Y h:i A') }}"
                                                title="View Details">
                                                <i class="ti ti-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No students or teachers found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View User Details Modal -->
    <div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewUserModalLabel">User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">User ID</label>
                            <p class="mb-0" id="view_user_id">-</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Name</label>
                            <p class="mb-0" id="view_user_name">-</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <p class="mb-0" id="view_user_email">-</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Role</label>
                            <p class="mb-0" id="view_user_role">-</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <p class="mb-0" id="view_user_status">-</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Email Verified</label>
                            <p class="mb-0" id="view_user_email_verified">-</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Email Verified At</label>
                            <p class="mb-0" id="view_user_email_verified_at">-</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Created At</label>
                            <p class="mb-0" id="view_user_created_at">-</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Last Updated</label>
                            <p class="mb-0" id="view_user_updated_at">-</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Handle view modal data
        document.addEventListener('DOMContentLoaded', function() {
            const viewModal = document.getElementById('viewUserModal');
            if (viewModal) {
                viewModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;

                    // Get all data attributes
                    const userId = button.getAttribute('data-user-id');
                    const userName = button.getAttribute('data-user-name');
                    const userEmail = button.getAttribute('data-user-email');
                    const userRole = button.getAttribute('data-user-role');
                    const userStatus = button.getAttribute('data-user-status');
                    const userEmailVerified = button.getAttribute('data-user-email-verified');
                    const userEmailVerifiedAt = button.getAttribute('data-user-email-verified-at');
                    const userCreatedAt = button.getAttribute('data-user-created-at');
                    const userUpdatedAt = button.getAttribute('data-user-updated-at');

                    // Set role badge
                    let roleBadge = '';
                    if (userRole === 'teacher') {
                        roleBadge = '<span class="badge bg-info">' + userRole.charAt(0).toUpperCase() + userRole.slice(1) + '</span>';
                    } else {
                        roleBadge = '<span class="badge bg-success">' + userRole.charAt(0).toUpperCase() + userRole.slice(1) + '</span>';
                    }

                    // Set status badge
                    let statusBadge = '';
                    if (userStatus === 'Active') {
                        statusBadge = '<span class="badge bg-success">Active</span>';
                    } else {
                        statusBadge = '<span class="badge bg-danger">Disabled</span>';
                    }

                    // Set email verified badge
                    let emailVerifiedBadge = '';
                    if (userEmailVerified === 'Yes') {
                        emailVerifiedBadge = '<span class="badge bg-success">Verified</span>';
                    } else {
                        emailVerifiedBadge = '<span class="badge bg-warning">Not Verified</span>';
                    }

                    // Populate modal fields
                    document.getElementById('view_user_id').textContent = userId;
                    document.getElementById('view_user_name').textContent = userName;
                    document.getElementById('view_user_email').textContent = userEmail;
                    document.getElementById('view_user_role').innerHTML = roleBadge;
                    document.getElementById('view_user_status').innerHTML = statusBadge;
                    document.getElementById('view_user_email_verified').innerHTML = emailVerifiedBadge;
                    document.getElementById('view_user_email_verified_at').textContent = userEmailVerifiedAt;
                    document.getElementById('view_user_created_at').textContent = userCreatedAt;
                    document.getElementById('view_user_updated_at').textContent = userUpdatedAt;
                });
            }
        });
    </script>
    @endpush

</x-admin-layout>
