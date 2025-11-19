<x-admin-layout>
    <div class="row">
        <div class="col-md-12 col-xl-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Detailed Progress</h5>
                    <p class="text-muted mb-0 small">
                        Student: <strong>{{ $user->name }}</strong> | 
                        Model: <strong>{{ $model3d->name }}</strong>
                    </p>
                </div>
                <a href="{{ route('admin.progress.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left"></i> Back to Progress
                </a>
            </div>

            <!-- Summary Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6">
                            <div class="text-center">
                                <h6 class="text-muted mb-2">Total Questions</h6>
                                <h3 class="mb-0">{{ $totalQuestions }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="text-center">
                                <h6 class="text-muted mb-2">Correct Answers</h6>
                                <h3 class="mb-0 text-success">{{ $correctAnswers }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="text-center">
                                <h6 class="text-muted mb-2">Score</h6>
                                <h3 class="mb-0">{{ $correctAnswers }}/{{ $totalQuestions }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="text-center">
                                <h6 class="text-muted mb-2">Percentage</h6>
                                <h3 class="mb-0 text-{{ $percentage >= 70 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger') }}">
                                    {{ $percentage }}%
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Answers -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">All Answers</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Question</th>
                                    <th>Student Answer</th>
                                    <th>Correct Answer</th>
                                    <th>Result</th>
                                    <th>Score</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($answers as $index => $answer)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $answer->question->question_text ?? 'N/A' }}</strong>
                                            @if($answer->question->explanation)
                                                <br><small class="text-muted">{{ $answer->question->explanation }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $answer->student_answer }}</td>
                                        <td>{{ $answer->question->correct_answer ?? 'N/A' }}</td>
                                        <td>
                                            @if($answer->is_correct)
                                                <span class="badge bg-success">
                                                    <i class="ti ti-check"></i> Correct
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="ti ti-x"></i> Incorrect
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $answer->score }}</span>
                                        </td>
                                        <td>
                                            <small>{{ $answer->created_at->format('M d, Y H:i') }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>



