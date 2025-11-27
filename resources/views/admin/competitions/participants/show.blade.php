<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">{{ $participant->team_name ?: 'Team #'.$participant->id }}</h5>
                    <p class="text-muted mb-0">
                        <span class="badge bg-{{ $participant->status_badge }}">{{ ucfirst($participant->status) }}</span>
                        {{ $competition->name }}
                    </p>
                </div>
                <a href="{{ route('admin.competitions.participants.index', $competition) }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left"></i> Back to Participants
                </a>
            </div>

            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Team Info -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="ti ti-users me-2"></i>Team Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>Team Name:</strong> {{ $participant->team_name ?: 'Not specified' }}</p>
                                    <p class="mb-2"><strong>School:</strong> {{ $participant->teacherClub->school->name ?? '—' }}</p>
                                    <p class="mb-2"><strong>Teacher:</strong> {{ $participant->teacherClub->user->name ?? '—' }}</p>
                                    <p class="mb-0"><strong>Club:</strong> {{ $participant->teacherClub->club->name ?? '—' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>Registered:</strong> {{ $participant->registered_at->format('d M Y, H:i') }}</p>
                                    @if($participant->approved_at)
                                        <p class="mb-2"><strong>Approved:</strong> {{ $participant->approved_at->format('d M Y, H:i') }}</p>
                                    @endif
                                    <p class="mb-2"><strong>Status:</strong> <span class="badge bg-{{ $participant->status_badge }}">{{ ucfirst($participant->status) }}</span></p>
                                    @if($participant->rejection_reason)
                                        <p class="mb-0 text-danger"><strong>Rejection Reason:</strong> {{ $participant->rejection_reason }}</p>
                                    @endif
                                </div>
                            </div>
                            @if($participant->team_description)
                                <hr>
                                <p class="mb-0"><strong>Team Description:</strong></p>
                                <p class="mb-0">{{ $participant->team_description }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Students -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="ti ti-school me-2"></i>Participating Students ({{ $participant->participantStudents->count() }})</h6>
                        </div>
                        <div class="card-body">
                            @if($participant->participantStudents->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Age</th>
                                                <th>Gender</th>
                                                <th>Class</th>
                                                <th>Role</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($participant->participantStudents as $ps)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $ps->student->name }}</td>
                                                    <td>
                                                        {{ $ps->student->age }} years
                                                        @if($competition->min_age && $ps->student->age < $competition->min_age)
                                                            <i class="ti ti-alert-triangle text-danger" title="Below minimum age"></i>
                                                        @elseif($competition->max_age && $ps->student->age > $competition->max_age)
                                                            <i class="ti ti-alert-triangle text-danger" title="Above maximum age"></i>
                                                        @endif
                                                    </td>
                                                    <td>{{ ucfirst($ps->student->gender) }}</td>
                                                    <td>{{ $ps->student->schoolClass->name ?? '—' }}</td>
                                                    <td>{{ $ps->role ?? '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Requirements Check -->
                                <div class="alert alert-light mt-3 mb-0">
                                    <h6 class="alert-heading">Requirements Check</h6>
                                    <ul class="mb-0">
                                        <li>
                                            Team Size: {{ $participant->participantStudents->count() }} / {{ $competition->min_members }}-{{ $competition->max_members ?? '∞' }} members
                                            @if($participant->participantStudents->count() >= $competition->min_members && (!$competition->max_members || $participant->participantStudents->count() <= $competition->max_members))
                                                <i class="ti ti-check text-success"></i>
                                            @else
                                                <i class="ti ti-x text-danger"></i>
                                            @endif
                                        </li>
                                        @if($competition->min_age || $competition->max_age)
                                            <li>
                                                Age Range: {{ $competition->min_age ?? 'Any' }}-{{ $competition->max_age ?? 'Any' }} years
                                                @php
                                                    $ageOk = true;
                                                    foreach($participant->participantStudents as $ps) {
                                                        if(($competition->min_age && $ps->student->age < $competition->min_age) || 
                                                           ($competition->max_age && $ps->student->age > $competition->max_age)) {
                                                            $ageOk = false;
                                                            break;
                                                        }
                                                    }
                                                @endphp
                                                @if($ageOk)
                                                    <i class="ti ti-check text-success"></i>
                                                @else
                                                    <i class="ti ti-x text-danger"></i>
                                                @endif
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            @else
                                <p class="text-muted mb-0">No students registered for this team.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Submissions -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="ti ti-file-upload me-2"></i>Submissions ({{ $participant->submissions->count() }})</h6>
                        </div>
                        <div class="card-body">
                            @if($participant->submissions->count() > 0)
                                @foreach($participant->submissions as $submission)
                                    <div class="border rounded p-3 mb-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">{{ $submission->title }}</h6>
                                                <p class="text-muted small mb-2">
                                                    Submitted: {{ $submission->submitted_at?->format('d M Y, H:i') ?? 'Draft' }}
                                                    <span class="badge bg-{{ $submission->status_badge }} ms-2">{{ ucfirst($submission->status) }}</span>
                                                </p>
                                            </div>
                                            <a href="{{ route('admin.competitions.submissions.show', [$competition, $submission]) }}" class="btn btn-sm btn-outline-primary">
                                                View Details
                                            </a>
                                        </div>
                                        @if($submission->description)
                                            <p class="mb-2 small">{{ Str::limit($submission->description, 200) }}</p>
                                        @endif
                                        @if($submission->score !== null)
                                            <div class="d-flex align-items-center gap-3">
                                                <span class="badge bg-primary fs-6">Score: {{ number_format($submission->score, 1) }}</span>
                                            </div>
                                        @endif
                                        @if($submission->attachments->count() > 0)
                                            <div class="mt-2">
                                                <small class="text-muted">{{ $submission->attachments->count() }} attachment(s)</small>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted mb-0">No submissions yet.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Scoring -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="ti ti-star me-2"></i>Scoring</h6>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <div class="display-4 fw-bold text-primary">
                                    {{ $participant->final_score !== null ? number_format($participant->final_score, 1) : '—' }}
                                </div>
                                <p class="text-muted mb-0">Final Score</p>
                                @if($participant->rank)
                                    <span class="badge bg-{{ $participant->rank <= 3 ? ['', 'warning', 'secondary', 'danger'][$participant->rank] : 'light text-dark' }} fs-6 mt-2">
                                        Rank #{{ $participant->rank }}
                                    </span>
                                @endif
                            </div>

                            <form action="{{ route('admin.competitions.participants.update-score', [$competition, $participant]) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Update Score</label>
                                    <input type="number" name="final_score" class="form-control" 
                                           value="{{ $participant->final_score }}" min="0" max="100" step="0.1"
                                           placeholder="0 - 100">
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Update Score</button>
                            </form>
                        </div>
                    </div>

                    <!-- Award Assignment -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="ti ti-trophy me-2"></i>Award</h6>
                        </div>
                        <div class="card-body">
                            @if($participant->award)
                                <div class="text-center mb-3">
                                    <div class="badge bg-warning text-dark fs-5 p-2">
                                        <i class="ti ti-trophy me-1"></i> {{ $participant->award->title }}
                                    </div>
                                    <p class="text-muted small mb-0">{{ $participant->award->position_label }}</p>
                                </div>
                            @endif

                            <form action="{{ route('admin.competitions.participants.assign-award', [$competition, $participant]) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Assign Award</label>
                                    <select name="award_id" class="form-select">
                                        <option value="">No Award</option>
                                        @foreach($competition->awards as $award)
                                            <option value="{{ $award->id }}" {{ $participant->award_id == $award->id ? 'selected' : '' }}>
                                                {{ $award->position_label }} - {{ $award->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-warning w-100">Update Award</button>
                            </form>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="ti ti-settings me-2"></i>Actions</h6>
                        </div>
                        <div class="card-body">
                            @if($participant->status === 'pending')
                                <form action="{{ route('admin.competitions.participants.approve', [$competition, $participant]) }}" method="POST" class="mb-2">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="ti ti-check"></i> Approve Participant
                                    </button>
                                </form>
                                <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                    <i class="ti ti-x"></i> Reject Participant
                                </button>
                            @elseif($participant->status === 'approved')
                                <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#disqualifyModal">
                                    <i class="ti ti-ban"></i> Disqualify
                                </button>
                            @else
                                <p class="text-muted small mb-0">No actions available for {{ $participant->status }} participants.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Participant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.competitions.participants.reject', [$competition, $participant]) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-0">
                            <label class="form-label">Reason for rejection <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" class="form-control" rows="3" required 
                                      placeholder="Explain why this participant is being rejected..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Disqualify Modal -->
    <div class="modal fade" id="disqualifyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Disqualify Participant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.competitions.participants.disqualify', [$competition, $participant]) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="text-danger"><i class="ti ti-alert-triangle"></i> This action will disqualify the team from the competition.</p>
                        <div class="mb-0">
                            <label class="form-label">Reason for disqualification <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" class="form-control" rows="3" required 
                                      placeholder="Explain why this participant is being disqualified..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Disqualify</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>

