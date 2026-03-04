<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Project Details</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-primary">
                        <i class="ti ti-edit"></i> Edit
                    </a>
                    <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary">
                        <i class="ti ti-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

            @if($project->cover_image)
                <div class="card mb-4">
                    <div class="card-body p-0">
                        <img src="{{ Storage::url($project->cover_image) }}"
                             alt="{{ $project->name }}"
                             class="w-100"
                             style="max-height: 320px; object-fit: cover; border-radius: 0.25rem;">
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h4 class="mb-3">{{ $project->name }}</h4>
                            <p class="text-muted small mb-3">
                                <span class="badge bg-light text-dark text-capitalize me-1">{{ $project->difficulty }}</span>
                                @if($project->category)
                                    <span class="badge bg-light text-dark me-1">{{ $project->category->name }}</span>
                                @endif
                                @if($project->platform)
                                    <span class="badge bg-light text-dark">{{ $project->platform }}</span>
                                @endif
                            </p>

                            @if($project->description)
                                <h6 class="fw-semibold mb-2">Description</h6>
                                <p class="text-muted mb-4">{{ $project->description }}</p>
                            @endif

                            @if($project->guide)
                                <h6 class="fw-semibold mb-2">Step-by-step guide</h6>
                                <div class="text-muted mb-4" style="white-space: pre-wrap;">{{ $project->guide }}</div>
                            @endif

                            @if($project->embedded_code)
                                <h6 class="fw-semibold mb-2">Embedded code</h6>
                                <pre class="bg-dark text-white p-3 rounded small mb-0" style="white-space: pre; overflow-x: auto; max-height: 300px;">{{ $project->embedded_code }}</pre>
                            @endif
                        </div>
                    </div>

                    @if($project->circuit_image)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Circuit diagram</h6>
                            </div>
                            <div class="card-body text-center">
                                <img src="{{ Storage::url($project->circuit_image) }}"
                                     alt="Circuit for {{ $project->name }}"
                                     class="img-fluid rounded"
                                     style="max-height: 400px; object-fit: contain;">
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Status</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge {{ $project->is_published ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $project->is_published ? 'Published' : 'Draft' }}
                                </span>
                                <span class="badge {{ $project->is_active ? 'bg-primary' : 'bg-dark' }}">
                                    {{ $project->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            @if($project->published_at)
                                <p class="text-muted small mt-2 mb-0">Published {{ $project->published_at->format('M j, Y') }}</p>
                            @endif
                            @if($project->author)
                                <p class="text-muted small mt-1 mb-0">By {{ $project->author->name }}</p>
                            @endif
                        </div>
                    </div>

                    @if($project->equipment->count() > 0)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Equipment used</h6>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th style="width: 70px;">Qty</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($project->equipment as $item)
                                            <tr>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->pivot->quantity ?? '—' }}</td>
                                                <td class="text-muted small">{{ $item->pivot->notes ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    @if($project->tags->count() > 0)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Tags</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($project->tags as $tag)
                                        <span class="badge bg-light text-dark border">{{ $tag->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
