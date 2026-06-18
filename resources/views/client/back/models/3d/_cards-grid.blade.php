@php
    $loading = $loading ?? false;
    $skeletonCount = $skeletonCount ?? 12;
    $model3ds = $model3ds ?? collect();
    $search = $search ?? '';
    $selectedSubject = $selectedSubject ?? null;
@endphp

@if($loading)
    <div class="row g-4" aria-busy="true" aria-label="Loading 3D models">
        @foreach(range(1, $skeletonCount) as $i)
            <div class="col-12 col-md-3 col-xl-3">
                <x-client.skeleton.model-card />
            </div>
        @endforeach
    </div>
@elseif($model3ds->isEmpty())
    <div class="card border shadow-none">
        <div class="card-body text-center py-5">
            <i class="bi bi-box display-4 text-muted mb-3"></i>
            <h5 class="fw-semibold mb-2">
                @if($search)
                    No models found for "{{ $search }}"
                @elseif($selectedSubject)
                    No models found for this subject
                @else
                    No 3D models published yet
                @endif
            </h5>
            <p class="text-muted mb-3">
                @if($search || $selectedSubject)
                    Try adjusting your search or filters to find what you're looking for.
                @else
                    Once your instructor uploads 3D models they will appear here automatically.
                @endif
            </p>
            @if($search || $selectedSubject)
                <a href="{{ route('client.models.3d.index') }}" class="badge bg-light-soft text-dark border d-inline-flex align-items-center gap-1 text-decoration-none">
                    Clear all filters
                </a>
            @else
                <a href="{{ route('dashboard') }}" class="badge bg-light-soft text-dark border d-inline-flex align-items-center gap-1 text-decoration-none">
                    Explore dashboard
                </a>
            @endif
        </div>
    </div>
@else
    @if($model3ds->total() > 0)
        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="text-muted mb-0 small">
                Showing {{ $model3ds->firstItem() }}-{{ $model3ds->lastItem() }} of {{ $model3ds->total() }} models
            </p>
            @if($model3ds->hasPages())
                <span class="text-muted small">
                    Page {{ $model3ds->currentPage() }} of {{ $model3ds->lastPage() }}
                </span>
            @endif
        </div>
    @endif

    <div class="row g-4" aria-busy="false">
        @foreach($model3ds as $model)
            <div class="col-12 col-md-3 col-xl-3">
                @include('client.back.models.3d._model-card', ['model' => $model])
            </div>
        @endforeach
    </div>

    @if($model3ds->hasPages())
        <nav class="mt-4" aria-label="Model navigation">
            <div class="d-flex justify-content-center">
                {{ $model3ds->links('pagination::client-bootstrap-5') }}
            </div>
        </nav>
    @endif
@endif
