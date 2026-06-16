<x-app-layout>
    <div class="col-lg-9">

        <div class="dashboard-content-header">
            <h4 class="mb-1">Weekly Sessions</h4>
        </div>

     @include('elements.client-validation')

        @if($sessions->isEmpty())
            <div class="card border shadow-none">
                <div class="card-body text-center py-5">
                    <i class="bi bi-calendar-x display-4 text-muted mb-3"></i>
                    <h5 class="fw-semibold mb-2">No sessions found</h5>
                    <p class="text-muted mb-3">
                        @if($statusFilter === 'all')
                            There are no weekly sessions available for your clubs yet.
                        @else
                            No {{ $statusFilter }} sessions found.
                        @endif
                    </p>
                </div>
            </div>
        @else
            <div class="row g-4">
                @foreach($sessions as $session)
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card h-100 border shadow-none">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="flex-grow-1">
                                        <h5 class="card-title mb-1">
                                            <a href="{{ route('client.weekly-sessions.show', $session) }}" class="text-dark text-decoration-none">
                                                {{ $session->title }}
                                            </a>
                                        </h5>
                                        <p class="text-muted small mb-2">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            From {{ $session->session_date->format('l, M d, Y') }}
                                            @if($session->session_end_date)
                                                <span class="text-muted"> - To {{ $session->session_end_date->format('l, M d, Y') }}</span>
                                            @endif
                                        </p>
                                    </div>
                                    <span class="badge {{ $session->status_badge }}">
                                        {{ ucfirst($session->status) }}
                                    </span>
                                </div>

                                @if($session->description)
                                    <p class="card-text text-muted small mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        {{ $session->description }}
                                    </p>
                                @endif

                        
                                {{-- Report Status --}}
                                <div class="mb-3">
                                    @if($session->teacherReport)
                                        <span class="badge bg-success text-white p-1 border">
                                            <i class="bi bi-check-circle"></i> Report Submitted
                                        </span>
                                    @else
                                        <span class="badge bg-secondary text-white p-1 border">
                                          No Report Yet
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-auto d-flex gap-2 justify-content-end">
                                    <a href="{{ route('client.weekly-sessions.show', $session) }}" class="btn btn-primary text-white">
                                        <i class="bi bi-eye"></i> Session
                                    </a>
                                  
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($sessions->hasPages())
                <div class="mt-4">
                    {{ $sessions->links() }}
                </div>
            @endif
        @endif
    </div>
</x-app-layout>
