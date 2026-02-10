<x-app-layout>
    <div class="col-lg-9">

        <div class="dashboard-content-header">
            <h2 class="title mb-1">School Clubs</h2>
        </div>

        <section class="row">
            <div class="col-12">
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($clubs->isEmpty())
                    <div class="card border shadow-none">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-school display-4 text-muted mb-3"></i>
                            <h5 class="fw-semibold mb-2">No clubs available yet</h5>
                            <p class="text-muted mb-3">
                                Once the administrator creates clubs, they will appear here for you to join.
                            </p>
                            <a href="{{ route('dashboard') }}" class="btn btn-primary text-white">
                                Return to dashboard
                            </a>
                        </div>
                    </div>
                @else
                    <div class="row g-4">
                        @foreach($clubs as $club)
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="card h-100 border shadow-none">
                                    @if($club->image_path)
                                        <div style="overflow: hidden; height: 300px;">
                                            <img src="{{ asset('storage/' . $club->image_path) }}"
                                                 alt="{{ $club->name }}"
                                                 class="w-100 h-100"
                                                 style="object-fit: cover;"
                                                 loading="lazy"
                                                 decoding="async">
                                        </div>
                                    @else
                                        <div class="rounded-top d-flex flex-column justify-content-center align-items-center bg-light" style="height: 220px;">
                                            <i class="bi bi-school text-secondary fs-1"></i>
                                            <span class="text-muted small mt-2">No image</span>
                                        </div>
                                    @endif

                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            @if(in_array($club->id, $joinedClubIds ?? []))
                                                <span class="badge bg-info">
                                                    <i class="bi bi-check-circle-fill me-1"></i>
                                                    Joined
                                                </span>
                                            @elseif(in_array($club->id, $pendingRequestClubIds ?? []))
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-clock-history me-1"></i>
                                                    Pending
                                                </span>
                                            @else
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle me-1"></i>
                                                    Active
                                                </span>
                                            @endif
                                        </div>

                                        <h5 class="fw-semibold">{{ $club->name }}</h5>
                                        <p class="text-muted small mb-3">
                                            {{ Str::limit($club->description ?? 'No description provided.', 110) }}
                                        </p>

                                        <div class="mt-auto">
                                            @if(in_array($club->id, $joinedClubIds ?? []))
                                                <button class="btn btn-outline-secondary w-100" disabled>
                                                    <i class="bi bi-check-circle me-1"></i>
                                                    Already Joined
                                                </button>
                                            @elseif(in_array($club->id, $pendingRequestClubIds ?? []))
                                                <button class="btn btn-outline-warning w-100" disabled>
                                                    <i class="bi bi-clock-history me-1"></i>
                                                    Request Pending
                                                </button>
                                            @else
                                                <a href="{{ route('client.clubs.join.form', $club) }}" class="btn btn-primary w-100 text-white">
                                                    <i class="bi bi-person-plus me-1"></i>
                                                    Join Club
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    </div>
</x-app-layout>


