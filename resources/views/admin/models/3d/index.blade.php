<x-admin-layout>

    <div class="row">
        <div class="col-md-12 col-xl-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">3D Models Management</h5>
                <a href="{{ route('admin.models.3d.create') }}" class="btn btn-sm btn-primary">
                    <i class="ti ti-plus"></i> Add New 3D Model
                </a>
            </div>

        
            <!-- Search Form -->
            <div class="card mb-3">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('admin.models.3d.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <input type="text" name="search" class="form-control" placeholder="Search by name, description, tags, or subject..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>All Status</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="ti ti-search"></i> Search
                            </button>
                            @if(request('search') || request('status'))
                                <a href="{{ route('admin.models.3d.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-x"></i> Clear
                                </a>
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
                                    <th>Subject</th>
                                    <th>Classes</th>
                                    <th>Tags</th>
                                    <th>Questions</th>
                                    <th>Status</th>
                                    <th>Logs</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($model3ds as $index => $model)
                                    <tr>
                                        <td>{{ $index + 1 + ($model3ds->currentPage() - 1) * $model3ds->perPage() }}</td>
                                        <td>{{ $model->name }}</td>
                                        <td>{{ $model->subject->subject_name ?? 'N/A' }}</td>
                                        <td>
                                            @forelse($model->schoolClasses as $class)
                                                <span class="badge bg-light text-dark border">{{ $class->name }}</span>
                                            @empty
                                                <span class="text-muted small">All classes</span>
                                            @endforelse
                                        </td>
                                        <td>{{ Str::limit($model->tags ?? 'N/A', 30) }}</td>
                                        <td>
                                            <span class="badge bg-warning">{{ $model->questions_count }}</span>
                                        </td>
                                        <td>
                                            @if($model->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            @include('admin.partials.ownership-meta', ['record' => $model, 'compact' => true, 'class' => ''])
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.models.3d.show', $model) }}"
                                                   class="btn btn-sm btn-info"
                                                   title="View Details">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.models.3d.questions.index', $model) }}"
                                                   class="btn btn-sm btn-warning"
                                                   title="Manage Questions">
                                                    <i class="ti ti-question-mark"></i>
                                                </a>
                                                <a href="{{ route('admin.models.3d.edit', $model) }}"
                                                   class="btn btn-sm btn-primary"
                                                   title="Edit">
                                                    <i class="ti ti-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.models.3d.destroy', $model) }}"
                                                      method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this 3D model? This will also delete the GLB file and thumbnail. This action cannot be undone.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-sm btn-danger"
                                                            title="Delete">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">No 3D models found. <a href="{{ route('admin.models.3d.create') }}">Create one now</a>.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($model3ds->hasPages())
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    Showing {{ $model3ds->firstItem() }} to {{ $model3ds->lastItem() }} of {{ $model3ds->total() }} results
                                </div>
                                <div>
                                    {{ $model3ds->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-admin-layout>
