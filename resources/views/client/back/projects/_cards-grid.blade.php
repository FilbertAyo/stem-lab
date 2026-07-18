@php
    $loading = $loading ?? false;
    $skeletonCount = $skeletonCount ?? 12;
    $projects = $projects ?? collect();
    $activeCategory = $activeCategory ?? null;
    $hasActiveCategory = $activeCategory !== null;
@endphp

@if($loading)
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" aria-busy="true" aria-label="Loading projects">
        @foreach(range(1, $skeletonCount) as $i)
            <div class="col">
                <x-client.skeleton.project-card />
            </div>
        @endforeach
    </div>
@elseif($projects->isEmpty())
    <div class="col-12 py-5 text-center">
        <iconify-icon icon="lucide:folder-open" width="64" height="64" class="text-muted mb-3"></iconify-icon>
        <h4 class="h5 text-muted mb-2">No projects here yet</h4>
        <p class="text-muted mb-0">
            @if($hasActiveCategory)
                No projects in this category. Try another or view all.
            @else
                Active projects will appear here.
            @endif
        </p>
    </div>
@else
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" aria-busy="false">
        @foreach($projects as $project)
            @include('client.back.projects._project-card', ['project' => $project])
        @endforeach
    </div>

    @if($projects->hasPages())
        <div class="mt-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="small text-muted">
                Showing {{ $projects->firstItem() }} – {{ $projects->lastItem() }} of {{ $projects->total() }}
            </div>
            <div>
                {{ $projects->links('pagination::client-bootstrap-5') }}
            </div>
        </div>
    @endif
@endif
