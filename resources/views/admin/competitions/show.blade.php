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
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Cover & Details -->
                    <div class="card mb-3">
                        @if($competition->cover_image)
                            <img src="{{ Storage::url($competition->cover_image) }}" class="card-img-top" alt="{{ $competition->name }}" style="max-height: 300px; object-fit: cover;">
                        @endif
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Description</h6>
                            <p>{{ $competition->description ?: 'No description provided.' }}</p>
                        </div>
                    </div>

                    <!-- Terms & Conditions -->
                    @if($competition->terms_and_conditions)
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="ti ti-file-certificate me-2"></i>Terms and Conditions</h6>
                            </div>
                            <div class="card-body">
                                <div class="prose">{!! nl2br(e($competition->terms_and_conditions)) !!}</div>
                            </div>
                        </div>
                    @endif

                    <!-- Instructions -->
                    @if($competition->instructions)
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="ti ti-list-check me-2"></i>How to Participate</h6>
                            </div>
                            <div class="card-body">
                                <div class="prose">{!! nl2br(e($competition->instructions)) !!}</div>
                            </div>
                        </div>
                    @endif

                    <!-- Awards -->
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="ti ti-trophy me-2"></i>Awards ({{ $competition->awards->count() }})</h6>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addAwardModal">
                                <i class="ti ti-plus"></i> Add Award
                            </button>
                        </div>
                        <div class="card-body">
                            @if($competition->awards->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Position</th>
                                                <th>Title</th>
                                                <th>Prize Type</th>
                                                <th>Value</th>
                                                <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($competition->awards as $award)
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-{{ $award->position <= 3 ? ['', 'warning', 'secondary', 'danger'][$award->position] : 'light text-dark' }}">
                                                            {{ $award->position_label }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-2">
                                                            @if($award->prize_image)
                                                                <img src="{{ Storage::url($award->prize_image) }}" alt="" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                                            @endif
                                                            <div>
                                                                <strong>{{ $award->title }}</strong>
                                                                @if($award->description)
                                                                    <div class="small text-muted">{{ Str::limit($award->description, 50) }}</div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>{{ $award->prize_type ?: '—' }}</td>
                                                    <td>{{ $award->formatted_prize }}</td>
                                                    <td class="text-center">
                                                        <div class="btn-group btn-group-sm">
                                                            <button class="btn btn-outline-primary" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#editAwardModal"
                                                                    data-award="{{ json_encode($award) }}"
                                                                    data-action="{{ route('admin.competitions.awards.update', [$competition, $award]) }}">
                                                                <i class="ti ti-edit"></i>
                                                            </button>
                                                            <button class="btn btn-outline-danger" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#deleteAwardModal"
                                                                    data-action="{{ route('admin.competitions.awards.destroy', [$competition, $award]) }}"
                                                                    data-title="{{ $award->title }}">
                                                                <i class="ti ti-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="ti ti-trophy fs-1 d-block mb-2"></i>
                                    No awards added yet. Click "Add Award" to create one.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Participants -->
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="ti ti-users me-2"></i>Participants ({{ $competition->participants->count() }})</h6>
                            <a href="{{ route('admin.competitions.participants.index', $competition) }}" class="btn btn-sm btn-primary">
                                <i class="ti ti-external-link"></i> Manage Participants
                            </a>
                        </div>
                        <div class="card-body">
                            @if($competition->participants->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Team</th>
                                                <th>School</th>
                                                <th>Teacher</th>
                                                <th>Students</th>
                                                <th>Status</th>
                                                <th>Registered</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($competition->participants as $participant)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $participant->team_name ?: 'Team #'.$participant->id }}</td>
                                                    <td>{{ $participant->teacherClub->school->name ?? '—' }}</td>
                                                    <td>{{ $participant->teacherClub->user->name ?? '—' }}</td>
                                                    <td>
                                                        <span class="badge bg-info">{{ $participant->participantStudents->count() }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $participant->status_badge }}">
                                                            {{ ucfirst($participant->status) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $participant->registered_at->format('d M Y') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="ti ti-users fs-1 d-block mb-2"></i>
                                    No participants registered yet.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Quick Info -->
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
                                    <span class="text-muted">Participants</span>
                                    <span class="fw-semibold">{{ $competition->participants_count }}</span>
                                </li>
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">Awards</span>
                                    <span class="fw-semibold">{{ $competition->awards_count }}</span>
                                </li>
                                <li class="d-flex justify-content-between py-2">
                                    <span class="text-muted">Created by</span>
                                    <span class="fw-semibold">{{ $competition->creator->name ?? 'Unknown' }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="ti ti-calendar me-2"></i>Timeline</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                @if($competition->registration_start)
                                    <li class="d-flex justify-content-between py-2 border-bottom">
                                        <span class="text-muted">Registration Opens</span>
                                        <span>{{ $competition->registration_start->format('d M Y, H:i') }}</span>
                                    </li>
                                @endif
                                @if($competition->registration_deadline)
                                    <li class="d-flex justify-content-between py-2 border-bottom">
                                        <span class="text-muted">Registration Closes</span>
                                        <span>{{ $competition->registration_deadline->format('d M Y, H:i') }}</span>
                                    </li>
                                @endif
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">Competition Start</span>
                                    <span class="text-success fw-semibold">{{ $competition->competition_start->format('d M Y, H:i') }}</span>
                                </li>
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">Competition End</span>
                                    <span class="text-danger fw-semibold">{{ $competition->competition_end->format('d M Y, H:i') }}</span>
                                </li>
                                @if($competition->results_announcement_date)
                                    <li class="d-flex justify-content-between py-2">
                                        <span class="text-muted">Results</span>
                                        <span>{{ $competition->results_announcement_date->format('d M Y, H:i') }}</span>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <!-- Requirements -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="ti ti-adjustments me-2"></i>Requirements</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">Team Size</span>
                                    <span class="fw-semibold">
                                        {{ $competition->min_members }}{{ $competition->max_members ? ' - '.$competition->max_members : '+' }} members
                                    </span>
                                </li>
                                <li class="d-flex justify-content-between py-2">
                                    <span class="text-muted">Age Range</span>
                                    <span class="fw-semibold">
                                        @if($competition->min_age || $competition->max_age)
                                            {{ $competition->min_age ?? 'Any' }} - {{ $competition->max_age ?? 'Any' }} years
                                        @else
                                            No restriction
                                        @endif
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="ti ti-bolt me-2"></i>Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.competitions.update-status', $competition) }}" method="POST" class="mb-3">
                                @csrf
                                <label class="form-label small">Change Status</label>
                                <div class="input-group">
                                    <select name="status" class="form-select">
                                        <option value="draft" {{ $competition->status == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="published" {{ $competition->status == 'published' ? 'selected' : '' }}>Published</option>
                                        <option value="ongoing" {{ $competition->status == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                                        <option value="judging" {{ $competition->status == 'judging' ? 'selected' : '' }}>Judging</option>
                                        <option value="completed" {{ $competition->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ $competition->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    <button type="submit" class="btn btn-outline-primary">Update</button>
                                </div>
                            </form>
                            <div class="d-grid">
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

    <!-- Add Award Modal -->
    <div class="modal fade" id="addAwardModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Award</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.competitions.awards.store', $competition) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Position <span class="text-danger">*</span></label>
                                <input type="number" name="position" class="form-control" required min="1" value="{{ $competition->awards->count() + 1 }}">
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" required placeholder="e.g., Gold Medal">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Award description..."></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Prize Type</label>
                                <input type="text" name="prize_type" class="form-control" placeholder="e.g., Cash, Certificate">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Prize Value (TZS)</label>
                                <input type="number" name="prize_value" class="form-control" min="0" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Prize Image</label>
                            <input type="file" name="prize_image" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Award</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Award Modal -->
    <div class="modal fade" id="editAwardModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Award</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editAwardForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Position <span class="text-danger">*</span></label>
                                <input type="number" name="position" id="editPosition" class="form-control" required min="1">
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="editTitle" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="editDescription" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Prize Type</label>
                                <input type="text" name="prize_type" id="editPrizeType" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Prize Value (TZS)</label>
                                <input type="number" name="prize_value" id="editPrizeValue" class="form-control" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Prize Image</label>
                            <input type="file" name="prize_image" class="form-control" accept="image/*">
                            <small class="text-muted">Leave empty to keep current image</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Award</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Award Modal -->
    <div class="modal fade" id="deleteAwardModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Award</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="deleteAwardForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <p class="mb-0">Are you sure you want to delete <strong id="deleteAwardTitle"></strong>?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Competition Modal -->
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
                        <p class="mb-0">Are you sure you want to delete <strong>{{ $competition->name }}</strong>? This will also delete all awards, participants, and submissions. This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Competition</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Edit Award Modal
                const editAwardModal = document.getElementById('editAwardModal');
                editAwardModal?.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const award = JSON.parse(button.getAttribute('data-award'));
                    const action = button.getAttribute('data-action');

                    document.getElementById('editAwardForm').action = action;
                    document.getElementById('editPosition').value = award.position;
                    document.getElementById('editTitle').value = award.title;
                    document.getElementById('editDescription').value = award.description || '';
                    document.getElementById('editPrizeType').value = award.prize_type || '';
                    document.getElementById('editPrizeValue').value = award.prize_value || '';
                });

                // Delete Award Modal
                const deleteAwardModal = document.getElementById('deleteAwardModal');
                deleteAwardModal?.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    document.getElementById('deleteAwardForm').action = button.getAttribute('data-action');
                    document.getElementById('deleteAwardTitle').textContent = button.getAttribute('data-title');
                });
            });
        </script>
    @endpush
</x-admin-layout>

