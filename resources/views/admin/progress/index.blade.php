<x-admin-layout>
    <div class="row">
        <div class="col-md-12 col-xl-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Student Progress & Tracking</h5>
                    <p class="text-muted mb-0 small">Monitor student performance on 3D Model Questions</p>
                </div>
            </div>

            

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-md bg-primary text-white rounded">
                                        <i class="ti ti-check fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0 text-muted">Total Answers</h6>
                                    <h4 class="mb-0">{{ $stats['total_answers'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-md bg-success text-white rounded">
                                        <i class="ti ti-check-circle fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0 text-muted">Correct Answers</h6>
                                    <h4 class="mb-0">{{ $stats['total_correct'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-md bg-info text-white rounded">
                                        <i class="ti ti-users fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0 text-muted">Active Students</h6>
                                    <h4 class="mb-0">{{ $stats['total_students'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-md bg-warning text-white rounded">
                                        <i class="ti ti-package fs-4"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0 text-muted">Models with Answers</h6>
                                    <h4 class="mb-0">{{ $stats['total_models'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.progress.index') }}" class="row g-3">
                        <div class="col-md-5">
                            <label for="model_id" class="form-label">Filter by Model</label>
                            <select class="form-select" id="model_id" name="model_id">
                                <option value="">All Models</option>
                                @foreach($models as $model)
                                    <option value="{{ $model->id }}" {{ request('model_id') == $model->id ? 'selected' : '' }}>
                                        {{ $model->name }} ({{ $model->questions_count }} questions)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label for="user_id" class="form-label">Filter by Student</label>
                            <select class="form-select" id="user_id" name="user_id">
                                <option value="">All Students</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" {{ request('user_id') == $student->id ? 'selected' : '' }}>
                                        {{ $student->name }} ({{ $student->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Progress Table -->
            <div class="card tbl-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student</th>
                                    <th>3D Model</th>
                                    <th>Questions Answered</th>
                                    <th>Correct</th>
                                    <th>Score</th>
                                    <th>Percentage</th>
                                    <th>Last Answered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paginated as $index => $progress)
                                    <tr>
                                        <td>{{ $index + 1 + (($paginated->currentPage() - 1) * $paginated->perPage()) }}</td>
                                        <td>
                                            <div>
                                                <strong>{{ $progress->user->name }}</strong><br>
                                                <small class="text-muted">{{ $progress->user->email }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.models.3d.show', $progress->model3d) }}" class="text-primary">
                                                {{ $progress->model3d->name }}
                                            </a>
                                            <br>
                                            <small class="text-muted">{{ $progress->model3d->subject->subject_name ?? 'General' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $progress->total_questions }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">{{ $progress->correct_answers }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $progress->total_score }}/{{ $progress->total_questions }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $progress->percentage >= 70 ? 'success' : ($progress->percentage >= 50 ? 'warning' : 'danger') }}">
                                                {{ $progress->percentage }}%
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ $progress->last_answered->format('M d, Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <x-admin.table-actions :actions="[
                                                [
                                                    'type' => 'link',
                                                    'label' => 'View Details',
                                                    'icon' => 'ti ti-eye',
                                                    'url' => route('admin.progress.detail', [$progress->user, $progress->model3d]),
                                                ],
                                                [
                                                    'type' => 'link',
                                                    'label' => 'Student Progress',
                                                    'icon' => 'ti ti-user',
                                                    'url' => route('admin.progress.student', $progress->user),
                                                ],
                                                [
                                                    'type' => 'link',
                                                    'label' => 'Model Progress',
                                                    'icon' => 'ti ti-package',
                                                    'url' => route('admin.progress.model', $progress->model3d),
                                                ],
                                            ]" />
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <i class="ti ti-inbox fs-1 text-muted mb-3 d-block"></i>
                                            <p class="text-muted mb-0">No progress found. Students will appear here once they start answering questions.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($paginated->hasPages())
                        <div class="mt-3">
                            {{ $paginated->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

