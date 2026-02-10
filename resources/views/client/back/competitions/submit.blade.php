<x-app-layout>
    <div class="col-lg-9">

        <div class="dashboard-content-header mb-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('client.competitions.index') }}">Competitions</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('client.competitions.show', $competition) }}">{{ $competition->name }}</a></li>
                    <li class="breadcrumb-item active">Submit</li>
                </ol>
            </nav>
            <h2 class="title mb-0">Submit Your Work</h2>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <form action="{{ route('client.competitions.submit.store', $competition) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="background-color: #f8d7da; border-color: #f5c6cb;">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-exclamation-triangle me-2 fs-5 mt-1"></i>
                                <div>
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Basic Info -->
                    <div class="card border-0 shadow-none mb-4">
                        <div class="card-header bg-light-soft">
                            <h6 class="mb-0"><i class="bi bi-file-text me-2"></i>Submission Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                       id="title" name="title" 
                                       value="{{ old('title', $existingSubmission?->title) }}" required
                                       placeholder="Give your project/experiment a title">
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description / Overview</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="4"
                                          placeholder="Provide an overview of your project or experiment...">{{ old('description', $existingSubmission?->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Experiment Details -->
                    <div class="card border-0 shadow-none mb-4">
                        <div class="card-header bg-light-soft">
                            <h6 class="mb-0"><i class="bi bi-flask me-2"></i>Experiment Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="methodology" class="form-label">Methodology / Process</label>
                                <textarea class="form-control @error('methodology') is-invalid @enderror" 
                                          id="methodology" name="methodology" rows="5"
                                          placeholder="Describe how you conducted the experiment. What steps did you follow?">{{ old('methodology', $existingSubmission?->methodology) }}</textarea>
                                @error('methodology')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Include step-by-step instructions of your process.</small>
                            </div>

                            <div class="mb-3">
                                <label for="materials_used" class="form-label">Materials / Equipment Used</label>
                                <textarea class="form-control @error('materials_used') is-invalid @enderror" 
                                          id="materials_used" name="materials_used" rows="3"
                                          placeholder="List the materials and equipment you used...">{{ old('materials_used', $existingSubmission?->materials_used) }}</textarea>
                                @error('materials_used')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="results" class="form-label">Results / Findings</label>
                                <textarea class="form-control @error('results') is-invalid @enderror" 
                                          id="results" name="results" rows="5"
                                          placeholder="What did you discover? What were the outcomes?">{{ old('results', $existingSubmission?->results) }}</textarea>
                                @error('results')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="conclusion" class="form-label">Conclusion</label>
                                <textarea class="form-control @error('conclusion') is-invalid @enderror" 
                                          id="conclusion" name="conclusion" rows="4"
                                          placeholder="Summarize your conclusions and what you learned...">{{ old('conclusion', $existingSubmission?->conclusion) }}</textarea>
                                @error('conclusion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="card border-0 shadow-none mb-4">
                        <div class="card-header bg-light-soft">
                            <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Additional Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="challenges_faced" class="form-label">Challenges Faced</label>
                                <textarea class="form-control @error('challenges_faced') is-invalid @enderror" 
                                          id="challenges_faced" name="challenges_faced" rows="3"
                                          placeholder="What challenges did you encounter and how did you overcome them?">{{ old('challenges_faced', $existingSubmission?->challenges_faced) }}</textarea>
                                @error('challenges_faced')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-0">
                                <label for="learning_outcomes" class="form-label">Learning Outcomes</label>
                                <textarea class="form-control @error('learning_outcomes') is-invalid @enderror" 
                                          id="learning_outcomes" name="learning_outcomes" rows="3"
                                          placeholder="What did your team learn from this experience?">{{ old('learning_outcomes', $existingSubmission?->learning_outcomes) }}</textarea>
                                @error('learning_outcomes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- File Uploads -->
                    <div class="card border-0 shadow-none mb-4">
                        <div class="card-header bg-light-soft">
                            <h6 class="mb-0"><i class="bi bi-paperclip me-2"></i>Attachments</h6>
                        </div>
                        <div class="card-body">
                            <!-- Existing Attachments -->
                            @if($existingSubmission && $existingSubmission->attachments->count() > 0)
                                <div class="mb-4">
                                    <h6 class="small fw-semibold mb-2">Current Attachments</h6>
                                    <div class="row g-2">
                                        @foreach($existingSubmission->attachments as $attachment)
                                            <div class="col-md-6">
                                                <div class="border rounded p-2 d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <i class="{{ $attachment->icon_class }} fs-4 me-2"></i>
                                                        <div>
                                                            <small class="d-block">{{ Str::limit($attachment->original_filename, 30) }}</small>
                                                            <span class="badge bg-light text-dark">{{ $attachment->formatted_size }}</span>
                                                        </div>
                                                    </div>
                                                    <form action="{{ route('client.competitions.attachment.delete', [$competition, $attachment]) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this attachment?')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- New Uploads -->
                            <div class="mb-3">
                                <label class="form-label">Upload Files</label>
                                <input type="file" class="form-control @error('attachments.*') is-invalid @enderror" 
                                       name="attachments[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.mp4,.mov">
                                @error('attachments.*')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    Supported: Images (JPG, PNG, GIF), Documents (PDF, DOC), Videos (MP4, MOV). Max 20MB per file.
                                </small>
                            </div>

                            <div class="alert alert-info small mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>What to upload:</strong>
                                <ul class="mb-0 mt-1">
                                    <li>Photos of your experiment setup and process</li>
                                    <li>Photos of students conducting the experiment</li>
                                    <li>PDF reports or documentation</li>
                                    <li>Any charts, graphs, or visual data</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Actions -->
                    <div class="card border-0 shadow-none">
                        <div class="card-body">
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="submit" name="submit_final" value="0" class="btn btn-primary text-white">
                                    <i class="bi bi-save me-1"></i> Save as Draft
                                </button>
                                <button type="submit" name="submit_final" value="1" class="btn btn-success"
                                        onclick="return confirm('Are you sure you want to submit? You won\'t be able to edit after submission.')">
                                    <i class="bi bi-send me-1"></i> Submit for Review
                                </button>
                                <a href="{{ route('client.competitions.show', $competition) }}" class="btn btn-outline-secondary">
                                    Cancel
                                </a>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="bi bi-info-circle me-1"></i>
                                Save as draft to continue later, or submit when ready for judging.
                            </small>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-none mb-4 sticky-top" style="top: 20px;">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Competition Info</h6>
                    </div>
                    <div class="card-body">
                        <h6 class="fw-semibold">{{ $competition->name }}</h6>
                        <p class="text-muted small mb-3">{{ $competition->club->name }}</p>
                        
                        <div class="mb-3">
                            <strong class="small">Deadline:</strong>
                            <div class="text-danger">{{ $competition->competition_end->format('d M Y, H:i') }}</div>
                        </div>

                        <div class="mb-3">
                            <strong class="small">Your Team:</strong>
                            <div>{{ $participant->team_name ?: 'Team #'.$participant->id }}</div>
                        </div>

                        @if($existingSubmission)
                            <div class="alert alert-warning small mb-0">
                                <i class="bi bi-pencil me-2"></i>
                                You have a draft submission. Continue editing or submit when ready.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

