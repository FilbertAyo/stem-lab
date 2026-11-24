<x-admin-layout>
    <div class="row">
        <div class="col-md-12 col-xl-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Questions for: {{ $model3d->name }}</h5>
                    <p class="text-muted mb-0 small">{{ $model3d->subject->subject_name ?? 'General' }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.models.3d.show', $model3d) }}" class="btn btn-secondary">
                        <i class="ti ti-arrow-left"></i> Back to Model
                    </a>
                    <a href="{{ route('admin.models.3d.questions.create', $model3d) }}" class="btn btn-primary">
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

            <!-- Search Form -->
            <div class="card mb-3">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('admin.models.3d.questions.index', $model3d) }}" class="row g-3 align-items-end">
                        <div class="col-md-10">
                            <input type="text" name="search" class="form-control" placeholder="Search by question text, explanation, or answer..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="ti ti-search"></i> Search
                            </button>
                            @if(request('search'))
                                <a href="{{ route('admin.models.3d.questions.index', $model3d) }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-x"></i> Clear
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    @if($questions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">Order</th>
                                        <th>Question</th>
                                        <th style="width: 150px;">Type</th>
                                        <th style="width: 200px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="questions-table-body">
                                    @foreach($questions as $question)
                                        <tr data-question-id="{{ $question->id }}">
                                            <td>
                                                <span class="badge bg-secondary">{{ $question->order }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ Str::limit($question->question_text, 80) }}</strong>
                                                @if($question->explanation)
                                                    <br><small class="text-muted">Has explanation</small>
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
                                                <x-admin.table-actions :actions="[
                                                    [
                                                        'type' => 'link',
                                                        'label' => 'Edit',
                                                        'icon' => 'ti ti-edit',
                                                        'url' => route('admin.models.3d.questions.edit', [$model3d, $question]),
                                                    ],
                                                    [
                                                        'type' => 'form',
                                                        'label' => 'Delete',
                                                        'icon' => 'ti ti-trash',
                                                        'url' => route('admin.models.3d.questions.destroy', [$model3d, $question]),
                                                        'method' => 'DELETE',
                                                        'confirm' => 'Are you sure you want to delete this question?',
                                                        'danger' => true,
                                                    ],
                                                ]" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($questions->count() > 1)
                            <div class="mt-3">
                                <p class="text-muted small mb-2">Drag rows to reorder questions (coming soon) or use the order numbers above.</p>
                            </div>
                        @endif

                        @if($questions->hasPages())
                            <div class="card-footer">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-muted small">
                                        Showing {{ $questions->firstItem() }} to {{ $questions->lastItem() }} of {{ $questions->total() }} results
                                    </div>
                                    <div>
                                        {{ $questions->links('pagination::bootstrap-5') }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="ti ti-question-mark fs-1 text-muted mb-3"></i>
                            <p class="text-muted">No questions have been added to this model yet.</p>
                            <a href="{{ route('admin.models.3d.questions.create', $model3d) }}" class="btn btn-primary">
                                <i class="ti ti-plus"></i> Add First Question
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

