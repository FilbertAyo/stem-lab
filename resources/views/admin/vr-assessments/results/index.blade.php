<x-admin-layout>

    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">VR Assessment Results</h5>
            </div>

            {{-- Filters and Search --}}
            <div class="card mb-3">
                <div class="card-body">
                    <form action="{{ route('admin.vr-assessments.results.index') }}" method="GET" class="row g-3">
                        <div class="col-md-4">
                            
                            <input type="text" name="search" class="form-control" placeholder="Search by student name, email, question bank, or experiment..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Question Bank</label>
                            <select name="question_bank_id" class="form-select">
                                <option value="">All Question Banks</option>
                                @foreach($questionBanks as $bank)
                                    <option value="{{ $bank->id }}" {{ request('question_bank_id') == $bank->id ? 'selected' : '' }}>
                                        {{ $bank->title }} ({{ $bank->vrExperiment->name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="passed" {{ request('status') === 'passed' ? 'selected' : '' }}>Passed</option>
                                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="ti ti-filter"></i> Filter
                            </button>
                            <a href="{{ route('admin.vr-assessments.results.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-x"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student</th>
                                    <th>Question Bank</th>
                                    <th>Experiment</th>
                                    <th>Score</th>
                                    <th>Result</th>
                                    <th>Completed</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attempts as $attempt)
                                    <tr>
                                        <td>{{ $attempt->id }}</td>
                                        <td>
                                            <strong>{{ $attempt->user->name }}</strong>
                                            <br><small class="text-muted">{{ $attempt->user->email }}</small>
                                        </td>
                                        <td>{{ $attempt->questionBank->title }}</td>
                                        <td>
                                            <small>{{ $attempt->questionBank->vrExperiment->name }}</small>
                                            <br>
                                            <span class="badge bg-info">{{ $attempt->questionBank->vrExperiment->schoolClasses->pluck('name')->join(', ') ?: 'All Classes' }}</span>
                                        </td>
                                        <td>
                                            <span class="fs-5 fw-bold {{ $attempt->passed ? 'text-success' : 'text-danger' }}">
                                                {{ number_format($attempt->score, 1) }}%
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                {{ $attempt->correct_answers }}/{{ $attempt->total_questions }} correct
                                            </small>
                                        </td>
                                        <td>
                                            @if($attempt->passed)
                                                <span class="badge bg-light-success text-success b-success">
                                                    <i class="ti ti-check"></i> Passed
                                                </span>
                                            @else
                                                <span class="badge bg-light-danger text-danger b-danger">
                                                    <i class="ti ti-x"></i> Failed
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $attempt->completed_at->format('M d, Y') }}
                                            <br><small class="text-muted">{{ $attempt->completed_at->format('H:i A') }}</small>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.vr-assessments.results.show', $attempt) }}"
                                               class="btn btn-sm btn-info"
                                               title="View Details">
                                                <i class="ti ti-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="ti ti-clipboard-x fs-1 text-muted d-block mb-2"></i>
                                            No assessment results found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($attempts->hasPages())
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    Showing {{ $attempts->firstItem() }} to {{ $attempts->lastItem() }} of {{ $attempts->total() }} results
                                </div>
                                <div>
                                    {{ $attempts->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-admin-layout>

