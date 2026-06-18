<x-admin-layout>

    <div class="row">
        <div class="col-md-12 col-xl-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">VR Experiments Management</h5>
                <a href="{{ route('admin.vr-experiments.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus"></i> Add New VR Experiment
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card mb-3">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('admin.vr-experiments.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-6">
                            
                            <input type="text" name="search" class="form-control" placeholder="Search by name, description, URL, subject, or class..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="ti ti-search"></i> Search
                            </button>
                            @if(request('search'))
                                <a href="{{ route('admin.vr-experiments.index') }}" class="btn btn-outline-secondary">
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
                                    <th>Thumbnail</th>
                                    <th>Name</th>
                                    <th>Subject</th>
                                    <th>Classes</th>
                                    <th>External URL</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vrExperiments as $experiment)
                                    <tr>
                                        <td>{{ $loop->index + 1 + ($vrExperiments->currentPage() - 1) * $vrExperiments->perPage() }}</td>
                                        <td>
                                            @if($experiment->thumbnail)
                                                <img src="{{ asset('storage/' . $experiment->thumbnail) }}" 
                                                     alt="{{ $experiment->name }}" 
                                                     class="img-thumbnail" 
                                                     style="max-width: 80px; max-height: 60px;">
                                            @else
                                                <span class="text-muted">No thumbnail</span>
                                            @endif
                                        </td>
                                        <td>{{ $experiment->name }}</td>
                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $experiment->subject->subject_name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            @forelse($experiment->schoolClasses as $class)
                                                <span class="badge bg-info me-1">{{ $class->name }}</span>
                                            @empty
                                                <span class="text-muted">All Classes</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            <a href="{{ $experiment->external_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="ti ti-external-link"></i> Open
                                            </a>
                                        </td>
                                        <td>
                                            @if($experiment->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.vr-experiments.edit', $experiment) }}"
                                                   class="btn btn-sm btn-primary"
                                                   title="Edit">
                                                    <i class="ti ti-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.vr-experiments.destroy', $experiment) }}"
                                                      method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this VR experiment? This action cannot be undone.');">
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
                                        <td colspan="8" class="text-center py-4">No VR experiments found. <a href="{{ route('admin.vr-experiments.create') }}">Create one now</a>.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($vrExperiments->hasPages())
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    Showing {{ $vrExperiments->firstItem() }} to {{ $vrExperiments->lastItem() }} of {{ $vrExperiments->total() }} results
                                </div>
                                <div>
                                    {{ $vrExperiments->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-admin-layout>

