<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Platforms</h5>
                    <p class="text-muted mb-0">Tools & environments used in projects (e.g. Arduino, Python, Scratch)</p>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPlatformModal">
                    <i class="ti ti-plus"></i> Add Platform
                </button>
            </div>

            <ul class="nav nav-tabs mb-3" id="projects-platforms-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a
                        class="nav-link {{ request()->routeIs('admin.projects.index') ? 'active' : '' }}"
                        href="{{ route('admin.projects.index') }}"
                        role="tab"
                        aria-selected="{{ request()->routeIs('admin.projects.index') ? 'true' : 'false' }}"
                    >
                        Projects
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a
                        class="nav-link {{ request()->routeIs('admin.platforms.*') ? 'active' : '' }}"
                        href="{{ route('admin.platforms.index') }}"
                        role="tab"
                        aria-selected="{{ request()->routeIs('admin.platforms.*') ? 'true' : 'false' }}"
                    >
                        Platforms
                    </a>
                </li>
            </ul>

            

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">#</th>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Status</th>
                                    <th style="width: 140px;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($platforms as $platform)
                                    <tr>
                                        <td>{{ $platform->id }}</td>
                                        <td class="fw-semibold">{{ $platform->name }}</td>
                                        <td><code class="small">{{ $platform->slug }}</code></td>
                                        <td>
                                            <span class="badge {{ $platform->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $platform->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <button
                                                    type="button"
                                                    class="btn btn-outline-primary"
                                                    title="Edit"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editPlatformModal"
                                                    data-edit-platform="{{ $platform->id }}"
                                                    data-edit-name="{{ $platform->name }}"
                                                    data-edit-icon="{{ $platform->icon }}"
                                                    data-edit-is-active="{{ $platform->is_active ? 1 : 0 }}"
                                                >
                                                    <i class="ti ti-edit"></i>
                                                </button>
                                                <form action="{{ route('admin.platforms.destroy', $platform) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this platform? Projects using it will be unlinked.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            No platforms yet.
                                            <button type="button" class="btn btn-link p-0 align-baseline" data-bs-toggle="modal" data-bs-target="#createPlatformModal">
                                                Add the first one
                                            </button>
                                            (e.g. Arduino, Raspberry Pi, Python).
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

    <!-- Create Platform Modal -->
    <div class="modal fade" id="createPlatformModal" tabindex="-1" aria-labelledby="createPlatformModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createPlatformModalLabel">Add Platform</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.platforms.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="create_name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="name"
                                id="create_name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}"
                                required
                                placeholder="e.g. Arduino IDE"
                            >
                            <small class="text-muted">Shown to students when they filter or view projects.</small>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="create_icon" class="form-label">Icon (optional)</label>
                            <input
                                type="text"
                                name="icon"
                                id="create_icon"
                                class="form-control @error('icon') is-invalid @enderror"
                                value="{{ old('icon') }}"
                                placeholder="e.g. ti ti-microchip"
                            >
                            <small class="text-muted">Icon class name if you use Tabler icons.</small>
                            @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input type="hidden" name="is_active" value="0">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="is_active"
                                value="1"
                                id="create_isActive"
                                {{ old('is_active', true) ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="create_isActive">Active (show in project forms)</label>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check"></i> Save Platform
                        </button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Platform Modal -->
    <div class="modal fade" id="editPlatformModal" tabindex="-1" aria-labelledby="editPlatformModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPlatformModalLabel">Edit Platform</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="editPlatformForm" method="POST" action="">
                    @csrf
                    @method('PUT')

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="name"
                                id="edit_name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}"
                                required
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="edit_icon" class="form-label">Icon (optional)</label>
                            <input
                                type="text"
                                name="icon"
                                id="edit_icon"
                                class="form-control @error('icon') is-invalid @enderror"
                                value="{{ old('icon') }}"
                            >
                            @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input type="hidden" name="is_active" value="0">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="is_active"
                                value="1"
                                id="edit_isActive"
                            >
                            <label class="form-check-label" for="edit_isActive">Active (show in project forms)</label>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy"></i> Save changes
                        </button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const updateActionTemplate = "{{ route('admin.platforms.update', ['platform' => '__ID__']) }}";

            const editForm = document.getElementById('editPlatformForm');
            const editName = document.getElementById('edit_name');
            const editIcon = document.getElementById('edit_icon');
            const editIsActive = document.getElementById('edit_isActive');

            document.querySelectorAll('[data-edit-platform]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const id = this.getAttribute('data-edit-platform');
                    const name = this.getAttribute('data-edit-name') ?? '';
                    const icon = this.getAttribute('data-edit-icon') ?? '';
                    const isActive = this.getAttribute('data-edit-is-active') === '1';

                    editForm.action = updateActionTemplate.replace('__ID__', id);
                    editName.value = name;
                    editIcon.value = icon;
                    editIsActive.checked = isActive;
                });
            });
        });
    </script>
@endpush
