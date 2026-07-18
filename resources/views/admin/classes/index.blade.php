<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Classes Management</h5>
                </div>
                <a href="{{ route('admin.classes.export') }}" class="btn btn-sm btn-danger">
                    <i class="ti ti-download"></i> Download PDF
                </a>
                <!-- <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createClassModal">
                    <i class="ti ti-plus"></i> Add Class
                </button> -->
            </div>

           

            <div class="card tbl-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <!-- <th>Last Updated</th> -->
                                    <!-- <th class="text-center" style="width: 160px;">Actions</th> -->
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($classes as $class)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="fw-semibold">{{ $class->name }}</td>
                                        <td>{{ $class->description ? \Illuminate\Support\Str::limit($class->description, 70) : '—' }}</td>
                                        <td>
                                            <span class="badge {{ $class->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $class->is_active ? 'Active' : 'Disabled' }}
                                            </span>
                                        </td>
                                        <!-- <td>{{ $class->updated_at?->format('d M Y, H:i') }}</td> -->
                                        <!-- <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <button
                                                    class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editClassModal"
                                                    data-action="{{ route('admin.classes.update', $class) }}"
                                                    data-id="{{ $class->id }}"
                                                    data-name="{{ $class->name }}"
                                                    data-description="{{ $class->description }}"
                                                    data-active="{{ $class->is_active ? '1' : '0' }}"
                                                >
                                                    <i class="ti ti-edit"></i>
                                                </button>
                                                <button
                                                    class="btn btn-sm {{ $class->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#statusModal"
                                                    data-action="{{ route('admin.classes.toggle-status', $class) }}"
                                                    data-name="{{ $class->name }}"
                                                    data-status="{{ $class->is_active ? 'disable' : 'enable' }}"
                                                >
                                                    <i class="ti {{ $class->is_active ? 'ti-ban' : 'ti-check' }}"></i>
                                                </button>
                                            </div>
                                        </td> -->
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            No classes found. Use the "Add Class" button to create the first one.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createClassModal" tabindex="-1" aria-labelledby="createClassModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createClassModalLabel">Add Class</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.classes.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="className" class="form-label">Class Name</label>
                            <input type="text" name="name" id="className" class="form-control" value="{{ old('name') }}" placeholder="e.g. Form 1" required>
                            @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-0">
                            <label for="classDescription" class="form-label">Description</label>
                            <textarea name="description" id="classDescription" rows="3" class="form-control" placeholder="Short description (optional)">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Class</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editClassModal" tabindex="-1" aria-labelledby="editClassModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editClassModalLabel">Edit Class</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="editClassForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editClassName" class="form-label">Class Name</label>
                            <input type="text" name="name" id="editClassName" class="form-control" required>
                            @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="editClassDescription" class="form-label">Description</label>
                            <textarea name="description" id="editClassDescription" rows="3" class="form-control"></textarea>
                            @error('description')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="editClassActiveToggle">
                            <label class="form-check-label" for="editClassActiveToggle">Active</label>
                            <input type="hidden" name="is_active" id="editClassIsActive" value="1">
                            @error('is_active')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Class</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Disable/Enable Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel">Update Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="statusForm">
                    @csrf
                    <div class="modal-body">
                        <p class="mb-0" id="statusModalMessage"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning" id="statusModalSubmit">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const editModal = document.getElementById('editClassModal');
                const editForm = document.getElementById('editClassForm');
                const editName = document.getElementById('editClassName');
                const editDescription = document.getElementById('editClassDescription');
                const editToggle = document.getElementById('editClassActiveToggle');
                const editStatusInput = document.getElementById('editClassIsActive');

                editModal?.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const action = button.getAttribute('data-action');
                    const name = button.getAttribute('data-name');
                    const description = button.getAttribute('data-description') || '';
                    const isActive = button.getAttribute('data-active') === '1';

                    editForm.setAttribute('action', action);
                    editName.value = name;
                    editDescription.value = description;
                    editToggle.checked = isActive;
                    editStatusInput.value = isActive ? '1' : '0';
                });

                editToggle?.addEventListener('change', function () {
                    editStatusInput.value = this.checked ? '1' : '0';
                });

                const statusModal = document.getElementById('statusModal');
                const statusForm = document.getElementById('statusForm');
                const statusMessage = document.getElementById('statusModalMessage');
                const statusSubmit = document.getElementById('statusModalSubmit');

                statusModal?.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const action = button.getAttribute('data-action');
                    const name = button.getAttribute('data-name');
                    const status = button.getAttribute('data-status');

                    statusForm.setAttribute('action', action);
                    if (status === 'disable') {
                        statusMessage.textContent = `Disable ${name}? Students will no longer see it until re-enabled.`;
                        statusSubmit.classList.remove('btn-success');
                        statusSubmit.classList.add('btn-warning');
                        statusSubmit.textContent = 'Disable';
                    } else {
                        statusMessage.textContent = `Enable ${name}? It will become available immediately.`;
                        statusSubmit.classList.remove('btn-warning');
                        statusSubmit.classList.add('btn-success');
                        statusSubmit.textContent = 'Enable';
                    }
                });
            });
        </script>
    @endpush
</x-admin-layout>
