<x-admin-layout>

    <div class="row">
        <div class="col-md-12 col-xl-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">VR Assessment - Question Banks</h5>
                <a href="{{ route('admin.vr-assessments.question-banks.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus"></i> Create Question Bank
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($questionBanks as $bank)
                                    <tr>
                                        <td>{{ $bank->id }}</td>
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
                                            <span class="badge bg-info">{{ $bank->vrExperiment->schoolClass->name ?? 'N/A' }}</span>
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
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
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
                                        <td colspan="8" class="text-center py-4">
                                            <i class="ti ti-clipboard-list fs-1 text-muted d-block mb-2"></i>
                                            No question banks found. <a href="{{ route('admin.vr-assessments.question-banks.create') }}">Create one now</a>.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-admin-layout>

