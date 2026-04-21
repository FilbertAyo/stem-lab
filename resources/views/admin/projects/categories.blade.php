<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Project Categories</h5>
                    <p class="text-muted mb-0">Create and manage categories used by projects</p>
                </div>
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
                <li class="nav-item" role="presentation">
                    <a
                        class="nav-link {{ request()->routeIs('admin.project-categories.*') ? 'active' : '' }}"
                        href="{{ route('admin.project-categories.index') }}"
                        role="tab"
                        aria-selected="{{ request()->routeIs('admin.project-categories.*') ? 'true' : 'false' }}"
                    >
                        Categories
                    </a>
                </li>
            </ul>

         
            <div class="card mb-3">
                <div class="card-header bg-transparent border-bottom">
                    <h6 class="mb-0">Add category</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.project-categories.store') }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-4">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Description (optional)</label>
                            <input type="text" name="description" class="form-control" value="{{ old('description') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Sort order</label>
                            <input type="number" name="sort_order" class="form-control" min="0" value="{{ old('sort_order', 0) }}">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" id="newCatActive" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                                <label class="form-check-label" for="newCatActive">Active</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-plus"></i> Add category
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card tbl-card">
                <div class="card-header bg-transparent border-bottom">
                    <h6 class="mb-0">Existing categories</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Description</th>
                                    <th style="width: 120px;">Sort</th>
                                    <th style="width: 120px;">Projects</th>
                                    <th style="width: 120px;">Status</th>
                                    <th style="width: 220px;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                    <form id="update-category-{{ $category->id }}" action="{{ route('admin.project-categories.update', $category) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('PUT')
                                    </form>
                                    <tr>
                                        <td>
                                            <input form="update-category-{{ $category->id }}" type="text" name="name" class="form-control form-control-sm" value="{{ $category->name }}" required>
                                        </td>
                                        <td><code>{{ $category->slug }}</code></td>
                                        <td>
                                            <input form="update-category-{{ $category->id }}" type="text" name="description" class="form-control form-control-sm" value="{{ $category->description }}">
                                        </td>
                                        <td>
                                            <input form="update-category-{{ $category->id }}" type="number" name="sort_order" class="form-control form-control-sm" min="0" value="{{ $category->sort_order }}">
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $category->projects_count }}</span>
                                        </td>
                                        <td>
                                            <input form="update-category-{{ $category->id }}" type="hidden" name="is_active" value="0">
                                            <div class="form-check form-switch">
                                                <input form="update-category-{{ $category->id }}" class="form-check-input" type="checkbox" name="is_active" value="1" {{ $category->is_active ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <button form="update-category-{{ $category->id }}" type="submit" class="btn btn-sm btn-outline-primary" title="Save">
                                                    <i class="ti ti-device-floppy"></i>
                                                </button>
                                                <form action="{{ route('admin.project-categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete category &quot;{{ $category->name }}&quot;? Projects using it will become uncategorized.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No categories yet.</td>
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
