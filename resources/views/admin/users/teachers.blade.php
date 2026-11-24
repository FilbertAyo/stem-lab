<x-admin-layout>

    <div class="row">
        <div class="col-md-12 col-xl-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Teachers Management</h5>
            </div>

            @include('admin.users.partials.download-list', [
                'listRoute' => 'admin.users.teachers',
                'exportRoute' => 'admin.users.teachers.export',
                'entityLabel' => 'Teachers',
            ])

            <div class="card tbl-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
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
                                        <td>{{ $user->email }}</td>
                                        <td><x-whatsapp-phone :phone="$user->phone" /></td>
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
                                            <x-admin.table-actions :actions="[
                                                [
                                                    'type' => 'link',
                                                    'label' => 'View',
                                                    'icon' => 'ti ti-eye',
                                                    'url' => route('admin.users.show', $user),
                                                ],
                                                [
                                                    'type' => 'button',
                                                    'label' => 'Edit',
                                                    'icon' => 'ti ti-edit',
                                                    'attributes' => [
                                                        'data-bs-toggle' => 'modal',
                                                        'data-bs-target' => '#editTeacherModal',
                                                        'data-user-route-key' => $user->getRouteKey(),
                                                        'data-user-name' => $user->name,
                                                        'data-user-email' => $user->email,
                                                        'data-user-phone' => $user->phone ?? '',
                                                    ],
                                                ],
                                            ]" />
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No teachers found.</td>
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
                                    <div class="text-muted small">No teachers match your search.</div>
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

    <!-- Edit Teacher Modal -->
    <div class="modal fade" id="editTeacherModal" tabindex="-1" aria-labelledby="editTeacherModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTeacherModalLabel">Edit Teacher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editTeacherForm" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_form" value="edit_teacher">
                    <input type="hidden" name="edit_user_id" id="edit_teacher_user_id" value="{{ old('edit_user_id') }}">
                    @if(request()->filled('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif
                    @if(request()->filled('from_date'))
                        <input type="hidden" name="from_date" value="{{ request('from_date') }}">
                    @endif
                    @if(request()->filled('to_date'))
                        <input type="hidden" name="to_date" value="{{ request('to_date') }}">
                    @endif
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_teacher_name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                id="edit_teacher_name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="edit_teacher_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                id="edit_teacher_email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="edit_teacher_phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                id="edit_teacher_phone" name="phone" value="{{ old('phone') }}"
                                placeholder="+255XX or 07XX">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="edit_teacher_password" class="form-label">
                                Password <small class="text-muted">(Leave blank to keep current password)</small>
                            </label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                id="edit_teacher_password" name="password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Teacher</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function setEditTeacherForm(userRouteKey, userName, userEmail, userPhone) {
            const form = document.getElementById('editTeacherForm');
            if (!form || !userRouteKey) {
                return;
            }

            form.action = @json(route('admin.users.update', ['user' => '__ROUTE_KEY__'])).replace('__ROUTE_KEY__', userRouteKey);
            document.getElementById('edit_teacher_user_id').value = userRouteKey;
            document.getElementById('edit_teacher_name').value = userName || '';
            document.getElementById('edit_teacher_email').value = userEmail || '';
            document.getElementById('edit_teacher_phone').value = userPhone || '';
            document.getElementById('edit_teacher_password').value = '';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const editModal = document.getElementById('editTeacherModal');

            if (editModal) {
                editModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    setEditTeacherForm(
                        button.getAttribute('data-user-route-key'),
                        button.getAttribute('data-user-name'),
                        button.getAttribute('data-user-email'),
                        button.getAttribute('data-user-phone')
                    );
                });
            }

            @if($errors->any() && old('_form') === 'edit_teacher' && old('edit_user_id'))
                setEditTeacherForm(
                    @json(old('edit_user_id')),
                    @json(old('name')),
                    @json(old('email')),
                    @json(old('phone'))
                );
                if (editModal) {
                    bootstrap.Modal.getOrCreateInstance(editModal).show();
                }
            @endif
        });
    </script>
    @endpush

</x-admin-layout>
