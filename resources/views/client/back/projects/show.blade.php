@php
    $hasCircuit = $project->circuit_image;
    $hasGuide = (bool) $project->guide;
    $hasResources = $project->resources->isNotEmpty();
    $hasEquipment = $project->equipment->count() > 0;
    $hasTags = $project->tags->count() > 0;
    $hasCategories = $categories->count() > 0;
@endphp

<x-app-layout>
    <div class="col-lg-12">
        {{-- Breadcrumb --}}
        <nav class="small text-muted mb-3" aria-label="Breadcrumb">
            <a href="{{ route('client.projects.index') }}" class="text-muted text-decoration-none">
                <iconify-icon icon="lucide:arrow-left" class="me-1"></iconify-icon>Projects
            </a>
            @if($project->category)
                <span class="mx-1">/</span>
                <a href="{{ route('client.projects.index', ['category' => $project->category->slug]) }}" class="text-muted text-decoration-none">
                    {{ $project->category->name }}
                </a>
            @endif
        </nav>

        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div class="flex-grow-1">
                <h5 class="fw-bold text-dark mb-2">{{ $project->name }}</h5>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    @if($project->category)
                        <span class="badge bg-primary bg-opacity-10 text-primary">{{ $project->category->name }}</span>
                    @endif
                    <span class="badge bg-light bg-opacity-10 text-dark border text-capitalize">{{ $project->difficulty }}</span>
                    @if($project->estimated_time)
                        <span class="badge bg-light bg-opacity-10 text-dark border">{{ $project->estimated_time }} min</span>
                    @endif
                    @foreach($project->platforms as $platform)
                        <span class="badge bg-light bg-opacity-10 text-dark border">{{ $platform->name }}</span>
                    @endforeach
                </div>
               
            </div>
            <a href="{{ route('client.projects.index') }}" class="text-decoration-none text-muted small d-inline-flex align-items-center">
                <iconify-icon icon="lucide:layout-list" class="me-1"></iconify-icon>All projects
            </a>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-4">
                @if($project->cover_image || $project->description)
                    <section class="card border shadow-none mb-4">
                        @if($project->cover_image)
                            <div class="ratio ratio-16x10 overflow-hidden bg-light">
                                <img src="{{ Storage::url($project->cover_image) }}"
                                     alt="{{ $project->name }}"
                                     class="object-fit-cover w-100 h-100"
                                     loading="eager">
                            </div>
                        @endif
                        @if($project->description)
                            <div class="card-body">
                                <h5 class="fw-semibold mb-2">Overview</h5>
                                <p class="text-muted mb-0">{{ $project->description }}</p>
                            </div>
                        @endif
                    </section>
                @endif

                @if($hasCircuit)
                    <section class="card border shadow-none mb-4" id="section-circuit">
                        <div class="card-header border-0 py-3">
                            <h5 class="fw-semibold mb-0">Circuit diagram</h5>
                        </div>
                        <div class="card-body text-center">
                            <img src="{{ Storage::url($project->circuit_image) }}"
                                 alt="Circuit for {{ $project->name }}"
                                 class="img-fluid rounded object-fit-contain">
                        </div>
                    </section>
                @endif

                @if($hasGuide)
                    <section class="card border shadow-none mb-4" id="section-guide">
                        <div class="card-body">
                            <h5 class="fw-semibold mb-3">Guide notes</h5>
                            <div class="text-muted">{!! $project->guide !!}</div>
                        </div>
                    </section>
                @endif

                @push('scripts')
                <script>
                    (function () {
                        // Reserved for future project-page interactions.
                    })();
                </script>
                @endpush
            </div>

            <aside class="col-lg-4 mb-4 sticky-lg-top">
            @if($project->youtube_url || $project->external_link)
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @if($project->youtube_url)
                            <a href="{{ $project->youtube_url }}" target="_blank" rel="noopener" class="text-decoration-none d-inline-flex align-items-center gap-1 border border-danger rounded-pill px-2 py-1 small text-danger">
                                <i class="bi bi-youtube"></i> Watch video
                            </a>
                        @endif
                        @if($project->external_link)
                            <a href="{{ $project->external_link }}" target="_blank" rel="noopener" class="text-decoration-none d-inline-flex align-items-center gap-1 border border-primary rounded-pill px-2 py-1 small text-primary">
                                <i class="bi bi-link-45deg"></i> External link
                            </a>
                        @endif
                    </div>
                @endif
                @if($hasResources)
                    <div class="card border shadow-none mb-3">
                        <div class="card-header border-0 py-2">
                            <h5 class="fw-semibold mb-0">Resources</h5>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @foreach($project->resources as $res)
                                    <li class="list-group-item d-flex justify-content-between align-items-center border-0 py-2">
                                        <span class="small text-truncate me-2">{{ $res->title }}</span>
                                        @if($res->url)
                                            <a href="{{ $res->url }}" target="_blank" rel="noopener" class="text-primary text-decoration-none flex-shrink-0" aria-label="Open"><i class="bi bi-box-arrow-up-right"></i></a>
                                        @elseif($res->file_path)
                                            <a href="{{ Storage::url($res->file_path) }}" target="_blank" rel="noopener" class="text-secondary text-decoration-none flex-shrink-0" aria-label="Download"><i class="bi bi-download"></i></a>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @if($hasEquipment)
                    <div class="card border shadow-none mb-3">
                        <div class="card-header border-0 py-2">
                            <h5 class="fw-semibold mb-0">Equipment</h5>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <tbody>
                                    @foreach($project->equipment as $item)
                                        <tr>
                                            <td>{{ $item->name }}</td>
                                            <td class="text-muted small w-auto">{{ $item->pivot->quantity ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if($hasTags)
                    <div class="card border shadow-none mb-3">
                        <div class="card-header border-0 py-2">
                            <h5 class="fw-semibold mb-0">Tags</h5>
                        </div>
                        <div class="card-body py-2">
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($project->tags as $tag)
                                    <span class="badge bg-light bg-opacity-10 text-dark border">{{ $tag->name }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                @if($hasCategories)
                    <div class="card border shadow-none">
                        <div class="card-header border-0 py-2">
                                <h5 class="fw-semibold mb-0">Categories</h5>
                        </div>
                        <div class="card-body py-2">
                            <ul class="list-unstyled mb-0 small">
                                @foreach($categories as $category)
                                    <li class="mb-1">
                                        <a href="{{ route('client.projects.index', ['category' => $category->slug]) }}"
                                           class="d-flex justify-content-between align-items-center text-decoration-none {{ $activeCategory && $activeCategory->id === $category->id ? 'fw-semibold text-primary' : 'text-dark' }}">
                                            <span>{{ $category->name }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </div>
</x-app-layout>
