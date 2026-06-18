<x-admin-layout>

    <div class="row">
        <div class="col-md-12 col-xl-12">
            <h5 class="mb-3">Edit VR Experiment</h5>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.vr-experiments.update', $vrExperiment) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Experiment Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $vrExperiment->name) }}" required
                                       placeholder="e.g., Virtual Chemistry Lab">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="subject_id" class="form-label">Subject <span class="text-danger">*</span></label>
                                <select class="form-select @error('subject_id') is-invalid @enderror"
                                        id="subject_id" name="subject_id" required>
                                    <option value="">Select a subject</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" 
                                                {{ old('subject_id', $vrExperiment->subject_id) == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->subject_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('subject_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Classes <span class="text-danger">*</span></label>
                                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                    @foreach($classes as $class)
                                        <div class="form-check">
                                            <input class="form-check-input @error('class_ids') is-invalid @enderror @error('class_ids.*') is-invalid @enderror"
                                                   type="checkbox"
                                                   name="class_ids[]"
                                                   value="{{ $class->id }}"
                                                   id="class_{{ $class->id }}"
                                                   {{ in_array($class->id, old('class_ids', $vrExperiment->schoolClasses->pluck('id')->all())) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="class_{{ $class->id }}">
                                                {{ $class->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="form-text text-muted">Select one or more classes that can access this experiment.</small>
                                @error('class_ids')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                @error('class_ids.*')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="external_url" class="form-label">External Simulation URL <span class="text-danger">*</span></label>
                                <input type="url" class="form-control @error('external_url') is-invalid @enderror"
                                       id="external_url" name="external_url" 
                                       value="{{ old('external_url', $vrExperiment->external_url) }}" required
                                       placeholder="https://example.com/vr-experiment">
                                @error('external_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Enter the URL to the external VR simulation site
                                    <a href="{{ $vrExperiment->external_url }}" target="_blank" class="ms-2">
                                        <i class="ti ti-external-link"></i> Test current URL
                                    </a>
                                </small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="thumbnail" class="form-label">Thumbnail</label>
                                @if($vrExperiment->thumbnail)
                                    <div class="mb-2">
                                        <p class="mb-1">Current Thumbnail:</p>
                                        <img src="{{ asset('storage/' . $vrExperiment->thumbnail) }}" 
                                             alt="Current Thumbnail" 
                                             class="img-thumbnail" 
                                             style="max-width: 200px; max-height: 150px;">
                                    </div>
                                @endif
                                <input type="file" class="form-control @error('thumbnail') is-invalid @enderror"
                                       id="thumbnail" name="thumbnail" accept="image/*">
                                @error('thumbnail')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Upload a new thumbnail to replace the current one (JPEG, PNG, GIF, or WebP). Maximum size: 1 MB.</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="10"
                                      placeholder="Describe what this VR experiment is about, how students should use it, and what learning outcomes they will achieve...">{{ old('description', $vrExperiment->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Include: <strong>How to use it</strong> - step-by-step instructions, and 
                                <strong>Learning outcomes</strong> - what students will achieve
                            </small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', $vrExperiment->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active (visible to students)
                                </label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check"></i> Update VR Experiment
                            </button>
                            <a href="{{ route('admin.vr-experiments.index') }}" class="btn btn-secondary">
                                <i class="ti ti-x"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</x-admin-layout>

