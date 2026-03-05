<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Platforms</h5>
                    <p class="text-muted mb-0">Tools & environments used in projects (e.g. Arduino, Python, Scratch)</p>
                </div>
                <a href="{{ route('admin.platforms.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus"></i> Add Platform
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

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
                                                <a href="{{ route('admin.platforms.edit', $platform) }}" class="btn btn-outline-primary" title="Edit">
                                                    <i class="ti ti-edit"></i>
                                                </a>
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
                                            No platforms yet. <a href="{{ route('admin.platforms.create') }}">Add the first one</a> (e.g. Arduino, Raspberry Pi, Python).
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
</x-admin-layout>
