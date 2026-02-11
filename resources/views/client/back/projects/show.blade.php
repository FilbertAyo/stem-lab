<x-app-layout>
    <div class="col-lg-12">

        <div class="dashboard-content-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h2 class="title mb-1">{{ $project->name }}</h2>
                <p class="text-muted mb-0 small">
                    @if($project->category)
                        <span class="me-2">
                            <i class="bi bi-folder2-open me-1"></i>{{ $project->category->name }}
                        </span>
                    @endif
                    <span class="badge border text-dark text-capitalize me-1">
                        {{ $project->difficulty }}
                    </span>
                    @if($project->platform)
                        <span class="badge border text-dark">
                            {{ $project->platform }}
                        </span>
                    @endif
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('client.projects.index') }}" class="btn bg-light-soft text-dark btn-sm">
                    Back to Projects
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Main project content -->
            <div class="col-lg-8 mb-4">
                <div class="card border-0 shadow-none mb-4">
                    @if($project->cover_image)
                        <div class="position-relative" style="height: 400px;">
                            <img src="{{ Storage::url($project->cover_image) }}"
                                 alt="{{ $project->name }}"
                                 class="w-100 h-100"
                                 style="object-fit: cover; border-radius: 0.75rem 0.75rem 0 0;">
                        </div>
                    @endif

                    <div class="card-body">
                        @if($project->description)
                            <h5 class="fw-semibold mb-3">Project overview</h5>
                            <p class="text-muted mb-4">{{ $project->description }}</p>
                        @endif
                    </div>
                </div>

                @if($project->circuit_image)
                    <div class="card border-0 shadow-none mb-4">
                        <div class="card-header bg-light-soft">
                            <h5 class="mb-0">
                                <i class="bi bi-diagram-3 me-1"></i>Circuit diagram
                            </h5>
                        </div>
                        <div class="card-body text-center">
                            <img src="{{ Storage::url($project->circuit_image) }}"
                                 alt="Circuit diagram for {{ $project->name }}"
                                 class="img-fluid rounded"
                                 style="max-height: 420px; object-fit: contain;">
                        </div>
                    </div>
                @endif

                <div class="card border-0 shadow-none mb-4">
                   
                    <div class="card-body">
                    
                        @if($project->guide)
                            <h5 class="fw-semibold mb-3">Step-by-step guide</h5>
                            <div class="project-guide content-body">
                                {!! nl2br(e($project->guide)) !!}
                            </div>
                        @endif
                    </div>
                </div>

                @if($project->embedded_code)
                    <div class="card border-0 shadow-none mb-4">
                        <div class="card-header bg-light-soft">
                            <h5 class="mb-0">
                                <i class="bi bi-code-slash me-1"></i>Code
                            </h5>
                        </div>
                        <div class="card-body">
                            <div style="height: 400px; overflow-y: auto;">
                                <pre class="bg-dark text-white p-3 rounded small mb-0" style="white-space: pre; min-height: 100%; height: 100%; overflow-x: auto; margin-bottom: 0;">
                         {{ $project->embedded_code }}
                                </pre>
                            </div>
                        </div>
                    </div>
                @endif

             
            </div>

            <!-- Sidebar: Equipment + Tags -->
            <div class="col-lg-4 mb-4">
                @if($project->equipment->count() > 0)
                    <div class="card border-0 shadow-none mb-4">
                        <div class="card-header bg-light-soft">
                            <h5 class="mb-0">
                                <i class="bi bi-tools me-1"></i>Equipment used
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th style="width: 80px;">Qty</th>
                                            <th style="width: 120px;">Notes</th>
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
                    </div>
                @endif

                @if($project->tags->count() > 0)
                    <div class="card border-0 shadow-none mb-4">
                        <div class="card-header bg-light-soft">
                            <h5 class="mb-0">
                                <i class="bi bi-tags me-1"></i>Tags
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($project->tags as $tag)
                                    <span class="badge bg-light-gray text-dark border">{{ $tag->name }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                @if($categories->count() > 0)
                    <div class="card border-0 shadow-none">
                        <div class="card-header bg-light-soft">
                            <h5 class="mb-0">
                                <i class="bi bi-list-ul me-1"></i>Browse categories
                            </h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                @foreach($categories as $category)
                                    <li class="mb-1">
                                        <a href="{{ route('client.projects.index', ['category' => $category->slug]) }}"
                                           class="d-flex justify-content-between align-items-center text-decoration-none small {{ $activeCategory && $activeCategory->id === $category->id ? 'fw-semibold' : '' }}">
                                            <span>{{ $category->name }}</span>
                                            @if($category->projects_count)
                                                <span class="badge bg-light-gray text-dark">{{ $category->projects_count }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>