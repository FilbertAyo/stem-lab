<x-admin-layout>
    <div class="row">
        <div class="col-md-12 col-xl-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Student Progress: {{ $user->name }}</h5>
                    <p class="text-muted mb-0 small">{{ $user->email }}</p>
                </div>
                <a href="{{ route('admin.progress.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left"></i> Back to Progress
                </a>
            </div>

            

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total Answers</h6>
                            <h3 class="mb-0">{{ $stats['total_answers'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Correct Answers</h6>
                            <h3 class="mb-0 text-success">{{ $stats['total_correct'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Models Completed</h6>
                            <h3 class="mb-0">{{ $stats['total_models'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Average Score</h6>
                            <h3 class="mb-0 text-primary">{{ $stats['average_score'] }}%</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Answers by Model -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Answers by Model</h5>
                </div>
                <div class="card-body">
                    @forelse($answersByModel as $modelId => $modelAnswers)
                        @php
                            $model = $modelAnswers->first()->model3d;
                            $modelCorrect = $modelAnswers->where('is_correct', true)->count();
                            $modelTotal = $modelAnswers->count();
                            $modelScore = $modelTotal > 0 ? round(($modelCorrect / $modelTotal) * 100, 2) : 0;
                        @endphp
                        <div class="card mb-3 border">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">
                                            <a href="{{ route('admin.models.3d.show', $model) }}" class="text-primary">
                                                {{ $model->name }}
                                            </a>
                                        </h6>
                                        <small class="text-muted">{{ $model->subject->subject_name ?? 'General' }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $modelScore >= 70 ? 'success' : ($modelScore >= 50 ? 'warning' : 'danger') }}">
                                            {{ $modelCorrect }}/{{ $modelTotal }} ({{ $modelScore }}%)
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Question</th>
                                                <th>Student Answer</th>
                                                <th>Correct Answer</th>
                                                <th>Result</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($modelAnswers as $answer)
                                                <tr>
                                                    <td><small>{{ Str::limit($answer->question->question_text ?? 'N/A', 60) }}</small></td>
                                                    <td><small>{{ Str::limit($answer->student_answer, 30) }}</small></td>
                                                    <td><small>{{ Str::limit($answer->question->correct_answer ?? 'N/A', 30) }}</small></td>
                                                    <td>
                                                        @if($answer->is_correct)
                                                            <span class="badge bg-success">Correct</span>
                                                        @else
                                                            <span class="badge bg-danger">Incorrect</span>
                                                        @endif
                                                    </td>
                                                    <td><small>{{ $answer->created_at->format('M d, Y') }}</small></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="ti ti-inbox fs-1 text-muted mb-3 d-block"></i>
                            <p class="text-muted mb-0">This student hasn't answered any questions yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>





