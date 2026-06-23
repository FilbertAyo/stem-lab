@php
    $bestAttempt = $previousAttempts->sortByDesc('score')->first();
    $hasPassed = $bestAttempt && $bestAttempt->passed;
@endphp

<x-app-layout>
    <div class="col-lg-9">

        <nav class="small text-muted mb-3" aria-label="Breadcrumb">
            <a href="{{ route('client.vr-assessments.index') }}" class="text-muted text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i>VR Quizzes
            </a>
        </nav>

        <div class="dashboard-content-header d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1">{{ $questionBank->title }}</h4>
                <p class="text-muted mb-2">
                    <i class="bi bi-vr me-1"></i>{{ $questionBank->vrExperiment->name }}
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge border text-dark">
                        <i class="bi bi-book me-1"></i>{{ $questionBank->vrExperiment->subject->subject_name ?? 'General' }}
                    </span>
                    @if($hasPassed)
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle me-1"></i>Passed
                        </span>
                    @elseif($bestAttempt)
                        <span class="badge bg-warning text-dark">
                            <i class="bi bi-arrow-repeat me-1"></i>Try again
                        </span>
                    @else
                        <span class="badge bg-primary">
                            <i class="bi bi-play-circle me-1"></i>New quiz
                        </span>
                    @endif
                </div>
            </div>
            <a href="{{ route('client.vr-assessments.index') }}" class="badge bg-light-soft text-dark border d-inline-flex align-items-center gap-1 text-decoration-none">
                All quizzes
            </a>
        </div>

        <div class="row g-4">
            {{-- Quiz details --}}
            <div class="col-lg-8">
                <div class="card border shadow-none overflow-hidden">
                    <div class="position-relative" style="height: 220px;">
                        @if($questionBank->vrExperiment->thumbnail)
                            <img src="{{ asset('storage/' . $questionBank->vrExperiment->thumbnail) }}"
                                 alt="{{ $questionBank->vrExperiment->name }}"
                                 class="w-100 h-100"
                                 style="object-fit: cover;">
                        @else
                            <div class="d-flex flex-column justify-content-center align-items-center h-100 w-100 bg-primary">
                                <i class="bi bi-clipboard-check text-white" style="font-size: 3.5rem;"></i>
                                <span class="text-white-50 mt-2">VR Quiz Assessment</span>
                            </div>
                        @endif
                        <div class="position-absolute bottom-0 start-0 end-0 p-3"
                             style="background: linear-gradient(transparent, rgba(0,0,0,0.65));">
                            <span class="badge bg-dark bg-opacity-75">
                                <i class="bi bi-question-circle me-1"></i>{{ $questionBank->questions_count }} questions
                            </span>
                            @if($questionBank->time_limit)
                                <span class="badge bg-dark bg-opacity-75 ms-1">
                                    <i class="bi bi-clock me-1"></i>{{ $questionBank->time_limit }} min
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="card-body">
                        @if($questionBank->description)
                            <h6 class="fw-semibold mb-2">About this quiz</h6>
                            <p class="text-muted mb-4">{{ $questionBank->description }}</p>
                        @endif

                        <div class="row g-3 mb-4">
                            <div class="col-6 col-md-3">
                                <div class="quiz-stat-tile">
                                    <div class="quiz-stat-icon bg-primary bg-opacity-10 text-primary p-2">
                                        <i class="bi bi-list-ol"></i>
                                    </div>
                                    <h4 class="mb-0 fw-bold">{{ $questionBank->questions_count }}</h4>
                                    <small class="text-muted">Questions</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="quiz-stat-tile">
                                    <div class="quiz-stat-icon bg-info bg-opacity-10 text-info p-2">
                                        <i class="bi bi-clock"></i>
                                    </div>
                                    <h4 class="mb-0 fw-bold">{{ $questionBank->time_limit ?? '∞' }}</h4>
                                    <small class="text-muted">Minutes</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="quiz-stat-tile">
                                    <div class="quiz-stat-icon bg-warning bg-opacity-10 text-warning p-2">
                                        <i class="bi bi-bullseye"></i>
                                    </div>
                                    <h4 class="mb-0 fw-bold">{{ $questionBank->passing_score }}%</h4>
                                    <small class="text-muted">To pass</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="quiz-stat-tile">
                                    <div class="quiz-stat-icon bg-success bg-opacity-10 text-success p-2">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </div>
                                    <h4 class="mb-0 fw-bold">{{ $previousAttempts->count() }}</h4>
                                    <small class="text-muted">Attempts</small>
                                </div>
                            </div>
                        </div>

                        <div class="quiz-experiment-link p-3">
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                @if($questionBank->vrExperiment->thumbnail)
                                    <img src="{{ asset('storage/' . $questionBank->vrExperiment->thumbnail) }}"
                                         alt=""
                                         class="rounded flex-shrink-0"
                                         width="72"
                                         height="72"
                                         style="object-fit: cover;">
                                @else
                                    <div class="rounded bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width: 72px; height: 72px;">
                                        <i class="bi bi-vr text-primary fs-4"></i>
                                    </div>
                                @endif
                                <div class="flex-grow-1">
                                    <h6 class="fw-semibold mb-1">Related VR experiment</h6>
                                    <p class="text-muted small mb-2">{{ $questionBank->vrExperiment->name }}</p>
                                    <span class="badge bg-light bg-opacity-10 text-dark border">
                                        {{ $questionBank->vrExperiment->schoolClasses->pluck('name')->join(', ') ?: 'All classes' }}
                                    </span>
                                </div>
                                <a href="{{ route('client.vr-experiments.show', $questionBank->vrExperiment) }}"
                                   class="btn btn-sm btn-outline-primary flex-shrink-0">
                                    <i class="bi bi-eye me-1"></i>View experiment
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Start quiz panel --}}
            <div class="col-lg-4">
                <div class="card border shadow-none quiz-start-panel sticky-top" style="top: 5.5rem;">
                    <div class="card-header bg-light-soft py-3">
                        <h5 class="mb-0 fw-semibold">
                            <i class="bi bi-play-circle me-2 text-primary"></i>
                            {{ $inProgressAttempt ? 'Continue quiz' : 'Start quiz' }}
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($inProgressAttempt)
                            <div class="alert alert-warning border-0 d-flex align-items-start gap-2 mb-3">
                                <i class="bi bi-exclamation-triangle mt-1"></i>
                                <div>
                                    <strong class="d-block">Unfinished attempt</strong>
                                    <span class="small">Pick up where you left off.</span>
                                </div>
                            </div>
                        @else
                            @if($bestAttempt)
                                <div class="text-center mb-3 p-3 rounded bg-light">
                                    <small class="text-muted d-block mb-1">Your best score</small>
                                    <span class="fs-3 fw-bold {{ $hasPassed ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($bestAttempt->score, 1) }}%
                                    </span>
                                </div>
                            @endif

                            <div class="text-center mb-3">
                                <div class="quiz-stat-icon bg-primary bg-opacity-10 text-primary mx-auto" style="width: 3rem; height: 3rem; font-size: 1.25rem;">
                                    <i class="bi bi-clipboard-check"></i>
                                </div>
                                <h6 class="fw-semibold mt-3 mb-1">Ready to begin?</h6>
                                <p class="text-muted small mb-0">Review the rules below before you start.</p>
                            </div>

                            <ul class="quiz-rules-list">
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span>Answer all {{ $questionBank->questions_count }} questions before submitting.</span>
                                </li>
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span>
                                        @if($questionBank->time_limit)
                                            You have <strong>{{ $questionBank->time_limit }} minutes</strong> — the quiz auto-submits when time runs out.
                                        @else
                                            No time limit — work at your own pace.
                                        @endif
                                    </span>
                                </li>
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span>Score at least <strong>{{ $questionBank->passing_score }}%</strong> to pass.</span>
                                </li>
                            </ul>
                        @endif

                        <form action="{{ route('client.vr-assessments.start', $questionBank) }}" method="POST">
                            @csrf
                            @if($inProgressAttempt)
                                <button type="submit" class="btn btn-warning text-white btn-lg w-100">
                                    <i class="bi bi-arrow-repeat me-1"></i>Continue quiz
                                </button>
                            @else
                                <button type="submit" class="btn btn-primary text-white btn-lg w-100">
                                    <i class="bi bi-play-fill me-1"></i>Start quiz
                                </button>
                            @endif
                        </form>
                    </div>

                    @if($previousAttempts->count() > 0)
                        <div class="card-footer bg-light-soft border-top">
                            <h6 class="fw-semibold mb-3">Recent attempts</h6>
                            @foreach($previousAttempts as $attempt)
                                <div class="quiz-attempt-row d-flex justify-content-between align-items-center gap-2">
                                    <div>
                                        <span class="badge {{ $attempt->passed ? 'bg-success' : 'bg-danger' }}">
                                            {{ number_format($attempt->score, 1) }}%
                                        </span>
                                        <small class="text-muted ms-2">
                                            {{ $attempt->completed_at->format('M d, H:i') }}
                                        </small>
                                    </div>
                                    <a href="{{ route('client.vr-assessments.results', $attempt) }}"
                                       class="btn btn-sm btn-outline-secondary">
                                        Results
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
