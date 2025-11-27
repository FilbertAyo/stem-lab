<x-admin-layout>

    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">{{ $questionBank->title }}</h5>
                    <p class="text-muted mb-0 small">
                        Experiment: {{ $questionBank->vrExperiment->name }} |
                        {{ $questionBank->vrExperiment->schoolClass->name ?? 'N/A' }} -
                        {{ $questionBank->vrExperiment->subject->subject_name ?? 'N/A' }}
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.vr-assessments.question-banks.index') }}" class="btn btn-secondary">
                        <i class="ti ti-arrow-left"></i> Back to List
                    </a>
                    <a href="{{ route('admin.vr-assessments.questions.create', $questionBank) }}" class="btn btn-primary">
                        <i class="ti ti-plus"></i> Add Question
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Bank Info Card --}}
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ $questionBank->questions->count() }}</h3>
                            <small>Questions</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ $questionBank->time_limit ?? '∞' }}</h3>
                            <small>Minutes Limit</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ $questionBank->passing_score }}%</h3>
                            <small>Passing Score</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="mb-0"><i class="ti ti-{{ $questionBank->is_active ? 'check' : 'x' }}"></i></h3>
                            <small>{{ $questionBank->is_active ? 'Active' : 'Inactive' }}</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Questions Table --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Questions in this Bank</h6>
                </div>
                <div class="card-body">
                    @if($questionBank->questions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">Order</th>
                                        <th>Question</th>
                                        <th style="width: 120px;">Type</th>
                                        <th style="width: 80px;">Points</th>
                                        <th style="width: 150px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($questionBank->questions as $question)
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary">{{ $question->order }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ Str::limit($question->question_text, 100) }}</strong>
                                                @if($question->explanation)
                                                    <br><small class="text-muted"><i class="ti ti-info-circle"></i> Has explanation</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($question->question_type === 'mcq')
                                                    <span class="badge bg-primary">MCQ</span>
                                                @elseif($question->question_type === 'true_false')
                                                    <span class="badge bg-info">True/False</span>
                                                @else
                                                    <span class="badge bg-warning">Short Answer</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-dark">{{ $question->points }} pts</span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.vr-assessments.questions.edit', [$questionBank, $question]) }}"
                                                       class="btn btn-sm btn-primary"
                                                       title="Edit">
                                                        <i class="ti ti-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.vr-assessments.questions.destroy', [$questionBank, $question]) }}"
                                                          method="POST"
                                                          class="d-inline"
                                                          onsubmit="return confirm('Are you sure you want to delete this question?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="btn btn-sm btn-danger"
                                                                title="Delete">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <small class="text-muted">
                                Total Points: <strong>{{ $questionBank->questions->sum('points') }}</strong>
                            </small>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="ti ti-question-mark fs-1 text-muted mb-3"></i>
                            <p class="text-muted">No questions have been added to this bank yet.</p>
                            <a href="{{ route('admin.vr-assessments.questions.create', $questionBank) }}" class="btn btn-primary">
                                <i class="ti ti-plus"></i> Add First Question
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-admin-layout>

