<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Create Competition</h5>
                    <p class="text-muted mb-0">Set up a new competition for clubs</p>
                </div>
                <a href="{{ route('admin.competitions.index') }}" class="btn btn-outline-secondary">
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

            <form action="{{ route('admin.competitions.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="row">
                    <!-- Main Details -->
                    <div class="col-lg-8">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="ti ti-info-circle me-2"></i>Basic Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label for="name" class="form-label">Competition Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               id="name" name="name" value="{{ old('name') }}" required
                                               placeholder="e.g., STEM Innovation Challenge 2025">
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="club_id" class="form-label">Club <span class="text-danger">*</span></label>
                                        <select class="form-select @error('club_id') is-invalid @enderror" 
                                                id="club_id" name="club_id" required>
                                            <option value="">Select Club</option>
                                            @foreach($clubs as $club)
                                                <option value="{{ $club->id }}" {{ old('club_id') == $club->id ? 'selected' : '' }}>
                                                    {{ $club->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('club_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Short Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3"
                                              placeholder="Brief overview of the competition...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="cover_image" class="form-label">Cover Image</label>
                                    <input type="file" class="form-control @error('cover_image') is-invalid @enderror" 
                                           id="cover_image" name="cover_image" accept="image/*">
                                    @error('cover_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Recommended: 1200x630px, max 4MB</small>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="ti ti-file-text me-2"></i>Terms & Instructions</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="terms_and_conditions" class="form-label">Terms and Conditions</label>
                                    <textarea class="form-control @error('terms_and_conditions') is-invalid @enderror" 
                                              id="terms_and_conditions" name="terms_and_conditions" rows="6"
                                              placeholder="Enter the terms and conditions for participating in this competition...">{{ old('terms_and_conditions') }}</textarea>
                                    @error('terms_and_conditions')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-0">
                                    <label for="instructions" class="form-label">How to Participate (Instructions)</label>
                                    <textarea class="form-control @error('instructions') is-invalid @enderror" 
                                              id="instructions" name="instructions" rows="6"
                                              placeholder="Step-by-step guide on how to participate, what to submit, format requirements, etc.">{{ old('instructions') }}</textarea>
                                    @error('instructions')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="ti ti-calendar me-2"></i>Timeline</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="registration_start" class="form-label">Registration Opens</label>
                                    <input type="datetime-local" class="form-control @error('registration_start') is-invalid @enderror" 
                                           id="registration_start" name="registration_start" value="{{ old('registration_start') }}">
                                    @error('registration_start')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="registration_deadline" class="form-label">Registration Deadline</label>
                                    <input type="datetime-local" class="form-control @error('registration_deadline') is-invalid @enderror" 
                                           id="registration_deadline" name="registration_deadline" value="{{ old('registration_deadline') }}">
                                    @error('registration_deadline')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <hr>
                                <div class="mb-3">
                                    <label for="competition_start" class="form-label">Competition Start <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('competition_start') is-invalid @enderror" 
                                           id="competition_start" name="competition_start" value="{{ old('competition_start') }}" required>
                                    @error('competition_start')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="competition_end" class="form-label">Competition End <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('competition_end') is-invalid @enderror" 
                                           id="competition_end" name="competition_end" value="{{ old('competition_end') }}" required>
                                    @error('competition_end')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <hr>
                                <div class="mb-0">
                                    <label for="results_announcement_date" class="form-label">Results Announcement</label>
                                    <input type="datetime-local" class="form-control @error('results_announcement_date') is-invalid @enderror" 
                                           id="results_announcement_date" name="results_announcement_date" value="{{ old('results_announcement_date') }}">
                                    @error('results_announcement_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="ti ti-adjustments me-2"></i>Requirements</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label for="min_members" class="form-label">Min Members <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('min_members') is-invalid @enderror" 
                                               id="min_members" name="min_members" value="{{ old('min_members', 1) }}" min="1" required>
                                        @error('min_members')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label for="max_members" class="form-label">Max Members</label>
                                        <input type="number" class="form-control @error('max_members') is-invalid @enderror" 
                                               id="max_members" name="max_members" value="{{ old('max_members') }}" min="1">
                                        @error('max_members')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Leave empty for no limit</small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6 mb-0">
                                        <label for="min_age" class="form-label">Min Age</label>
                                        <input type="number" class="form-control @error('min_age') is-invalid @enderror" 
                                               id="min_age" name="min_age" value="{{ old('min_age') }}" min="1" max="100">
                                        @error('min_age')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-6 mb-0">
                                        <label for="max_age" class="form-label">Max Age</label>
                                        <input type="number" class="form-control @error('max_age') is-invalid @enderror" 
                                               id="max_age" name="max_age" value="{{ old('max_age') }}" min="1" max="100">
                                        @error('max_age')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="ti ti-settings me-2"></i>Settings</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Draft competitions are not visible to teachers</small>
                                </div>

                                <div class="form-check form-switch">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="ti ti-check"></i> Create Competition
                            </button>
                            <a href="{{ route('admin.competitions.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>

