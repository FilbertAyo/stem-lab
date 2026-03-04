<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Projects</h5>
                    <p class="text-muted mb-0">Create and manage project guides</p>
                </div>
                <a href="{{ route('admin.projects.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus"></i> Add Project
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Please fix the errors below and try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Filters -->
            <div class="card mb-3">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('admin.projects.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label small text-muted mb-1">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Search by name, category, tags..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">Publish status</label>
                            <select name="status" class="form-select">
                                <option value="" {{ request('status') === null || request('status') === '' ? 'selected' : '' }}>All</option>
                                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small text-muted mb-1">Active</label>
                            <select name="active" class="form-select">
                                <option value="" {{ request('active') === null || request('active') === '' ? 'selected' : '' }}>All</option>
                                <option value="active" {{ request('active') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('active') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-outline-primary w-100">
                                <i class="ti ti-search"></i> Filter
                            </button>
                            @if(request('search') || request('status') || request('active'))
                                <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-x"></i>
                                </a>
                            @endif
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
                                    <th style="width: 80px;">Cover</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Difficulty</th>
                                    <th>Tags</th>
                                    <th>Equipment</th>
                                    <th>Status</th>
                                    <th style="width: 170px;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($projects as $project)
                                    <tr>
                                        <td>
                                            @if($project->cover_image)
                                                <img
                                                    src="{{ Storage::url($project->cover_image) }}"
                                                    alt="{{ $project->name }}"
                                                    class="rounded"
                                                    style="width: 64px; height: 64px; object-fit: cover;"
                                                >
                                            @else
                                                <div class="rounded bg-light d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                                    <i class="ti ti-photo text-muted"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="fw-semibold">
                                            <div class="d-flex flex-column">
                                                <span>{{ $project->name }}</span>
                                                <small class="text-muted">/{{ $project->slug }}</small>
                                            </div>
                                        </td>
                                        <td>{{ $project->category?->name ?? '—' }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark text-capitalize">{{ $project->difficulty }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">{{ $project->tags_count }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $project->equipment_count }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                <span class="badge {{ $project->is_published ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $project->is_published ? 'Published' : 'Draft' }}
                                                </span>
                                                <span class="badge {{ $project->is_active ? 'bg-primary' : 'bg-dark' }}">
                                                    {{ $project->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-sm btn-outline-info" title="View">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="ti ti-edit"></i>
                                                </a>
                                                <form
                                                    action="{{ route('admin.projects.destroy', $project) }}"
                                                    method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Delete this project? This cannot be undone.');"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger" type="submit" title="Delete">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            No projects yet. <a href="{{ route('admin.projects.create') }}">Create the first project</a>.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($projects->hasPages())
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    Showing {{ $projects->firstItem() }} to {{ $projects->lastItem() }} of {{ $projects->total() }} results
                                </div>
                                <div>
                                    {{ $projects->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>