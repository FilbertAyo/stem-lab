@php
    $hasActiveCategory = $activeCategory !== null;
@endphp

<x-app-layout>
    <div class="col-lg-12">
        <!-- <div class="dashboard-content-header mb-4">
            <h2 class="title mb-0">
                {{ $activeCategory?->name ?? 'Projects' }}
            </h2>
            @if($hasActiveCategory)
                <a href="{{ route('client.projects.index') }}"
                   class="btn btn-link p-2 text-dark"
                   title="Clear category filter"
                   aria-label="Clear filter">
                    <iconify-icon icon="lucide:x" class="fs-6"></iconify-icon>
                </a>
            @endif
        </div> -->

        <div class="row">
            <!-- Categories sidebar (like dashboard.html) -->
            <div class="col-lg-3 mb-4 mb-lg-0">
                <div class="dashboard-sidebar">

                    <ul class="category-list">
                        <li>
                            <a href="{{ route('client.projects.index') }}"
                               class="{{ $hasActiveCategory ? '' : 'active' }}">
                                All Projects
                                <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                            </a>
                        </li>
                        @foreach($categories as $category)
                            <li>
                                <a href="{{ route('client.projects.index', ['category' => $category->slug]) }}"
                                   class="{{ $activeCategory && $activeCategory->id === $category->id ? 'active' : '' }}">
                                    {{ $category->name }}
                                
                                    <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Projects grid (like article-grid) -->
            <div class="col-lg-9">
                <div class="article-grid">
                    @forelse($projects as $project)
                        <a href="{{ route('client.projects.show', $project) }}"
                           class="article-item">
                            <h3>{{ $project->name }}</h3>
                            <p>
                                {{ \Illuminate\Support\Str::limit($project->description ?? 'No description provided yet.', 110) }}
                            </p>
                            <div class="d-flex flex-wrap gap-2 mt-2 small text-muted">
                                <span class="badge border text-dark text-capitalize">
                                    {{ $project->difficulty }}
                                </span>
                            
                            </div>
                        </a>
                    @empty
                        <div class="py-5 text-center text-muted">
                            <p class="mb-1">No projects available yet in this category.</p>
                            @if($hasActiveCategory)
                                <a href="{{ route('client.projects.index') }}" class="text-decoration-underline">
                                    View all projects
                                </a>
                            @endif
                        </div>
                    @endforelse
                </div>

                @if($projects->hasPages())
                    <div class="mt-4 d-flex justify-content-between align-items-center">
                        <div class="small text-muted">
                            Showing {{ $projects->firstItem() }} – {{ $projects->lastItem() }} of {{ $projects->total() }} projects
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