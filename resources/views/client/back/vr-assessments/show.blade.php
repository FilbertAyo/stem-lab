<x-app-layout>
    <div class="col-lg-9">

        <div class="dashboard-content-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h2 class="title mb-1">{{ $questionBank->title }}</h2>
                <p class="text-muted mb-0">
                    <i class="bi bi-vr me-1"></i>{{ $questionBank->vrExperiment->name }}
                </p>
            </div>
                <a href="{{ route('client.vr-assessments.index') }}" class="btn bg-light-soft text-dark btn-sm">
                Back to Quizzes
            </a>
        </div>

        <div class="row">
            {{-- Quiz Info --}}
            <div class="col-lg-8 mb-4">
                <div class="card border-0 shadow-none">
                    {{-- Experiment Banner --}}
                    <div class="position-relative" style="height: 200px;">
                        @if($questionBank->vrExperiment->thumbnail)
                            <img src="{{ asset('storage/' . $questionBank->vrExperiment->thumbnail) }}"
                                 alt="{{ $questionBank->vrExperiment->name }}"
                                 class="w-100 h-100"
                                 style="object-fit: cover; border-radius: 0.5rem 0.5rem 0 0;">
                        @else
                            <div class="d-flex flex-column justify-content-center align-items-center h-100 w-100"
                                 style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.5rem 0.5rem 0 0;">
                                <i class="bi bi-question-circle text-white" style="font-size: 4rem;"></i>
                                <span class="text-white-50 mt-2">Quiz Assessment</span>
                            </div>
                        @endif
                    </div>

                    <div class="card-body">
                        <h4 class="fw-bold mb-3">{{ $questionBank->title }}</h4>
                        
                        @if($questionBank->description)
                            <p class="text-muted mb-4">{{ $questionBank->description }}</p>
                        @endif

                        {{-- Quiz Stats --}}
                        <div class="row g-3 mb-4">
                            <div class="col-6 col-md-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h4 class="mb-0 text-primary">{{ $questionBank->questions_count }}</h4>
                                    <small class="text-muted">Questions</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h4 class="mb-0 text-info">{{ $questionBank->time_limit ?? '∞' }}</h4>
                                    <small class="text-muted">Minutes</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h4 class="mb-0 text-warning">{{ $questionBank->passing_score }}%</h4>
                                    <small class="text-muted">To Pass</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h4 class="mb-0 text-success">{{ $previousAttempts->count() }}</h4>
                                    <small class="text-muted">Your Attempts</small>
                                </div>
                            </div>
                        </div>

                        {{-- Related Experiment Info --}}
                        <div class="border rounded p-3">
                            <h6 class="fw-semibold mb-2">
                                <i class="bi bi-vr me-1"></i>Related VR Experiment
                            </h6>
                            <p class="mb-2">{{ $questionBank->vrExperiment->name }}</p>
                            <div class="d-flex gap-2 flex-wrap">
                                <span class="badge bg-primary">{{ $questionBank->vrExperiment->subject->subject_name ?? 'General' }}</span>
                                <span class="badge bg-info">{{ $questionBank->vrExperiment->schoolClasses->pluck('name')->join(', ') ?: 'All Classes' }}</span>
                            </div>
                            <a href="{{ route('client.vr-experiments.show', $questionBank->vrExperiment) }}" 
                               class="btn btn-sm btn-outline-primary mt-2">
                                <i class="bi bi-eye me-1"></i>View Experiment
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Start Quiz Panel --}}
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-none sticky-top" style="top: 20px;">
                    <div class="card-header bg-light-soft">
                        <h5 class="mb-0"><i class="bi bi-play-circle me-2"></i>Start Quiz</h5>
                    </div>
                    <div class="card-body text-center">
                        @if($inProgressAttempt)
                            <div class="alert alert-warning mb-3">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                You have an unfinished attempt!
                            </div>
                            <form action="{{ route('client.vr-assessments.start', $questionBank) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-warning text-white btn-lg w-100 mb-2">
                                    <i class="bi bi-arrow-repeat me-1"></i>Continue Quiz
                                </button>
                            </form>
                        @else
                            <div class="py-3">
                                <i class="bi bi-clipboard-check text-primary" style="font-size: 3rem;"></i>
                                <h5 class="mt-3">Ready to test your knowledge?</h5>
                                <p class="text-muted small">
                                    @if($questionBank->time_limit)
                                        You'll have <strong>{{ $questionBank->time_limit }} minutes</strong> to complete this quiz.
                                    @else
                                        Take your time - there's no time limit!
                                    @endif
                                </p>
                            </div>
                            <form action="{{ route('client.vr-assessments.start', $questionBank) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary text-white btn-lg w-100">
                                    <i class="bi bi-play-fill me-1"></i>Start Quiz
                                </button>
                            </form>
                        @endif
                    </div>

                    @if($previousAttempts->count() > 0)
                        <div class="card-footer bg-light-soft">
                            <h6 class="fw-semibold mb-2">Previous Attempts</h6>
                            <div class="list-group list-group-flush">
                                @foreach($previousAttempts as $attempt)
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
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
                                            View
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

