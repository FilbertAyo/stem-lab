<x-admin-layout>

    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Assessment Result Details</h5>
                    <p class="text-muted mb-0 small">{{ $attempt->questionBank->title }}</p>
                </div>
                <a href="{{ route('admin.vr-assessments.results.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left"></i> Back to Results
                </a>
            </div>

            {{-- Summary Cards --}}
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">Student</h6>
                            <h5 class="mb-0">{{ $attempt->user->name }}</h5>
                            <small class="text-muted">{{ $attempt->user->email }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card {{ $attempt->passed ? 'bg-success' : 'bg-danger' }} text-white">
                        <div class="card-body text-center">
                            <h6 class="mb-2">Score</h6>
                            <h2 class="mb-0">{{ number_format($attempt->score, 1) }}%</h2>
                            <small>{{ $attempt->correct_answers }}/{{ $attempt->total_questions }} correct</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">Result</h6>
                            @if($attempt->passed)
                                <span class="badge bg-success fs-5 px-3 py-2">
                                    <i class="ti ti-trophy"></i> PASSED
                                </span>
                            @else
                                <span class="badge bg-danger fs-5 px-3 py-2">
                                    <i class="ti ti-x"></i> FAILED
                                </span>
                            @endif
                            <br><small class="text-muted">Pass mark: {{ $attempt->questionBank->passing_score }}%</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2">Duration</h6>
                            <h5 class="mb-0">{{ $attempt->duration_in_minutes ?? 'N/A' }} min</h5>
                            <small class="text-muted">
                                Completed: {{ $attempt->completed_at->format('M d, Y H:i') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Experiment Info --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Experiment Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Experiment:</strong> {{ $attempt->questionBank->vrExperiment->name }}
                        </div>
                        <div class="col-md-3">
                            <strong>Class:</strong> {{ $attempt->questionBank->vrExperiment->schoolClass->name ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Subject:</strong> {{ $attempt->questionBank->vrExperiment->subject->subject_name ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Answer Details --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Answer Details</h6>
                </div>
                <div class="card-body">
                    @foreach($attempt->answers as $index => $answer)
                        <div class="card mb-3 {{ $answer->is_correct ? 'border-success' : 'border-danger' }}">
                            <div class="card-header d-flex justify-content-between align-items-center {{ $answer->is_correct ? 'bg-success-subtle' : 'bg-danger-subtle' }}">
                                <span>
                                    <strong>Question {{ $index + 1 }}</strong>
                                    <span class="badge {{ $answer->is_correct ? 'bg-success' : 'bg-danger' }} ms-2">
                                        {{ $answer->is_correct ? 'Correct' : 'Incorrect' }}
                                    </span>
                                </span>
                                <span class="badge bg-dark">{{ $answer->points_earned }}/{{ $answer->question->points }} pts</span>
                            </div>
                            <div class="card-body">
                                <p class="mb-3"><strong>{{ $answer->question->question_text }}</strong></p>
                                
                                @if($answer->question->question_type === 'mcq' && $answer->question->options)
                                    <div class="mb-3">
                                        <small class="text-muted">Options:</small>
                                        <ul class="list-unstyled ms-3">
                                            @foreach($answer->question->options as $option)
                                                <li class="{{ $option === $answer->question->correct_answer ? 'text-success fw-bold' : '' }}">
                                                    @if($option === $answer->question->correct_answer)
                                                        <i class="ti ti-check text-success"></i>
                                                    @endif
                                                    {{ $option }}
                                                    @if($option === $answer->student_answer && !$answer->is_correct)
                                                        <span class="badge bg-danger">Student's answer</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">Student's Answer:</small>
                                        <div class="{{ $answer->is_correct ? 'text-success' : 'text-danger' }} fw-bold">
                                            {{ $answer->student_answer ?? 'No answer provided' }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Correct Answer:</small>
                                        <div class="text-success fw-bold">
                                            {{ $answer->question->correct_answer }}
                                        </div>
                                    </div>
                                </div>

                                @if($answer->question->explanation)
                                    <div class="mt-3 p-2 bg-light rounded">
                                        <small class="text-muted"><i class="ti ti-info-circle"></i> Explanation:</small>
                                        <p class="mb-0">{{ $answer->question->explanation }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</x-admin-layout>

