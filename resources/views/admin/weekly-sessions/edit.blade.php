<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Edit Weekly Session</h5>
                    <p class="text-muted mb-0">Update session details and associations</p>
                </div>
                <a href="{{ route('admin.weekly-sessions.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left"></i> Back to List
                </a>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.weekly-sessions.update', $weeklySession) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="club_id" class="form-label">Club <span class="text-danger">*</span></label>
                                <select class="form-select @error('club_id') is-invalid @enderror"
                                        id="club_id" name="club_id" required>
                                    <option value="">Select a club</option>
                                    @foreach($clubs as $club)
                                        <option value="{{ $club->id }}" {{ old('club_id', $weeklySession->club_id) == $club->id ? 'selected' : '' }}>
                                            {{ $club->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('club_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="subject_id" class="form-label">Subject <span class="text-danger">*</span></label>
                                <select class="form-select @error('subject_id') is-invalid @enderror"
                                        id="subject_id" name="subject_id" required>
                                    <option value="">Select a subject</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ old('subject_id', $weeklySession->subject_id) == $subject->id ? 'selected' : '' }}>
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
                                <label for="session_date" class="form-label">Session Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('session_date') is-invalid @enderror"
                                       id="session_date" name="session_date" value="{{ old('session_date', $weeklySession->session_date->format('Y-m-d')) }}" required>
                                @error('session_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label">Session Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   id="title" name="title" value="{{ old('title', $weeklySession->title) }}" required
                                   placeholder="e.g., Introduction to Robotics">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description / Objectives</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="4"
                                      placeholder="Describe the session objectives, learning activities, and expected outcomes...">{{ old('description', $weeklySession->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="classes" class="form-label">Classes</label>
                                <input type="text" 
                                       id="classSearch" 
                                       class="form-control mb-2" 
                                       placeholder="Search classes...">
                                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;" id="classList">
                                    @foreach($classes as $class)
                                        <div class="form-check class-item" data-name="{{ strtolower($class->name) }}">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="classes[]" 
                                                   value="{{ $class->id }}" 
                                                   id="class_{{ $class->id }}"
                                                   {{ in_array($class->id, old('classes', $weeklySession->classes->pluck('id')->toArray())) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="class_{{ $class->id }}">
                                                {{ $class->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted">Type to search and select one or more classes</small>
                                @error('classes.*')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const classSearchInput = document.getElementById('classSearch');
                                    const classList = document.getElementById('classList');
                                    const classItems = classList.querySelectorAll('.class-item');
                                    
                                    if (classSearchInput) {
                                        classSearchInput.addEventListener('input', function(e) {
                                            const searchTerm = e.target.value.toLowerCase().trim();
                                            
                                            classItems.forEach(function(item) {
                                                const name = item.getAttribute('data-name');
                                                const matches = searchTerm === '' || name.includes(searchTerm);
                                                item.style.display = matches ? 'block' : 'none';
                                            });
                                        });
                                    }
                                });
                            </script>

                            <div class="col-md-6 mb-3">
                                <label for="vr_experiment_id" class="form-label">VR Experiment (Optional)</label>
                                <select class="form-select @error('vr_experiment_id') is-invalid @enderror"
                                        id="vr_experiment_id" name="vr_experiment_id">
                                    <option value="">No VR Experiment</option>
                                    @foreach($vrExperiments as $experiment)
                                        <option value="{{ $experiment->id }}" {{ old('vr_experiment_id', $weeklySession->vr_experiment_id) == $experiment->id ? 'selected' : '' }}>
                                            {{ $experiment->name }}
                                            @if($experiment->schoolClass)
                                                ({{ $experiment->schoolClass->name }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('vr_experiment_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="model3ds" class="form-label">3D Models (Optional)</label>
                            <input type="text" 
                                   id="model3dSearch" 
                                   class="form-control mb-2" 
                                   placeholder="Search 3D models by name or subject...">
                            <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto;" id="model3dList">
                                @forelse($model3ds as $model3d)
                                    <div class="form-check model3d-item" data-name="{{ strtolower($model3d->name) }}" data-subject="{{ strtolower($model3d->subject->subject_name ?? '') }}">
                                        <input class="form-check-input" type="checkbox" 
                                               name="model3ds[]" 
                                               value="{{ $model3d->id }}" 
                                               id="model3d_{{ $model3d->id }}"
                                               {{ in_array($model3d->id, old('model3ds', $weeklySession->model3ds->pluck('id')->toArray())) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="model3d_{{ $model3d->id }}">
                                            {{ $model3d->name }}
                                            @if($model3d->subject)
                                                <span class="text-muted">({{ $model3d->subject->subject_name }})</span>
                                            @endif
                                        </label>
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">No 3D models available</p>
                                @endforelse
                            </div>
                            <small class="text-muted">Type to search and select one or more 3D models</small>
                            @error('model3ds.*')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const searchInput = document.getElementById('model3dSearch');
                                const modelList = document.getElementById('model3dList');
                                const modelItems = modelList.querySelectorAll('.model3d-item');
                                
                                if (searchInput) {
                                    searchInput.addEventListener('input', function(e) {
                                        const searchTerm = e.target.value.toLowerCase().trim();
                                        
                                        modelItems.forEach(function(item) {
                                            const name = item.getAttribute('data-name');
                                            const subject = item.getAttribute('data-subject');
                                            const matches = searchTerm === '' || 
                                                          name.includes(searchTerm) || 
                                                          subject.includes(searchTerm);
                                            
                                            item.style.display = matches ? 'block' : 'none';
                                        });
                                    });
                                }
                            });
                        </script>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    id="status" name="status">
                                <option value="draft" {{ old('status', $weeklySession->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="scheduled" {{ old('status', $weeklySession->status) == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="active" {{ old('status', $weeklySession->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="completed" {{ old('status', $weeklySession->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check"></i> Update Session
                            </button>
                            <a href="{{ route('admin.weekly-sessions.index') }}" class="btn btn-secondary">
                                <i class="ti ti-x"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
