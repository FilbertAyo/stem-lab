<x-app-layout>
    <div class="col-lg-9">

        {{-- Results Header --}}
        <div class="card border shadow-none mb-4">
            <div class="card-body text-center py-5">
                @if($attempt->passed)
                    <div class="mb-4">
                        <i class="bi bi-trophy-fill text-warning" style="font-size: 5rem;"></i>
                    </div>
                    <h2 class="text-success mb-2">Congratulations!</h2>
                    <p class="text-muted fs-5">You passed the quiz!</p>
                @else
                    <div class="mb-4">
                        <i class="bi bi-emoji-frown text-muted" style="font-size: 5rem;"></i>
                    </div>
                    <h2 class="text-danger mb-2">Keep Trying!</h2>
                    <p class="text-muted fs-5">You didn't pass this time, but don't give up!</p>
                @endif

                <div class="row justify-content-center mt-4">
                    <div class="col-md-6">
                        <div class="card {{ $attempt->passed ? 'bg-success' : 'bg-danger' }} text-white">
                            <div class="card-body py-4">
                                <h1 class="display-3 fw-bold mb-0">{{ number_format($attempt->score, 1) }}%</h1>
                                <p class="mb-0">Your Score</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center mt-4 g-3">
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light rounded">
                            <h4 class="mb-0 text-success">{{ $attempt->correct_answers }}</h4>
                            <small class="text-muted">Correct</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light rounded">
                            <h4 class="mb-0 text-danger">{{ $attempt->total_questions - $attempt->correct_answers }}</h4>
                            <small class="text-muted">Incorrect</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light rounded">
                            <h4 class="mb-0">{{ $attempt->total_questions }}</h4>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light rounded">
                            <h4 class="mb-0">{{ $attempt->questionBank->passing_score }}%</h4>
                            <small class="text-muted">Pass Mark</small>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-center gap-3 flex-wrap">
                    <a href="{{ route('client.vr-assessments.show', $attempt->questionBank) }}" class="btn btn-primary">
                        <i class="bi bi-arrow-repeat me-1"></i>Try Again
                    </a>
                    <a href="{{ route('client.vr-assessments.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-list me-1"></i>All Quizzes
                    </a>
                </div>
            </div>
        </div>

        {{-- Quiz Info --}}
        <div class="card border shadow-none mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="fw-bold mb-1">{{ $attempt->questionBank->title }}</h5>
                        <p class="text-muted mb-0">
                            <i class="bi bi-vr me-1"></i>{{ $attempt->questionBank->vrExperiment->name }}
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-2 mt-md-0">
                        <small class="text-muted">
                            Completed: {{ $attempt->completed_at->format('M d, Y \a\t H:i') }}
                        </small>
                        @if($attempt->duration_in_minutes)
                            <br>
                            <small class="text-muted">
                                Duration: {{ $attempt->duration_in_minutes }} minutes
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Answer Review --}}
        <div class="card border shadow-none">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-list-check me-1"></i>Answer Review</h5>
            </div>
            <div class="card-body">
                @foreach($attempt->questionBank->questions as $index => $question)
                    @php
                        $answer = $attempt->answers->where('question_id', $question->id)->first();
                        $isCorrect = $answer && $answer->is_correct;
                        $studentAnswer = $answer ? $answer->student_answer : null;
                    @endphp
                    <div class="card mb-3 {{ $isCorrect ? 'border-success' : 'border-danger' }}">
                        <div class="card-header d-flex justify-content-between align-items-center {{ $isCorrect ? 'bg-success-subtle' : 'bg-danger-subtle' }}">
                            <span>
                                <strong>Question {{ $index + 1 }}</strong>
                                @if($isCorrect)
                                    <span class="badge bg-success ms-2">
                                        <i class="bi bi-check"></i> Correct
                                    </span>
                                @else
                                    <span class="badge bg-danger ms-2">
                                        <i class="bi bi-x"></i> Incorrect
                                    </span>
                                @endif
                            </span>
                            <span class="badge bg-dark">{{ $answer ? $answer->points_earned : 0 }}/{{ $question->points }} pts</span>
                        </div>
                        <div class="card-body">
                            <h6 class="mb-3">{{ $question->question_text }}</h6>

                            @if($question->question_type === 'mcq' && $question->options)
                                <div class="mb-3">
                                    @foreach($question->options as $option)
                                        <div class="p-2 rounded mb-1 
                                            {{ $option === $question->correct_answer ? 'bg-success-subtle border border-success' : '' }}
                                            {{ $option === $studentAnswer && !$isCorrect ? 'bg-danger-subtle border border-danger' : '' }}">
                                            @if($option === $question->correct_answer)
                                                <i class="bi bi-check-circle-fill text-success me-1"></i>
                                            @elseif($option === $studentAnswer)
                                                <i class="bi bi-x-circle-fill text-danger me-1"></i>
                                            @else
                                                <i class="bi bi-circle me-1 text-muted"></i>
                                            @endif
                                            {{ $option }}
                                            @if($option === $studentAnswer && $option !== $question->correct_answer)
                                                <span class="badge bg-danger ms-2">Your answer</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($question->question_type === 'true_false')
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <div class="p-2 rounded text-center
                                            {{ 'True' === $question->correct_answer ? 'bg-success-subtle border border-success' : '' }}
                                            {{ 'True' === $studentAnswer && !$isCorrect ? 'bg-danger-subtle border border-danger' : '' }}">
                                            @if('True' === $question->correct_answer)
                                                <i class="bi bi-check-circle-fill text-success me-1"></i>
                                            @elseif('True' === $studentAnswer)
                                                <i class="bi bi-x-circle-fill text-danger me-1"></i>
                                            @endif
                                            True
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-2 rounded text-center
                                            {{ 'False' === $question->correct_answer ? 'bg-success-subtle border border-success' : '' }}
                                            {{ 'False' === $studentAnswer && !$isCorrect ? 'bg-danger-subtle border border-danger' : '' }}">
                                            @if('False' === $question->correct_answer)
                                                <i class="bi bi-check-circle-fill text-success me-1"></i>
                                            @elseif('False' === $studentAnswer)
                                                <i class="bi bi-x-circle-fill text-danger me-1"></i>
                                            @endif
                                            False
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <small class="text-muted">Your Answer:</small>
                                        <div class="p-2 rounded {{ $isCorrect ? 'bg-success-subtle' : 'bg-danger-subtle' }}">
                                            {{ $studentAnswer ?? 'No answer provided' }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Correct Answer:</small>
                                        <div class="p-2 rounded bg-success-subtle">
                                            {{ $question->correct_answer }}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($question->explanation)
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-lightbulb me-1"></i>
                                    <strong>Explanation:</strong> {{ $question->explanation }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>

