<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">{{ $competition->name }}</h5>
                    <p class="text-muted mb-0">
                        <span class="badge bg-{{ $competition->status_badge }}">{{ ucfirst($competition->status) }}</span>
                        @if(!$competition->is_active)
                            <span class="badge bg-dark">Disabled</span>
                        @endif
                        <span class="ms-2">{{ $competition->club->name }}</span>
                    </p>
                </div>
                <div>
                    <a href="{{ route('admin.competitions.edit', $competition) }}" class="btn btn-primary me-2">
                        <i class="ti ti-edit"></i> Edit
                    </a>
                    <a href="{{ route('admin.competitions.registrations.index', $competition) }}" class="btn btn-outline-success me-2">
                        <i class="ti ti-users"></i> Registrations
                    </a>
                    <a href="{{ route('admin.competitions.ideas.index', $competition) }}" class="btn btn-outline-warning me-2">
                        <i class="ti ti-bulb"></i> Ideas
                    </a>
                    <a href="{{ route('admin.competitions.prototype-shortlisting.index', $competition) }}" class="btn btn-outline-info me-2">
                        <i class="ti ti-list-check"></i> Prototype Submissions
                    </a>
                    <a href="{{ route('admin.competitions.prototype-modification.index', $competition) }}" class="btn btn-outline-dark me-2">
                        <i class="ti ti-adjustments"></i> Prototype Modifications
                    </a>
                    <a href="{{ route('admin.competitions.pitch-winner.index', $competition) }}" class="btn btn-outline-success me-2">
                        <i class="ti ti-microphone"></i> Pitch & Winner
                    </a>
                    <a href="{{ route('admin.competitions.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-lg-8">
                    <div class="card mb-3">
                        @if($competition->cover_image)
                            <img
                                src="{{ Storage::url($competition->cover_image) }}"
                                class="card-img-top"
                                alt="{{ $competition->name }}"
                                style="max-height: 300px; object-fit: cover;"
                            >
                        @endif
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Description</h6>
                            <p class="mb-0">{{ $competition->description ?: 'No description provided.' }}</p>
                        </div>
                    </div>

                    @if($competition->terms_and_conditions)
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="ti ti-file-certificate me-2"></i>Terms and Conditions</h6>
                            </div>
                            <div class="card-body">
                                <div>{!! nl2br(e($competition->terms_and_conditions)) !!}</div>
                            </div>
                        </div>
                    @endif

                    @if($competition->instructions)
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="ti ti-list-check me-2"></i>How to Participate</h6>
                            </div>
                            <div class="card-body">
                                <div>{!! nl2br(e($competition->instructions)) !!}</div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-lg-4">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="ti ti-info-circle me-2"></i>Quick Info</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">Club</span>
                                    <span class="fw-semibold">{{ $competition->club->name }}</span>
                                </li>
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">Status</span>
                                    <span class="badge bg-{{ $competition->status_badge }}">{{ ucfirst($competition->status) }}</span>
                                </li>
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">Created by</span>
                                    <span class="fw-semibold">{{ $competition->creator->name ?? 'Unknown' }}</span>
                                </li>
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">Phases</span>
                                    <span class="fw-semibold">{{ $competition->phases->count() }}</span>
                                </li>
                                <li class="d-flex justify-content-between py-2">
                                    <span class="text-muted">Created at</span>
                                    <span class="fw-semibold">{{ $competition->created_at?->format('d M Y, H:i') }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="ti ti-calendar me-2"></i>Timeline</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">Registration Opens</span>
                                    <span>{{ $competition->registration_start?->format('d M Y, H:i') ?? 'Not set' }}</span>
                                </li>
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">Registration Closes</span>
                                    <span>{{ $competition->registration_deadline?->format('d M Y, H:i') ?? 'Not set' }}</span>
                                </li>
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">Competition Start</span>
                                    <span>{{ $competition->competition_start?->format('d M Y, H:i') ?? 'Not set' }}</span>
                                </li>
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">Competition End</span>
                                    <span>{{ $competition->competition_end?->format('d M Y, H:i') ?? 'Not set' }}</span>
                                </li>
                                <li class="d-flex justify-content-between py-2">
                                    <span class="text-muted">Results</span>
                                    <span>{{ $competition->results_announcement_date?->format('d M Y, H:i') ?? 'Not set' }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="ti ti-adjustments me-2"></i>Requirements</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">Pool (min–max members)</span>
                                    <span class="fw-semibold">
                                        {{ $competition->min_members }}{{ $competition->max_members ? ' – '.$competition->max_members : '+' }}
                                    </span>
                                </li>
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">Representatives per school</span>
                                    <span class="fw-semibold">{{ $competition->representative_count ?? 10 }}</span>
                                </li>
                                <li class="d-flex justify-content-between py-2">
                                    <span class="text-muted">Age Range</span>
                                    <span class="fw-semibold">
                                        @if($competition->min_age || $competition->max_age)
                                            {{ $competition->min_age ?? 'Any' }} - {{ $competition->max_age ?? 'Any' }}
                                        @else
                                            No restriction
                                        @endif
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="ti ti-list me-2"></i>Phase Timeline</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                @foreach($competition->phases as $phase)
                                    <li class="d-flex justify-content-between py-2 border-bottom">
                                        <div>
                                            <div class="fw-semibold">{{ $phase->sequence }}. {{ $phase->name }}</div>
                                            <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $phase->status)) }}</small>
                                        </div>
                                        <small class="text-muted">{{ $phase->opens_at?->format('d M') ?? '—' }}</small>
                                    </li>
                                @endforeach
                            </ul>
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
                                    <div class="form-text">This link will be shown to teachers during `Prototype development`.</div>
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
                                    <div class="form-text">This link will be shown to teachers during `Prototype modification`.</div>
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
                        <div class="card-header">
                            <h6 class="mb-0"><i class="ti ti-school me-2"></i>Allowed Classes</h6>
                        </div>
                        <div class="card-body">
                            @if($competition->classRequirements->isEmpty())
                                <span class="text-muted">All classes allowed.</span>
                            @else
                                <div class="d-flex gap-2 flex-wrap">
                                    @foreach($competition->classRequirements as $classRequirement)
                                        <span class="badge bg-light text-dark">{{ $classRequirement->schoolClass->name }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="ti ti-bolt me-2"></i>Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.competitions.edit', $competition) }}" class="btn btn-outline-primary">
                                    <i class="ti ti-edit"></i> Edit Competition
                                </a>
                                <a href="{{ route('admin.competitions.prototype-shortlisting.index', $competition) }}" class="btn btn-outline-warning">
                                    <i class="ti ti-list-check"></i> Prototype Shortlisting
                                </a>
                                <a href="{{ route('admin.competitions.prototype-modification.index', $competition) }}" class="btn btn-outline-dark">
                                    <i class="ti ti-adjustments"></i> Prototype Modifications
                                </a>
                                <a href="{{ route('admin.competitions.pitch-winner.index', $competition) }}" class="btn btn-outline-success">
                                    <i class="ti ti-microphone"></i> Pitch & Winner
                                </a>
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteCompetitionModal">
                                    <i class="ti ti-trash"></i> Delete Competition
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteCompetitionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Competition</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.competitions.destroy', $competition) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <p class="mb-0">Are you sure you want to delete <strong>{{ $competition->name }}</strong>?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Competition</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>

