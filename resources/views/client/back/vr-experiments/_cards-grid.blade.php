@php
    $loading = $loading ?? false;
    $skeletonCount = $skeletonCount ?? 12;
    $vrExperiments = $vrExperiments ?? collect();
    $search = $search ?? '';
    $selectedSubject = $selectedSubject ?? null;
@endphp

@if($loading)
    <div class="row g-4" aria-busy="true" aria-label="Loading experiments">
        @foreach(range(1, $skeletonCount) as $i)
            <div class="col-12 col-md-6 col-xl-4">
                <x-client.skeleton.lab-card />
            </div>
        @endforeach
    </div>
@elseif($vrExperiments->isEmpty())
    <div class="card border shadow-none">
        <div class="card-body text-center py-5">
            <i class="bi bi-vr display-4 text-muted mb-3"></i>
            <h5 class="fw-semibold mb-2">
                @if($search)
                    No experiments found for "{{ $search }}"
                @elseif($selectedSubject)
                    No experiments found for this subject
                @else
                    No VR experiments published yet
                @endif
            </h5>
            <p class="text-muted mb-3">
                @if($search || $selectedSubject)
                    Try adjusting your search or filters to find what you're looking for.
                @else
                    Once your instructor uploads VR experiments they will appear here automatically.
                @endif
            </p>
            @if($search || $selectedSubject)
                <a href="{{ route('client.vr-experiments.index') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Clear all filters
                </a>
            @else
                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm">
                    Explore dashboard
                </a>
            @endif
        </div>
    </div>
@else
    @if($vrExperiments->total() > 0)
        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="text-muted mb-0 small">
                Showing {{ $vrExperiments->firstItem() }}-{{ $vrExperiments->lastItem() }} of {{ $vrExperiments->total() }} experiments
            </p>
            @if($vrExperiments->hasPages())
                <span class="text-muted small">
                    Page {{ $vrExperiments->currentPage() }} of {{ $vrExperiments->lastPage() }}
                </span>
            @endif
        </div>
    @endif

    <div class="row g-4" aria-busy="false">
        @foreach($vrExperiments as $experiment)
            <div class="col-12 col-md-6 col-xl-4">
                @include('client.back.vr-experiments._experiment-card', ['experiment' => $experiment])
            </div>
        @endforeach
    </div>

    @if($vrExperiments->hasPages())
        <nav class="mt-4" aria-label="Experiment navigation">
            <div class="d-flex justify-content-center">
                {{ $vrExperiments->links('pagination::client-bootstrap-5') }}
            </div>
        </nav>
    @endif
@endif
