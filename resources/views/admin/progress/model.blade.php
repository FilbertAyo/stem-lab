<x-admin-layout>
    <div class="row">
        <div class="col-md-12 col-xl-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Model Progress: {{ $model3d->name }}</h5>
                    <p class="text-muted mb-0 small">{{ $model3d->subject->subject_name ?? 'General' }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.models.3d.show', $model3d) }}" class="btn btn-info">
                        <i class="ti ti-package"></i> View Model
                    </a>
                    <a href="{{ route('admin.progress.index') }}" class="btn btn-secondary">
                        <i class="ti ti-arrow-left"></i> Back to Progress
                    </a>
                </div>
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
                            <h6 class="text-muted mb-2">Students Attempted</h6>
                            <h3 class="mb-0">{{ $stats['total_students'] }}</h3>
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

            <!-- Answers by Student -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Answers by Student</h5>
                </div>
                <div class="card-body">
                    @forelse($answersByUser as $userId => $userAnswers)
                        @php
                            $student = $userAnswers->first()->user;
                            $studentCorrect = $userAnswers->where('is_correct', true)->count();
                            $studentTotal = $userAnswers->count();
                            $studentScore = $studentTotal > 0 ? round(($studentCorrect / $studentTotal) * 100, 2) : 0;
                        @endphp
                        <div class="card mb-3 border">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">
                                            <a href="{{ route('admin.progress.student', $student) }}" class="text-primary">
                                                {{ $student->name }}
                                            </a>
                                        </h6>
                                        <small class="text-muted">{{ $student->email }}</small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $studentScore >= 70 ? 'success' : ($studentScore >= 50 ? 'warning' : 'danger') }}">
                                            {{ $studentCorrect }}/{{ $studentTotal }} ({{ $studentScore }}%)
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
                                            @foreach($userAnswers as $answer)
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
                            <p class="text-muted mb-0">No students have answered questions for this model yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

