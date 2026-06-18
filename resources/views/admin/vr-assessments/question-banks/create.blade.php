<x-admin-layout>

    <div class="row">
        <div class="col-md-12 col-xl-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Create Question Bank</h5>
                <a href="{{ route('admin.vr-assessments.question-banks.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left"></i> Back to List
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.vr-assessments.question-banks.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="vr_experiment_id" class="form-label">VR Experiment <span class="text-danger">*</span></label>
                            <select class="form-select @error('vr_experiment_id') is-invalid @enderror"
                                    id="vr_experiment_id" name="vr_experiment_id" required>
                                <option value="">Select an experiment</option>
                                @foreach($experiments as $experiment)
                                    <option value="{{ $experiment->id }}" {{ old('vr_experiment_id') == $experiment->id ? 'selected' : '' }}>
                                        {{ $experiment->name }} ({{ $experiment->schoolClasses->pluck('name')->join(', ') ?: 'All Classes' }} - {{ $experiment->subject->subject_name ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('vr_experiment_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Select the VR experiment this question bank will be attached to.</small>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label">Bank Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   id="title" name="title" value="{{ old('title') }}" required
                                   placeholder="e.g., Basic Concepts Quiz, Advanced Assessment">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3"
                                      placeholder="Describe what this assessment covers...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="time_limit" class="form-label">Time Limit (minutes)</label>
                                <input type="number" class="form-control @error('time_limit') is-invalid @enderror"
                                       id="time_limit" name="time_limit" value="{{ old('time_limit') }}"
                                       min="1" max="180" placeholder="Leave empty for no time limit">
                                @error('time_limit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Optional. Leave empty for unlimited time.</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="passing_score" class="form-label">Passing Score (%) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('passing_score') is-invalid @enderror"
                                       id="passing_score" name="passing_score" value="{{ old('passing_score', 60) }}"
                                       min="0" max="100" required>
                                @error('passing_score')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Minimum percentage to pass the assessment.</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active (available to students)
                                </label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="ti ti-check"></i> Create Question Bank
                            </button>
                            <a href="{{ route('admin.vr-assessments.question-banks.index') }}" class="btn btn-secondary">
                                <i class="ti ti-x"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</x-admin-layout>

