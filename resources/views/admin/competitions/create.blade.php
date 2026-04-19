<x-admin-layout>
    @include('admin.competitions.partials.competition-form-styles')

    <div class="row">
        <div class="col-12">
            <div class="competition-form-hero mb-3">
                <h5 class="mb-1">Create Chomoza Competition</h5>
                <p class="mb-0 text-white-50">Configure club, classes, and full phase timeline (default 8 stages + optional extras).</p>
            </div>

            

            <form action="{{ route('admin.competitions.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card mb-3">
                            <div class="card-header"><h6 class="mb-0">Basic Information</h6></div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label">Competition Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Club <span class="text-danger">*</span></label>
                                        <select name="club_id" class="form-select @error('club_id') is-invalid @enderror" required>
                                            <option value="">Select club</option>
                                            @foreach($clubs as $club)
                                                <option value="{{ $club->id }}" @selected((string) old('club_id') === (string) $club->id)>{{ $club->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" id="competition_description" class="form-control js-tinymce-competition" rows="6">{!! old('description') !!}</textarea>
                                        <div class="form-text">Rich text matches the public explore competition page.</div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Cover Image</label>
                                        <input type="file" name="cover_image" class="form-control" accept="image/*">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Terms and Conditions</label>
                                        <textarea name="terms_and_conditions" id="competition_terms_and_conditions" class="form-control js-tinymce-competition" rows="8">{!! old('terms_and_conditions') !!}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Instructions</label>
                                        <textarea name="instructions" id="competition_instructions" class="form-control js-tinymce-competition" rows="8">{!! old('instructions') !!}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Allowed Classes</h6>
                                <small class="text-muted">Pulled from class table</small>
                            </div>
                            <div class="card-body">
                                <div class="row g-2">
                                    @foreach($classes as $class)
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    name="class_ids[]"
                                                    value="{{ $class->id }}"
                                                    id="class_{{ $class->id }}"
                                                    @checked(in_array($class->id, old('class_ids', [])))
                                                >
                                                <label class="form-check-label" for="class_{{ $class->id }}">{{ $class->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted d-block mt-2">If none selected, all classes can participate.</small>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Competition Phases</h6>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="loadFrameworkPhasesBtn">Load 8-stage template</button>
                                    <button type="button" class="btn btn-sm btn-primary" id="addPhaseBtn">Add phase</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="competition-phase-journey-head mb-3">
                                    Use static framework stages and optionally add extra custom phases. Each phase can have its own open/close window and status.
                                </div>
                                <div class="competition-phase-rail" id="phasesContainer"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card mb-3">
                            <div class="card-header"><h6 class="mb-0">Timeline & Requirements</h6></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Registration Opens</label>
                                    <input type="datetime-local" name="registration_start" value="{{ old('registration_start') }}" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Registration Deadline</label>
                                    <input type="datetime-local" name="registration_deadline" value="{{ old('registration_deadline') }}" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Competition Start <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="competition_start" value="{{ old('competition_start') }}" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Competition End <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="competition_end" value="{{ old('competition_end') }}" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Results Announcement</label>
                                    <input type="datetime-local" name="results_announcement_date" value="{{ old('results_announcement_date') }}" class="form-control">
                                </div>
                                <hr>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label">Min members (pool)</label>
                                        <input type="number" name="min_members" value="{{ old('min_members', 75) }}" min="1" class="form-control" required>
                                        <div class="form-text">Minimum students in the allowed class pool (eligibility), including before choosing representatives.</div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Max members (pool)</label>
                                        <input type="number" name="max_members" value="{{ old('max_members') }}" min="1" class="form-control">
                                        <div class="form-text">Optional cap on pool size. Leave empty for no upper limit.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Representatives to register</label>
                                        <input type="number" name="representative_count" value="{{ old('representative_count', 10) }}" min="1" class="form-control" required>
                                        <div class="form-text">Each school must select exactly this many representatives (must not exceed Min members pool). 70% girls rule still applies.</div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Min Age</label>
                                        <input type="number" name="min_age" value="{{ old('min_age') }}" min="1" class="form-control">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Max Age</label>
                                        <input type="number" name="max_age" value="{{ old('max_age') }}" min="1" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header"><h6 class="mb-0">Publishing</h6></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select" required>
                                        <option value="draft" @selected(old('status', 'draft') === 'draft')>Draft</option>
                                        <option value="published" @selected(old('status') === 'published')>Published</option>
                                    </select>
                                </div>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', true))>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button class="btn btn-primary btn-lg" type="submit">Create Competition</button>
                            <a href="{{ route('admin.competitions.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('admin.competitions.partials.competition-phase-builder')
    @include('admin.competitions.partials.competition-form-tinymce')
</x-admin-layout>

