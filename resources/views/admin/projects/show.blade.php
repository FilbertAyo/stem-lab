<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Project Details</h5>

                <div class="d-flex gap-2">
                    <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-edit"></i> Edit
                    </a>
                    <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary btn-sm">
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
                                @if($project->estimated_time)
                                    <span class="badge bg-info me-1">{{ $project->estimated_time }} min</span>
                                @endif
                                @foreach($project->platforms as $platform)
                                    <span class="badge bg-light text-dark me-1">{{ $platform->name }}</span>
                                @endforeach
                            </p>
                            @if($project->youtube_url || $project->external_link)
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    @if($project->youtube_url)
                                        <a href="{{ $project->youtube_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-danger"><i class="ti ti-brand-youtube me-1"></i> Watch video</a>
                                    @endif
                                    @if($project->external_link)
                                        <a href="{{ $project->external_link }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary"><i class="ti ti-link me-1"></i> External link</a>
                                    @endif
                                </div>
                            @endif

                            @if($project->description)
                                <h6 class="fw-semibold mb-2">Description</h6>
                                <p class="text-muted mb-4 bg-light p-3">{{ $project->description }}</p>
                            @endif

                            @if($project->guide)
                                <h6 class="fw-semibold mb-2">Guide notes</h6>
                                <div class="text-muted mb-4 bg-light p-3">{!! $project->guide !!}</div>
                            @endif

                            @if($project->codes->isNotEmpty())
                                <h6 class="fw-semibold mb-2">Code blocks</h6>
                                @foreach($project->codes as $code)
                                    <div class="mb-3 bg-light p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-semibold">{{ $code->title }}</span>
                                            <span class="badge bg-secondary">{{ $code->language }}</span>
                                        </div>
                                        @if($code->description)
                                            <p class="text-muted small mb-1">{{ $code->description }}</p>
                                        @endif
                                        <pre class="bg-dark text-white p-3 rounded small mb-0" style="white-space: pre; overflow-x: auto; max-height: 320px;">{{ $code->code }}</pre>
                                    </div>
                                @endforeach
                            @endif

                            @if($project->embedded_code && $project->codes->isEmpty())
                                <h6 class="fw-semibold mb-2">Code</h6>
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
                                <span class="badge {{ $project->is_published ? 'bg-light-success text-success b-success' : 'bg-light-secondary text-secondary b-secondary' }}">
                                    {{ $project->is_published ? 'Published' : 'Draft' }}
                                </span>
                                <span class="badge {{ $project->is_active ? 'bg-light-success text-success b-success' : 'bg-light-dark text-dark b-dark' }}">
                                    {{ $project->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            @if($project->published_at)
                                <p class="text-muted small mt-2 mb-0">Published {{ $project->published_at->format('M j, Y') }}</p>
                            @endif
                            <div class="mt-3">
                                @include('admin.partials.ownership-meta', ['record' => $project, 'compact' => true, 'class' => ''])
                            </div>
                        </div>
                    </div>

                    @if($project->equipment->count() > 0)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Equipment used</h6>
                            </div>
                            <div class="card-body p-2">
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

                    @if($project->resources->isNotEmpty())
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Resources</h6>
                            </div>
                            <div class="card-body p-0">
                                <ul class="list-group list-group-flush">
                                    @foreach($project->resources as $res)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span>{{ $res->title }}</span>
                                            @if($res->url)
                                                <a href="{{ $res->url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary"><i class="ti ti-external-link"></i></a>
                                            @elseif($res->file_path)
                                                <a href="{{ Storage::url($res->file_path) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary"><i class="ti ti-download"></i></a>
                                            @else
                                                <span class="badge bg-light text-dark">{{ $res->type }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
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
