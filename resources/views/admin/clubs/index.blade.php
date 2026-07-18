<x-admin-layout>
        <div class="row">
            <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Clubs</h5>
                    <p class="text-muted mb-0">Manage School Clubs</p>
                </div>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createClubModal">
                    <i class="ti ti-plus"></i> Add Club
                </button>
            </div>

           

            <div class="card tbl-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Club</th>
                                    <th>Description</th>
                                    <th>Image</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 160px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($clubs as $club)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="fw-semibold">{{ $club->name }}</td>
                                        <td>{{ $club->description ? \Illuminate\Support\Str::limit($club->description, 70) : '—' }}</td>
                                        <td>
                                            @if ($club->image_path)
                                                <img src="{{ Storage::url($club->image_path) }}" alt="{{ $club->name }}" class="rounded" style="width: 64px; height: 64px; object-fit: cover;">
                                            @else
                                                <span class="text-muted">No image</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $club->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $club->is_active ? 'Active' : 'Disabled' }}
                                            </span>
                                        </td>
                                     <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a
                                                    href="{{ route('admin.clubs.show', $club) }}"
                                                    class="btn btn-sm btn-outline-info"
                                                    title="View Details"
                                                >
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                <!-- <button
                                                    class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editClubModal"
                                                    data-action="{{ route('admin.clubs.update', $club) }}"
                                                    data-name="{{ $club->name }}"
                                                    data-description="{{ $club->description }}"
                                                    data-active="{{ $club->is_active ? '1' : '0' }}"
                                                    data-image="{{ $club->image_path ? Storage::url($club->image_path) : '' }}"
                                                    title="Edit"
                                                >
                                                    <i class="ti ti-edit"></i>
                                                </button>
                                                <button
                                                    class="btn btn-sm {{ $club->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#clubStatusModal"
                                                    data-action="{{ route('admin.clubs.toggle-status', $club) }}"
                                                    data-name="{{ $club->name }}"
                                                    data-status="{{ $club->is_active ? 'disable' : 'enable' }}"
                                                    title="{{ $club->is_active ? 'Disable' : 'Enable' }}"
                                                >
                                                    <i class="ti {{ $club->is_active ? 'ti-ban' : 'ti-check' }}"></i>
                                                </button> -->
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            No clubs found. Use the "Add Club" button to create the first one.
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
    <div class="modal fade" id="createClubModal" tabindex="-1" aria-labelledby="createClubModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createClubModalLabel">Add Club</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.clubs.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="clubName" class="form-label">Club Name</label>
                            <input type="text" name="name" id="clubName" class="form-control" value="{{ old('name') }}" placeholder="e.g. Robotics Club" required>
                            @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="clubDescription" class="form-label">Description</label>
                            <textarea name="description" id="clubDescription" rows="3" class="form-control" placeholder="Short description">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="clubImage" class="form-label">Club Image</label>
                            <input type="file" name="image" id="clubImage" class="form-control" accept="image/*">
                            <small class="text-muted">Recommended 1:1 ratio, max 2MB.</small>
                            @error('image')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" id="clubActiveToggle" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="clubActiveToggle">Active</label>
                            @error('is_active')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Club</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editClubModal" tabindex="-1" aria-labelledby="editClubModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editClubModalLabel">Edit Club</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="editClubForm" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editClubName" class="form-label">Club Name</label>
                            <input type="text" name="name" id="editClubName" class="form-control" required>
                            @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="editClubDescription" class="form-label">Description</label>
                            <textarea name="description" id="editClubDescription" rows="3" class="form-control"></textarea>
                            @error('description')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="editClubImage" class="form-label">Club Image</label>
                            <input type="file" name="image" id="editClubImage" class="form-control" accept="image/*">
                            <small class="text-muted d-block mb-2">Leave empty to keep the current image.</small>
                            <div id="editClubImagePreview" class="border rounded p-2 d-flex align-items-center gap-2" style="min-height: 70px;">
                                <img src="" alt="Club image preview" class="rounded d-none" style="width: 64px; height: 64px; object-fit: cover;">
                                <span class="text-muted small mb-0">No image uploaded.</span>
                            </div>
                            @error('image')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" id="editClubIsActive" value="1">
                            <input class="form-check-input" type="checkbox" role="switch" id="editClubActiveToggle">
                            <label class="form-check-label" for="editClubActiveToggle">Active</label>
                            @error('is_active')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Club</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Disable/Enable Modal -->
    <div class="modal fade" id="clubStatusModal" tabindex="-1" aria-labelledby="clubStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="clubStatusModalLabel">Update Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="clubStatusForm">
                    @csrf
                    <div class="modal-body">
                        <p class="mb-0" id="clubStatusModalMessage"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning" id="clubStatusModalSubmit">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const editModal = document.getElementById('editClubModal');
                const editForm = document.getElementById('editClubForm');
                const editName = document.getElementById('editClubName');
                const editDescription = document.getElementById('editClubDescription');
                const editToggle = document.getElementById('editClubActiveToggle');
                const editStatusInput = document.getElementById('editClubIsActive');
                const previewWrapper = document.getElementById('editClubImagePreview');
                const previewImage = previewWrapper?.querySelector('img');
                const previewPlaceholder = previewWrapper?.querySelector('span');

                editModal?.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const action = button.getAttribute('data-action');
                    const name = button.getAttribute('data-name');
                    const description = button.getAttribute('data-description') || '';
                    const isActive = button.getAttribute('data-active') === '1';
                    const imageUrl = button.getAttribute('data-image');

                    editForm.setAttribute('action', action);
                    editName.value = name;
                    editDescription.value = description;
                    editToggle.checked = isActive;
                    editStatusInput.value = isActive ? '1' : '0';

                    if (imageUrl) {
                        previewImage?.classList.remove('d-none');
                        previewImage.src = imageUrl;
                        previewPlaceholder?.classList.add('d-none');
                    } else {
                        previewImage?.classList.add('d-none');
                        previewImage.src = '';
                        previewPlaceholder?.classList.remove('d-none');
                    }
                });

                editToggle?.addEventListener('change', function () {
                    editStatusInput.value = this.checked ? '1' : '0';
                });

                const statusModal = document.getElementById('clubStatusModal');
                const statusForm = document.getElementById('clubStatusForm');
                const statusMessage = document.getElementById('clubStatusModalMessage');
                const statusSubmit = document.getElementById('clubStatusModalSubmit');

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
