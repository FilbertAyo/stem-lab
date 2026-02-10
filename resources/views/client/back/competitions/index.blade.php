<x-app-layout>
    <div class="col-lg-9">

        <div class="dashboard-content-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h2 class="title mb-1">Competitions</h2>    
            </div>
            <div>
                <a href="{{ route('client.competitions.my-submissions') }}" class="btn btn-primary text-white btn-sm">
                    My Submissions
                </a>
            </div>
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

                @if($teacherClubs->isEmpty())
                    <div class="card border shadow-none">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-trophy display-4 text-muted mb-3"></i>
                            <h5 class="fw-semibold mb-2">Join a club first</h5>
                            <p class="text-muted mb-3">
                                You need to join a club before you can view and participate in competitions.
                            </p>
                            <a href="{{ route('client.clubs.index') }}" class="btn btn-primary text-white btn-sm">
                                <i class="bi bi-plus-circle me-1"></i> Browse Clubs
                            </a>
                        </div>
                    </div>
                @elseif($competitions->isEmpty())
                    <div class="card border shadow-none">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-trophy display-4 text-muted mb-3"></i>
                            <h5 class="fw-semibold mb-2">No competitions available</h5>
                            <p class="text-muted mb-3">
                                There are no active competitions for your clubs at the moment. Check back later!
                            </p>
                            <a href="{{ route('dashboard') }}" class="btn btn-primary text-white btn-sm">
                                Return to dashboard
                            </a>
                        </div>
                    </div>
                @else
                    <div class="row g-4">
                        @foreach($competitions as $competition)
                            <div class="col-12 col-lg-6">
                                <div class="card h-100 border shadow-none">
                                    <div class="row g-0 h-100">
                                        <div class="col-md-4">
                                            @if($competition->cover_image)
                                                <img src="{{ Storage::url($competition->cover_image) }}" 
                                                     class="img-fluid rounded-start h-100" 
                                                     alt="{{ $competition->name }}"
                                                     style="object-fit: cover; min-height: 200px;">
                                            @else
                                                <div class="bg-light rounded-start d-flex align-items-center justify-content-center h-100" style="min-height: 200px;">
                                                    <i class="bi bi-trophy display-4 text-muted"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-md-8">
                                            <div class="card-body d-flex flex-column h-100">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <span class="badge bg-info">{{ $competition->club->name }}</span>
                                                    @if(in_array($competition->id, $joinedCompetitionIds))
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-check-circle me-1"></i> Registered
                                                        </span>
                                                    @elseif($competition->isRegistrationOpen())
                                                        <span class="badge bg-warning text-dark">Open for Registration</span>
                                                    @else
                                                        <span class="badge bg-secondary">Registration Closed</span>
                                                    @endif
                                                </div>

                                                <h5 class="card-title fw-semibold">{{ $competition->name }}</h5>
                                                <p class="card-text text-muted small flex-grow-1">
                                                    {{ Str::limit($competition->description, 100) }}
                                                </p>

                                                <div class="mb-3">
                                                    <div class="d-flex gap-3 text-muted small">
                                                        <span><i class="bi bi-calendar-event"></i> {{ $competition->competition_start->format('d M Y') }}</span>
                                                        <span><i class="bi bi-people"></i> {{ $competition->participants_count }} teams</span>
                                                    </div>
                                                    <div class="text-muted small mt-1">
                                                        <i class="bi bi-person"></i> {{ $competition->min_members }}-{{ $competition->max_members ?? '∞' }} members
                                                        @if($competition->min_age || $competition->max_age)
                                                            | <i class="bi bi-calendar3"></i> {{ $competition->min_age ?? 'Any' }}-{{ $competition->max_age ?? 'Any' }} years
                                                        @endif
                                                    </div>
                                                </div>

                                                <a href="{{ route('client.competitions.show', $competition) }}" class="btn btn-primary text-white btn-sm">
                                                    View Details <i class="bi bi-arrow-right"></i>
                                                </a>
                                            </div>
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

