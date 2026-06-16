@php
    $hasActiveCategory = $activeCategory !== null;
@endphp

<x-app-layout>
    <div class="col-lg-12">
        <div class="row">
            <!-- Categories sidebar -->
            <div class="col-lg-3 mb-4 mb-lg-0">
                <div class="dashboard-sidebar">
                    <ul class="category-list">
                        <li>
                            <a href="{{ route('client.projects.index') }}"
                               class="{{ $hasActiveCategory ? '' : 'active' }}">
                                All Projects
                                @if(!$hasActiveCategory && isset($projects->total))
                                    <span class="badge bg-light bg-opacity-10 text-dark border">{{ $projects->total() }}</span>
                                @endif
                                <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                            </a>
                        </li>
                        @foreach($categories as $category)
                            <li>
                                <a href="{{ route('client.projects.index', ['category' => $category->slug]) }}"
                                   class="{{ $activeCategory && $activeCategory->id === $category->id ? 'active' : '' }}">
                                    {{ $category->name }}
                                    <!-- @if($category->projects_count)
                                        <span class="badge bg-light bg-opacity-10 text-dark border">{{ $category->projects_count }}</span>
                                    @endif -->
                                    <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Projects grid -->
            <div class="col-lg-9">
                <h4 class="mb-4">
                    {{ $activeCategory ? $activeCategory->name : 'All Projects' }}
                </h4>

                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    @forelse($projects as $project)
                        <div class="col">
                            <a href="{{ route('client.projects.show', $project) }}"
                               class="card h-100 text-decoration-none text-dark border">
                                <div class="ratio ratio-16x10 overflow-hidden" style="height: 190px;">
                                    @if($project->cover_image)
                                        <img src="{{ Storage::url($project->cover_image) }}"
                                             alt="{{ $project->name }}"
                                             class="object-fit-cover"
                                             loading="lazy">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center text-secondary" style="height: 200px;">
                                            <iconify-icon icon="lucide:lightbulb" width="48" height="48"></iconify-icon>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h3 class="card-title h6 fw-bold mb-2">{{ $project->name }}</h3>
                                    <p class="card-text small text-muted mb-2 flex-grow-1">
                                        {{ Str::limit($project->description ?? 'No description provided yet.', 100) }}
                                    </p>
                                    <div class="d-flex flex-wrap gap-1 mb-2">
                                        <span class="badge bg-light bg-opacity-10 text-dark border text-capitalize">{{ $project->difficulty }}</span>
                                     
                                        @foreach($project->platforms->take(3) as $platform)
                                            <span class="badge bg-light bg-opacity-10 text-dark border">{{ $platform->name }}</span>
                                        @endforeach
                                        @if($project->platforms->count() > 3)
                                            <span class="badge bg-light bg-opacity-10 text-dark border">+{{ $project->platforms->count() - 3 }}</span>
                                        @endif
                                    </div>
                                
                                </div>
                            </a>
                        </div>
                    @empty
                        <div class="col-12 py-5 text-center">
                            <iconify-icon icon="lucide:folder-open" width="64" height="64" class="text-muted mb-3"></iconify-icon>
                            <h4 class="h5 text-muted mb-2">No projects here yet</h4>
                            <p class="text-muted mb-0">
                                @if($hasActiveCategory)
                                    No projects in this category. Try another or view all.
                                @else
                                    Projects will appear here once they're published.
                                @endif
                            </p>
                        </div>
                    @endforelse
                </div>

                @if($projects->hasPages())
                    <div class="mt-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="small text-muted">
                            Showing {{ $projects->firstItem() }} – {{ $projects->lastItem() }} of {{ $projects->total() }}
                        </div>
                        <div>
                            {{ $projects->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
