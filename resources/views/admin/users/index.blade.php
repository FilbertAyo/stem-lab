<x-admin-layout>



    <div class="row">
        <div class="col-md-12 col-xl-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Admin Users Management</h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAdminModal">
                    <i class="ti ti-plus"></i> Create Admin User
                </button>
            </div>

           

            <div class="card mb-3">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-8 col-lg-6">
                            <label class="form-label small mb-1">Search</label>
                            <input type="search" name="q" class="form-control" placeholder="Search by name or email..."
                                value="{{ request('q') }}" autocomplete="off">
                        </div>
                        <div class="col-auto d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="ti ti-search"></i> Search
                            </button>
                            @if(request()->filled('q'))
                                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Clear</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

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
                                    <th>Alert categories</th>
                                    <th>Status</th>
                                    <th>Email Verified</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($adminUsers as $index => $user)
                                    <tr>
                                        <td>{{ $adminUsers->firstItem() + $index }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ ucfirst($user->role) }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $assigned = is_array($user->admin_alert_categories) ? $user->admin_alert_categories : [];
                                                $showAll = $assigned === [];
                                            @endphp
                                            @if($showAll)
                                                <span class="badge bg-secondary">All categories</span>
                                            @else
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($assigned as $categoryKey)
                                                        <span class="badge bg-info text-dark">{{ $alertCategories[$categoryKey]['label'] ?? $categoryKey }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->is_active)
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
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editAdminModal"
                                                    data-user-id="{{ $user->id }}"
                                                    data-user-route-key="{{ $user->getRouteKey() }}"
                                                    data-user-name="{{ $user->name }}"
                                                    data-user-email="{{ $user->email }}"
                                                    data-alert-categories='@json(is_array($user->admin_alert_categories) ? $user->admin_alert_categories : [])'
                                                    title="Edit">
                                                    <i class="ti ti-edit"></i>
                                                </button>
                                                <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to {{ $user->is_active ? 'disable' : 'enable' }} this admin user?');">
                                                    @csrf
                                                    @if(request()->filled('q'))
                                                        <input type="hidden" name="q" value="{{ request('q') }}">
                                                    @endif
                                                    <button type="submit" class="btn btn-sm {{ $user->is_active ? 'btn-warning' : 'btn-success' }}"
                                                        title="{{ $user->is_active ? 'Disable' : 'Enable' }}"
                                                        {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                                        <i class="ti ti-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No admin users found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($adminUsers->hasPages() || request()->filled('q'))
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                @if($adminUsers->total() > 0)
                                    <div class="text-muted small">
                                        Showing {{ $adminUsers->firstItem() }} to {{ $adminUsers->lastItem() }} of {{ $adminUsers->total() }} results
                                    </div>
                                @else
                                    <div class="text-muted small">No admin users match your search.</div>
                                @endif
                                @if($adminUsers->hasPages())
                                    <div>
                                        {{ $adminUsers->links('pagination::bootstrap-5') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Create Admin Modal -->
    <div class="modal fade" id="createAdminModal" tabindex="-1" aria-labelledby="createAdminModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createAdminModalLabel">Create Admin User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    @if(request()->filled('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="create_name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                id="create_name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="create_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                id="create_email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="create_password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                id="create_password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Alert categories <span class="text-danger">*</span></label>
                            <p class="text-muted small mb-2">Choose which email queues this admin should receive. One admin can own more than one.</p>
                            @foreach($alertCategories as $key => $category)
                                <div class="form-check">
                                    <input
                                        class="form-check-input @error('admin_alert_categories') is-invalid @enderror"
                                        type="checkbox"
                                        name="admin_alert_categories[]"
                                        value="{{ $key }}"
                                        id="create_alert_{{ $key }}"
                                        @checked(collect(old('admin_alert_categories', []))->contains($key))
                                    >
                                    <label class="form-check-label" for="create_alert_{{ $key }}">
                                        <strong>{{ $category['label'] }}</strong>
                                        <span class="d-block small text-muted">{{ $category['description'] }}</span>
                                    </label>
                                </div>
                            @endforeach
                            @error('admin_alert_categories')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Admin User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Admin Modal -->
    <div class="modal fade" id="editAdminModal" tabindex="-1" aria-labelledby="editAdminModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAdminModalLabel">Edit Admin User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editAdminForm" method="POST">
                    @csrf
                    @method('PUT')
                    @if(request()->filled('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                id="edit_name" name="name" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                id="edit_email" name="email" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="edit_password" class="form-label">Password <small class="text-muted">(Leave blank to keep current password)</small></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                id="edit_password" name="password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Alert categories <span class="text-danger">*</span></label>
                            <p class="text-muted small mb-2">Choose which email queues this admin should receive. One admin can own more than one.</p>
                            @foreach($alertCategories as $key => $category)
                                <div class="form-check">
                                    <input
                                        class="form-check-input js-edit-alert-category @error('admin_alert_categories') is-invalid @enderror"
                                        type="checkbox"
                                        name="admin_alert_categories[]"
                                        value="{{ $key }}"
                                        id="edit_alert_{{ $key }}"
                                    >
                                    <label class="form-check-label" for="edit_alert_{{ $key }}">
                                        <strong>{{ $category['label'] }}</strong>
                                        <span class="d-block small text-muted">{{ $category['description'] }}</span>
                                    </label>
                                </div>
                            @endforeach
                            @error('admin_alert_categories')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Admin User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editModal = document.getElementById('editAdminModal');
            if (editModal) {
                editModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const userRouteKey = button.getAttribute('data-user-route-key') || button.getAttribute('data-user-id');
                    const userName = button.getAttribute('data-user-name');
                    const userEmail = button.getAttribute('data-user-email');
                    let selectedCategories = [];
                    try {
                        selectedCategories = JSON.parse(button.getAttribute('data-alert-categories') || '[]');
                    } catch (e) {
                        selectedCategories = [];
                    }

                    const form = document.getElementById('editAdminForm');
                    form.action = '{{ route("admin.users.update", ":id") }}'.replace(':id', userRouteKey);

                    document.getElementById('edit_name').value = userName;
                    document.getElementById('edit_email').value = userEmail;
                    document.getElementById('edit_password').value = '';

                    form.querySelectorAll('.js-edit-alert-category').forEach(function(checkbox) {
                        const shouldCheck = selectedCategories.length === 0
                            ? true
                            : selectedCategories.includes(checkbox.value);
                        checkbox.checked = shouldCheck;
                    });
                });
            }
        });
    </script>
    @endpush

</x-admin-layout>
