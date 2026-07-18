<x-admin-layout>

    <div class="row">
        <div class="col-md-12 col-xl-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">VR Assessment - Question Banks</h5>
                <a href="{{ route('admin.vr-assessments.question-banks.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus"></i> Create Question Bank
                </a>
            </div>


            <!-- Search Form -->
            <div class="card mb-3">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('admin.vr-assessments.question-banks.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-6">
                            
                            <input type="text" name="search" class="form-control" placeholder="Search by title, description, experiment name, subject, or class..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="ti ti-search"></i> Search
                            </button>
                            @if(request('search'))
                                <a href="{{ route('admin.vr-assessments.question-banks.index') }}" class="btn btn-outline-secondary">
                                    <i class="ti ti-x"></i> Clear
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="card tbl-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Experiment</th>
                                    <th>Class / Subject</th>
                                    <th>Questions</th>
                                    <th>Settings</th>
                                    <th>Status</th>
                                    <th>Logs</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($questionBanks as $bank)
                                    <tr>
                                        <td>{{ $loop->index + 1 + ($questionBanks->currentPage() - 1) * $questionBanks->perPage() }}</td>
                                        <td>
                                            <strong>{{ $bank->title }}</strong>
                                            @if($bank->description)
                                                <br><small class="text-muted">{{ Str::limit($bank->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.vr-experiments.edit', $bank->vrExperiment) }}" class="text-decoration-none">
                                                {{ $bank->vrExperiment->name }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $bank->vrExperiment->schoolClasses->pluck('name')->join(', ') ?: 'All Classes' }}</span>
                                            <span class="badge bg-primary">{{ $bank->vrExperiment->subject->subject_name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary fs-6">{{ $bank->questions_count }}</span>
                                        </td>
                                        <td>
                                            <small>
                                                @if($bank->time_limit)
                                                    <i class="ti ti-clock"></i> {{ $bank->time_limit }} min<br>
                                                @else
                                                    <i class="ti ti-clock"></i> No limit<br>
                                                @endif
                                                <i class="ti ti-target"></i> Pass: {{ $bank->passing_score }}%
                                            </small>
                                        </td>
                                        <td>
                                            @if($bank->is_active)
                                                <span class="badge bg-light-success text-success b-success">Active</span>
                                            @else
                                                <span class="badge bg-light-secondary text-secondary b-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            @include('admin.partials.ownership-meta', ['record' => $bank, 'compact' => true, 'class' => ''])
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.vr-assessments.question-banks.show', $bank) }}"
                                                   class="btn btn-sm btn-info"
                                                   title="View & Manage Questions">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.vr-assessments.question-banks.edit', $bank) }}"
                                                   class="btn btn-sm btn-primary"
                                                   title="Edit">
                                                    <i class="ti ti-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.vr-assessments.question-banks.toggle-status', $bank) }}"
                                                      method="POST"
                                                      class="d-inline">
                                                    @csrf
                                                    <button type="submit"
                                                            class="btn btn-sm {{ $bank->is_active ? 'btn-warning' : 'btn-success' }}"
                                                            title="{{ $bank->is_active ? 'Deactivate' : 'Activate' }}">
                                                        <i class="ti ti-{{ $bank->is_active ? 'player-pause' : 'player-play' }}"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.vr-assessments.question-banks.destroy', $bank) }}"
                                                      method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this question bank? All questions will be deleted.');">
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
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <i class="ti ti-clipboard-list fs-1 text-muted d-block mb-2"></i>
                                            No question banks found. <a href="{{ route('admin.vr-assessments.question-banks.create') }}">Create one now</a>.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                    @if($questionBanks->hasPages())
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    Showing {{ $questionBanks->firstItem() }} to {{ $questionBanks->lastItem() }} of {{ $questionBanks->total() }} results
                                </div>
                                <div>
                                    {{ $questionBanks->links('pagination::bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    @endif
            </div>
        </div>
    </div>
</x-admin-layout>
