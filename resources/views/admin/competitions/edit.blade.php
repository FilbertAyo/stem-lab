<x-admin-layout>
    @include('admin.competitions.partials.competition-form-styles')

    <div class="row">
        <div class="col-12">
            <div class="competition-form-hero mb-3">
                <h5 class="mb-1">Edit {{ $competition->name }}</h5>
                <p class="mb-0 text-white-50">Update phase windows, class eligibility, and publishing setup.</p>
            </div>

            

            <form action="{{ route('admin.competitions.update', $competition) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card mb-3">
                            <div class="card-header"><h6 class="mb-0">Basic Information</h6></div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label">Competition Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                               name="name" value="{{ old('name', $competition->name) }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Club <span class="text-danger">*</span></label>
                                        <select class="form-select @error('club_id') is-invalid @enderror" name="club_id" required>
                                            <option value="">Select Club</option>
                                            @foreach($clubs as $club)
                                                <option value="{{ $club->id }}" @selected((string) old('club_id', $competition->club_id) === (string) $club->id)>{{ $club->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control js-tinymce-competition" name="description" id="competition_description" rows="6">{!! old('description', $competition->description) !!}</textarea>
                                        <div class="form-text">Rich text matches the public explore competition page.</div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Cover Image</label>
                                        <input type="file" class="form-control" name="cover_image" accept="image/*">
                                    </div>
                                    @if($competition->cover_image)
                                        <div class="col-12">
                                            <img src="{{ Storage::url($competition->cover_image) }}" alt="Current cover" class="img-thumbnail" style="max-height: 150px;">
                                        </div>
                                    @endif
                                    <div class="col-12">
                                        <label class="form-label">Terms and Conditions</label>
                                        <textarea class="form-control js-tinymce-competition" name="terms_and_conditions" id="competition_terms_and_conditions" rows="8">{!! old('terms_and_conditions', $competition->terms_and_conditions) !!}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Instructions</label>
                                        <textarea class="form-control js-tinymce-competition" name="instructions" id="competition_instructions" rows="8">{!! old('instructions', $competition->instructions) !!}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header"><h6 class="mb-0">Allowed Classes</h6></div>
                            <div class="card-body">
                                @php($selectedClasses = old('class_ids', $competition->classRequirements->pluck('school_class_id')->all()))
                                <div class="row g-2">
                                    @foreach($classes as $class)
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="class_ids[]" value="{{ $class->id }}" id="class_{{ $class->id }}" @checked(in_array($class->id, $selectedClasses))>
                                                <label class="form-check-label" for="class_{{ $class->id }}">{{ $class->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
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
                                    Edit default stages or add extra custom phases. Status and dates drive teacher visibility.
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
                                    <input type="datetime-local" class="form-control" name="registration_start"
                                           value="{{ old('registration_start', $competition->registration_start?->format('Y-m-d\TH:i')) }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Registration Deadline</label>
                                    <input type="datetime-local" class="form-control" name="registration_deadline"
                                           value="{{ old('registration_deadline', $competition->registration_deadline?->format('Y-m-d\TH:i')) }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Competition Start <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control"
                                           name="competition_start" value="{{ old('competition_start', $competition->competition_start?->format('Y-m-d\TH:i')) }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Competition End <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control"
                                           name="competition_end" value="{{ old('competition_end', $competition->competition_end?->format('Y-m-d\TH:i')) }}" required>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">Results Announcement</label>
                                    <input type="datetime-local" class="form-control" name="results_announcement_date"
                                           value="{{ old('results_announcement_date', $competition->results_announcement_date?->format('Y-m-d\TH:i')) }}">
                                </div>
                                <hr>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label">Min members (pool)</label>
                                        <input type="number" class="form-control" name="min_members" value="{{ old('min_members', $competition->min_members) }}" min="1" required>
                                        <div class="form-text">Minimum students in the allowed class pool (eligibility).</div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Max members (pool)</label>
                                        <input type="number" class="form-control" name="max_members" value="{{ old('max_members', $competition->max_members) }}" min="1">
                                        <div class="form-text">Optional cap; leave empty for no limit.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Representatives to register</label>
                                        <input type="number" name="representative_count" value="{{ old('representative_count', $competition->representative_count ?? 10) }}" min="1" class="form-control" required>
                                        <div class="form-text">Each school must select exactly this many representatives (must not exceed Min members pool).</div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Min Age</label>
                                        <input type="number" class="form-control" name="min_age" value="{{ old('min_age', $competition->min_age) }}" min="1">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Max Age</label>
                                        <input type="number" class="form-control" name="max_age" value="{{ old('max_age', $competition->max_age) }}" min="1">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="ti ti-file-upload me-2"></i>Prototype Evidence (Google Form)</h6>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('admin.competitions.prototype-evidence-link.update', $competition) }}">
                                    @csrf
                                    <div class="mb-2">
                                        <label class="form-label">Google Form URL</label>
                                        <input
                                            type="text"
                                            name="google_form_url"
                                            class="form-control"
                                            placeholder="Paste the organizers' Google Form upload link"
                                            value="{{ old('google_form_url', $prototypeEvidenceFormUrl) }}"
                                        >
                                        <div class="form-text">Shown to teachers during `Prototype development`.</div>
                                    </div>

                                    @if(filled($prototypeEvidenceFormUrl))
                                        <div class="mb-3">
                                            <a href="{{ $prototypeEvidenceFormUrl }}" target="_blank" rel="noopener noreferrer" class="small">
                                                Current link (open)
                                            </a>
                                        </div>
                                    @endif

                                    <button type="submit" class="btn btn-primary btn-sm">Save link</button>
                                </form>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="ti ti-file-upload me-2"></i>Prototype Modification Evidence (Google Form)</h6>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('admin.competitions.prototype-modification-evidence-link.update', $competition) }}">
                                    @csrf
                                    <div class="mb-2">
                                        <label class="form-label">Google Form URL</label>
                                        <input
                                            type="text"
                                            name="google_form_url"
                                            class="form-control"
                                            placeholder="Paste the organizers' Google Form upload link"
                                            value="{{ old('google_form_url', $prototypeModificationEvidenceFormUrl) }}"
                                        >
                                        <div class="form-text">Shown to teachers during `Prototype modification`.</div>
                                    </div>

                                    @if(filled($prototypeModificationEvidenceFormUrl))
                                        <div class="mb-3">
                                            <a href="{{ $prototypeModificationEvidenceFormUrl }}" target="_blank" rel="noopener noreferrer" class="small">
                                                Current link (open)
                                            </a>
                                        </div>
                                    @endif

                                    <button type="submit" class="btn btn-primary btn-sm">Save link</button>
                                </form>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header"><h6 class="mb-0">Publishing</h6></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status" required>
                                        <option value="draft" @selected(old('status', $competition->status) === 'draft')>Draft</option>
                                        <option value="published" @selected(old('status', $competition->status) === 'published')>Published</option>
                                        <option value="ongoing" @selected(old('status', $competition->status) === 'ongoing')>Ongoing</option>
                                        <option value="judging" @selected(old('status', $competition->status) === 'judging')>Judging</option>
                                        <option value="completed" @selected(old('status', $competition->status) === 'completed')>Completed</option>
                                        <option value="cancelled" @selected(old('status', $competition->status) === 'cancelled')>Cancelled</option>
                                    </select>
                                </div>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                           {{ old('is_active', $competition->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Update Competition</button>
                            <a href="{{ route('admin.competitions.show', $competition) }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('admin.competitions.partials.competition-phase-builder')
    @include('admin.competitions.partials.competition-form-tinymce')
</x-admin-layout>

