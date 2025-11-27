<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">{{ $submission->title }}</h5>
                    <p class="text-muted mb-0">
                        <span class="badge bg-{{ $submission->status_badge }}">{{ ucfirst(str_replace('_', ' ', $submission->status)) }}</span>
                        {{ $submission->participant->team_name ?: 'Team #'.$submission->participant->id }}
                    </p>
                </div>
                <a href="{{ route('admin.competitions.submissions.index', $competition) }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left"></i> Back to Submissions
                </a>
            </div>

            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Submission Content -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="ti ti-file-description me-2"></i>Submission Content</h6>
                        </div>
                        <div class="card-body">
                            @if($submission->description)
                                <div class="mb-4">
                                    <h6 class="text-muted">Description</h6>
                                    <div class="border-start border-3 border-primary ps-3">
                                        {!! nl2br(e($submission->description)) !!}
                                    </div>
                                </div>
                            @endif

                            @if($submission->methodology)
                                <div class="mb-4">
                                    <h6 class="text-muted">Methodology</h6>
                                    <div class="border-start border-3 border-info ps-3">
                                        {!! nl2br(e($submission->methodology)) !!}
                                    </div>
                                </div>
                            @endif

                            @if($submission->results)
                                <div class="mb-4">
                                    <h6 class="text-muted">Results / Findings</h6>
                                    <div class="border-start border-3 border-success ps-3">
                                        {!! nl2br(e($submission->results)) !!}
                                    </div>
                                </div>
                            @endif

                            @if($submission->conclusion)
                                <div class="mb-4">
                                    <h6 class="text-muted">Conclusion</h6>
                                    <div class="border-start border-3 border-warning ps-3">
                                        {!! nl2br(e($submission->conclusion)) !!}
                                    </div>
                                </div>
                            @endif

                            @if($submission->materials_used)
                                <div class="mb-4">
                                    <h6 class="text-muted">Materials Used</h6>
                                    <p class="mb-0">{{ $submission->materials_used }}</p>
                                </div>
                            @endif

                            @if($submission->challenges_faced)
                                <div class="mb-4">
                                    <h6 class="text-muted">Challenges Faced</h6>
                                    <p class="mb-0">{{ $submission->challenges_faced }}</p>
                                </div>
                            @endif

                            @if($submission->learning_outcomes)
                                <div class="mb-0">
                                    <h6 class="text-muted">Learning Outcomes</h6>
                                    <p class="mb-0">{{ $submission->learning_outcomes }}</p>
                                </div>
                            @endif

                            @if(!$submission->description && !$submission->methodology && !$submission->results)
                                <p class="text-muted mb-0">No content provided.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Attachments -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="ti ti-paperclip me-2"></i>Attachments ({{ $submission->attachments->count() }})</h6>
                        </div>
                        <div class="card-body">
                            @if($submission->attachments->count() > 0)
                                <!-- Images -->
                                @php $images = $submission->attachments->where('type', 'image'); @endphp
                                @if($images->count() > 0)
                                    <h6 class="mb-3">Images ({{ $images->count() }})</h6>
                                    <div class="row g-3 mb-4">
                                        @foreach($images as $attachment)
                                            <div class="col-md-4 col-sm-6">
                                                <div class="card h-100">
                                                    <a href="{{ Storage::url($attachment->file_path) }}" target="_blank">
                                                        <img src="{{ Storage::url($attachment->file_path) }}" 
                                                             class="card-img-top" 
                                                             alt="{{ $attachment->caption ?: $attachment->original_filename }}"
                                                             style="height: 200px; object-fit: cover;">
                                                    </a>
                                                    <div class="card-body py-2">
                                                        <p class="card-text small mb-1">{{ $attachment->caption ?: $attachment->original_filename }}</p>
                                                        @if($attachment->category)
                                                            <span class="badge bg-light text-dark">{{ $attachment->category }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <!-- PDFs and Documents -->
                                @php $docs = $submission->attachments->whereIn('type', ['pdf', 'document']); @endphp
                                @if($docs->count() > 0)
                                    <h6 class="mb-3">Documents ({{ $docs->count() }})</h6>
                                    <div class="list-group mb-4">
                                        @foreach($docs as $attachment)
                                            <a href="{{ Storage::url($attachment->file_path) }}" 
                                               target="_blank"
                                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="{{ $attachment->icon_class }} fs-4 me-2"></i>
                                                    <span>{{ $attachment->original_filename }}</span>
                                                    @if($attachment->caption)
                                                        <small class="text-muted d-block ms-4">{{ $attachment->caption }}</small>
                                                    @endif
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-light text-dark">{{ $attachment->formatted_size }}</span>
                                                    <i class="ti ti-download ms-2"></i>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif

                                <!-- Other attachments -->
                                @php $others = $submission->attachments->whereNotIn('type', ['image', 'pdf', 'document']); @endphp
                                @if($others->count() > 0)
                                    <h6 class="mb-3">Other Files ({{ $others->count() }})</h6>
                                    <div class="list-group">
                                        @foreach($others as $attachment)
                                            <a href="{{ Storage::url($attachment->file_path) }}" 
                                               target="_blank"
                                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="{{ $attachment->icon_class }} fs-4 me-2"></i>
                                                    <span>{{ $attachment->original_filename }}</span>
                                                </div>
                                                <span class="badge bg-light text-dark">{{ $attachment->formatted_size }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            @else
                                <p class="text-muted mb-0">No attachments uploaded.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Previous Feedback -->
                    @if($submission->judge_feedback)
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="ti ti-message me-2"></i>Judge Feedback</h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info mb-0">
                                    <p class="mb-2">{!! nl2br(e($submission->judge_feedback)) !!}</p>
                                    <small class="text-muted">
                                        By {{ $submission->judge->name ?? 'Unknown' }} on {{ $submission->judged_at?->format('d M Y, H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Team Info -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="ti ti-users me-2"></i>Team Information</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-2"><strong>Team:</strong> {{ $submission->participant->team_name ?: 'Team #'.$submission->participant->id }}</p>
                            <p class="mb-2"><strong>School:</strong> {{ $submission->participant->teacherClub->school->name ?? '—' }}</p>
                            <p class="mb-2"><strong>Teacher:</strong> {{ $submission->participant->teacherClub->user->name ?? '—' }}</p>
                            <hr>
                            <p class="mb-2"><strong>Students:</strong></p>
                            <ul class="list-unstyled mb-0">
                                @foreach($submission->participant->participantStudents as $ps)
                                    <li class="mb-1">
                                        <i class="ti ti-user text-muted"></i> {{ $ps->student->name }}
                                        <small class="text-muted">({{ $ps->student->age }} years)</small>
                                    </li>
                                @endforeach
                            </ul>
                            <hr>
                            <a href="{{ route('admin.competitions.participants.show', [$competition, $submission->participant]) }}" class="btn btn-outline-primary btn-sm w-100">
                                View Full Team Details
                            </a>
                        </div>
                    </div>

                    <!-- Submission Info -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="ti ti-info-circle me-2"></i>Submission Info</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">Status</span>
                                    <span class="badge bg-{{ $submission->status_badge }}">{{ ucfirst(str_replace('_', ' ', $submission->status)) }}</span>
                                </li>
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">Submitted</span>
                                    <span>{{ $submission->submitted_at?->format('d M Y, H:i') ?? 'Not submitted' }}</span>
                                </li>
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">Version</span>
                                    <span>{{ $submission->version }}</span>
                                </li>
                                <li class="d-flex justify-content-between py-2">
                                    <span class="text-muted">Attachments</span>
                                    <span>{{ $submission->attachments->count() }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Judging Panel -->
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="ti ti-gavel me-2"></i>Judge This Submission</h6>
                        </div>
                        <div class="card-body">
                            @if($submission->score !== null)
                                <div class="text-center mb-3">
                                    <div class="display-4 fw-bold text-primary">{{ number_format($submission->score, 1) }}</div>
                                    <p class="text-muted mb-0">Current Score</p>
                                </div>
                            @endif

                            <form action="{{ route('admin.competitions.submissions.judge', [$competition, $submission]) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Score (0-100) <span class="text-danger">*</span></label>
                                    <input type="number" name="score" class="form-control form-control-lg text-center" 
                                           value="{{ $submission->score }}" min="0" max="100" step="0.1" required
                                           placeholder="0 - 100">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Feedback / Comments</label>
                                    <textarea name="judge_feedback" class="form-control" rows="4" 
                                              placeholder="Provide feedback for the team...">{{ $submission->judge_feedback }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ti ti-check"></i> {{ $submission->score !== null ? 'Update Score' : 'Submit Score' }}
                                </button>
                            </form>

                            <hr>

                            <!-- Quick Actions -->
                            @if($submission->status === 'submitted')
                                <form action="{{ route('admin.competitions.submissions.mark-under-review', [$competition, $submission]) }}" method="POST" class="mb-2">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-warning w-100">
                                        <i class="ti ti-eye"></i> Mark as Under Review
                                    </button>
                                </form>
                            @endif

                            <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#revisionModal">
                                <i class="ti ti-edit"></i> Request Revision
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revision Modal -->
    <div class="modal fade" id="revisionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Request Revision</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.competitions.submissions.request-revision', [$competition, $submission]) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted">Request the team to revise and resubmit their work.</p>
                        <div class="mb-0">
                            <label class="form-label">What needs to be revised? <span class="text-danger">*</span></label>
                            <textarea name="judge_feedback" class="form-control" rows="4" required 
                                      placeholder="Explain what changes or improvements are needed..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Request Revision</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>

