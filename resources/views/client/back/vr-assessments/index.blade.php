<x-app-layout>
    <div class="col-lg-9">

        <div class="dashboard-content-header">
            <h4 class="mb-0">VR Quizzes</h4>
        </div>

        @include('client.back.partials.class-filter-notice')

        @if($questionBanks->count() > 0)
            @if($questionBanks->total() > 0)
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="text-muted mb-0 small">
                        Showing {{ $questionBanks->firstItem() }}–{{ $questionBanks->lastItem() }} of {{ $questionBanks->total() }} quizzes
                    </p>
                    @if($questionBanks->hasPages())
                        <span class="text-muted small">
                            Page {{ $questionBanks->currentPage() }} of {{ $questionBanks->lastPage() }}
                        </span>
                    @endif
                </div>
            @endif

            <div class="row">
                @foreach($questionBanks as $bank)
                    @php
                        $attempts = $userAttempts[$bank->id] ?? collect();
                        $bestAttempt = $attempts->sortByDesc('score')->first();
                        $hasCompleted = $attempts->count() > 0;
                    @endphp
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 border shadow-none">
                            {{-- Experiment Thumbnail --}}
                            <div class="position-relative" style="height: 160px;">
                                @if($bank->vrExperiment->thumbnail)
                                    <img src="{{ asset('storage/' . $bank->vrExperiment->thumbnail) }}"
                                         alt="{{ $bank->vrExperiment->name }}"
                                         class="w-100 h-100"
                                         style="object-fit: cover;">
                                @else
                                    <div class="d-flex flex-column justify-content-center align-items-center h-100 w-100"
                                         style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.5rem 0.5rem 0 0;">
                                        <i class="bi bi-question-circle text-white" style="font-size: 3rem;"></i>
                                    </div>
                                @endif
                                
                                {{-- Status Badge --}}
                                @if($hasCompleted)
                                    @if($bestAttempt->passed)
                                        <span class="position-absolute top-0 end-0 m-2 badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>Passed
                                        </span>
                                    @else
                                        <span class="position-absolute top-0 end-0 m-2 badge bg-warning">
                                            <i class="bi bi-arrow-repeat me-1"></i>Try Again
                                        </span>
                                    @endif
                                @else
                                    <span class="position-absolute top-0 end-0 m-2 badge bg-primary">
                                        <i class="bi bi-play-circle me-1"></i>New
                                    </span>
                                @endif
                            </div>

                            <div class="card-body">
                                <h5 class="fw-bold mb-2">{{ $bank->title }}</h5>
                                <p class="text-muted small mb-2">
                                    <i class="bi bi-vr me-1"></i>{{ $bank->vrExperiment->name }}
                                </p>
                      
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <span class="badge bg-light text-dark">
                                        <i class="bi bi-question-circle me-1"></i>{{ $bank->questions_count }} Questions
                                    </span>
                                    @if($bank->time_limit)
                                        <span class="badge bg-light text-dark">
                                            <i class="bi bi-clock me-1"></i>{{ $bank->time_limit }} min
                                        </span>
                                    @endif
                                    <span class="badge bg-light text-dark">
                                        <i class="bi bi-target me-1"></i>Pass: {{ $bank->passing_score }}%
                                    </span>
                                </div>

                                @if($hasCompleted)
                                    <div class="bg-light rounded p-2 mb-3">
                                        <small class="text-muted">Best Score:</small>
                                        <span class="fw-bold {{ $bestAttempt->passed ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($bestAttempt->score, 1) }}%
                                        </span>
                                        <small class="text-muted">({{ $attempts->count() }} {{ Str::plural('attempt', $attempts->count()) }})</small>
                                    </div>
                                @endif
                            </div>

                            <div class="card-footer bg-white border-0 pt-0">
                                <a href="{{ route('client.vr-assessments.show', $bank) }}" class="btn btn-primary w-100">
                                    @if($hasCompleted)
                                        <i class="bi bi-arrow-repeat me-1"></i>Retake Quiz
                                    @else
                                        <i class="bi bi-play-fill me-1"></i>Start Quiz
                                    @endif
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($questionBanks->hasPages())
                <nav class="mt-2" aria-label="Quiz navigation">
                    <div class="d-flex justify-content-center">
                        {{ $questionBanks->links('pagination::client-bootstrap-5') }}
                    </div>
                </nav>
            @endif
        @else
            <div class="card border shadow-none">
                <div class="card-body text-center py-5">
                    <i class="bi bi-question-circle text-muted" style="font-size: 4rem;"></i>
                    <h4 class="mt-3">No Quizzes Available</h4>
                    <p class="text-muted">Check back later for new quizzes based on VR experiments.</p>
                    <a href="{{ route('client.vr-experiments.index') }}" class="btn btn-primary text-white btn-sm">
                        <i class="bi bi-vr me-1"></i>Explore VR Experiments
                    </a>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>

