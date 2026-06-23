@php
    $passed = $attempt->passed;
    $incorrectCount = $attempt->total_questions - $attempt->correct_answers;
    $scorePercent = (int) round($attempt->score);
@endphp

<x-app-layout>
    <div class="col-lg-9">

        <nav class="small text-muted mb-3" aria-label="Breadcrumb">
            <a href="{{ route('client.vr-assessments.index') }}" class="text-muted text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i>VR Quizzes
            </a>
            <span class="mx-1">/</span>
            <a href="{{ route('client.vr-assessments.show', $attempt->questionBank) }}" class="text-muted text-decoration-none">
                {{ Str::limit($attempt->questionBank->title, 40) }}
            </a>
            <span class="mx-1">/</span>
            <span>Results</span>
        </nav>

        <div class="row g-4">
            {{-- Results summary --}}
            <div class="col-lg-8">
                <div class="quiz-results-hero {{ $passed ? 'is-passed' : 'is-failed' }} mb-4">
                    <div class="quiz-results-banner {{ $passed ? 'is-passed' : 'is-failed' }}">
                        @if($passed)
                           <h4 class="fw-bold text-success mb-2">Congratulations!</h4>
                            <p class="text-muted mb-0">You passed this quiz. Great work!</p>
                        @else
                           <h4 class="fw-bold text-danger mb-2">Keep trying</h4>
                            <p class="text-muted mb-0">
                                You scored below {{ $attempt->questionBank->passing_score }}%. Review your answers and try again.
                            </p>
                        @endif

                        <div class="quiz-score-ring {{ $passed ? 'is-passed' : 'is-failed' }} mt-4">
                            <span class="fw-bold lh-1 fs-4">{{ number_format($attempt->score, 1) }}%</span>
                        </div>
                    </div>

                    <div class="card-body border-top">
                        <div class="row g-3">
                            <div class="col-6 col-md-3">
                                <div class="quiz-stat-tile">
                                    <div class="quiz-stat-icon bg-success bg-opacity-10 text-success p-2">
                                        <i class="bi bi-check-lg"></i>
                                    </div>
                                    <h4 class="mb-0 fw-bold text-success">{{ $attempt->correct_answers }}</h4>
                                    <small class="text-muted">Correct</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="quiz-stat-tile">
                                    <div class="quiz-stat-icon bg-danger bg-opacity-10 text-danger p-2">
                                        <i class="bi bi-x-lg"></i>
                                    </div>
                                    <h4 class="mb-0 fw-bold text-danger">{{ $incorrectCount }}</h4>
                                    <small class="text-muted">Incorrect</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="quiz-stat-tile">
                                    <div class="quiz-stat-icon bg-primary bg-opacity-10 text-primary p-2">
                                        <i class="bi bi-list-ol"></i>
                                    </div>
                                    <h4 class="mb-0 fw-bold">{{ $attempt->total_questions }}</h4>
                                    <small class="text-muted">Total</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="quiz-stat-tile">
                                    <div class="quiz-stat-icon bg-warning bg-opacity-10 text-warning p-2">
                                        <i class="bi bi-bullseye"></i>
                                    </div>
                                    <h4 class="mb-0 fw-bold">{{ $attempt->questionBank->passing_score }}%</h4>
                                    <small class="text-muted">Pass mark</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Answer review --}}
                <div class="card border shadow-none">
                    <div class="card-header bg-light-soft py-3">
                        <h5 class="mb-0 fw-semibold">
                            <i class="bi bi-list-check me-1 text-primary"></i>Answer review
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach($attempt->questionBank->questions as $index => $question)
                            @php
                                $answer = $attempt->answers->where('question_id', $question->id)->first();
                                $isCorrect = $answer && $answer->is_correct;
                                $studentAnswer = $answer ? $answer->student_answer : null;
                            @endphp
                            <div class="quiz-review-card {{ $isCorrect ? 'is-correct' : 'is-incorrect' }}">
                                <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
                                    <div class="d-flex align-items-center flex-wrap gap-2">
                                        <span class="badge bg-light text-dark border">
                                            Question {{ $index + 1 }}
                                        </span>
                                        @if($isCorrect)
                                            <span class="badge bg-light-success text-success b-success">
                                                <i class="bi bi-check-circle me-1"></i>Correct
                                            </span>
                                        @else
                                            <span class="badge bg-light-danger text-danger b-danger">
                                                <i class="bi bi-x-circle me-1"></i>Incorrect
                                            </span>
                                        @endif
                                    </div>
                                    <span class="badge bg-dark bg-opacity-75">
                                        {{ $answer ? $answer->points_earned : 0 }}/{{ $question->points }} pts
                                    </span>
                                </div>
                                <div class="card-body pt-0">
                                    <h6 class="fw-semibold mb-3">{{ $question->question_text }}</h6>

                                    @if($question->question_type === 'mcq' && $question->options)
                                        @foreach($question->options as $optionIndex => $option)
                                            @php
                                                $isCorrectOption = $option === $question->correct_answer;
                                                $isStudentWrong = $option === $studentAnswer && ! $isCorrect;
                                                $optionClass = $isCorrectOption
                                                    ? 'is-correct'
                                                    : ($isStudentWrong ? 'is-wrong' : 'is-neutral');
                                            @endphp
                                            <div class="quiz-review-option {{ $optionClass }}">
                                                @if($isCorrectOption)
                                                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                                                @elseif($isStudentWrong)
                                                    <i class="bi bi-x-circle-fill text-danger me-1"></i>
                                                @else
                                                    <i class="bi bi-circle text-muted me-1"></i>
                                                @endif
                                                <span class="badge bg-light text-dark border me-2">{{ chr(65 + $optionIndex) }}</span>
                                                {{ $option }}
                                              
                                                @if($isCorrectOption && ! $isCorrect)
                                                    <span class="badge bg-success ms-2">Correct answer</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    @elseif($question->question_type === 'true_false')
                                        <div class="row g-2 mb-0">
                                            @foreach(['True', 'False'] as $tfValue)
                                                @php
                                                    $isCorrectOption = $tfValue === $question->correct_answer;
                                                    $isStudentWrong = $tfValue === $studentAnswer && ! $isCorrect;
                                                    $optionClass = $isCorrectOption
                                                        ? 'is-correct'
                                                        : ($isStudentWrong ? 'is-wrong' : 'is-neutral');
                                                @endphp
                                                <div class="col-6">
                                                    <div class="quiz-review-option text-center {{ $optionClass }}">
                                                        @if($isCorrectOption)
                                                            <i class="bi bi-check-circle-fill text-success me-1"></i>
                                                        @elseif($isStudentWrong)
                                                            <i class="bi bi-x-circle-fill text-danger me-1"></i>
                                                        @endif
                                                        {{ $tfValue }}
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <small class="text-muted d-block mb-1">Your answer</small>
                                                <div class="quiz-review-option {{ $isCorrect ? 'is-correct' : 'is-wrong' }} mb-0">
                                                    {{ $studentAnswer ?? 'No answer provided' }}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted d-block mb-1">Correct answer</small>
                                                <div class="quiz-review-option is-correct mb-0">
                                                    {{ $question->correct_answer }}
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if($question->explanation)
                                        <div class="alert alert-info border-0 mt-3 mb-0 d-flex align-items-start gap-2">
                                            <i class="bi bi-lightbulb mt-1"></i>
                                            <div>
                                                <strong>Explanation</strong>
                                                <p class="mb-0 small">{{ $question->explanation }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                <div class="card border shadow-none dashboard-sticky-below-header">
                    <div class="card-header bg-light-soft py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="bi bi-clipboard-data me-1 text-primary"></i>Quiz summary
                        </h6>
                    </div>
                    <div class="card-body">
                        <h6 class="fw-bold mb-1">{{ $attempt->questionBank->title }}</h6>
                        <p class="text-muted small mb-3">
                            <i class="bi bi-vr me-1"></i>{{ $attempt->questionBank->vrExperiment->name }}
                        </p>

                        <ul class="list-unstyled small mb-4">
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Status</span>
                                <span class="badge {{ $passed ? 'bg-success' : 'bg-danger' }}">
                                    {{ $passed ? 'Passed' : 'Not passed' }}
                                </span>
                            </li>
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Completed</span>
                                <span>{{ $attempt->completed_at->format('M d, Y') }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Time</span>
                                <span>{{ $attempt->completed_at->format('H:i') }}</span>
                            </li>
                            @if($attempt->duration_in_minutes)
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">Duration</span>
                                    <span>{{ $attempt->duration_in_minutes }} min</span>
                                </li>
                            @endif
                            <li class="d-flex justify-content-between py-2">
                                <span class="text-muted">Accuracy</span>
                                <span class="fw-semibold">{{ $scorePercent }}%</span>
                            </li>
                        </ul>

                        <div class="d-grid gap-2">
                            <a href="{{ route('client.vr-assessments.show', $attempt->questionBank) }}"
                               class="btn btn-primary text-white">
                                <i class="bi bi-arrow-repeat me-1"></i>Try again
                            </a>
                            <a href="{{ route('client.vr-experiments.show', $attempt->questionBank->vrExperiment) }}"
                               class="btn btn-outline-primary">
                                <i class="bi bi-vr me-1"></i>View experiment
                            </a>
                            <a href="{{ route('client.vr-assessments.index') }}"
                               class="btn btn-outline-secondary">
                                <i class="bi bi-grid me-1"></i>All quizzes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
