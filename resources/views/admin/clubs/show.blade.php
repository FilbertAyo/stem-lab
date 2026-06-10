<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">{{ $club->name }}</h5>
                    <p class="text-muted mb-0">Club Details & Management</p>
                </div>
                <a href="{{ route('admin.clubs.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>
                    Back to Clubs
                </a>
            </div>

            @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ti ti-check me-1"></i> {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ti ti-alert-circle me-1"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Club Information Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Club Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> {{ $club->name }}</p>
                            <p><strong>Status:</strong>
                                @if($club->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Description:</strong></p>
                            <p>{{ $club->description ?? 'No description provided.' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <ul class="nav nav-tabs mb-3" role="navigation" aria-label="Club management sections">
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'leaders' ? 'active' : '' }}"
                       href="{{ route('admin.clubs.show', ['club' => $club, 'tab' => 'leaders']) }}">
                        School Leaders
                        <span class="badge bg-light text-dark ms-1">{{ $leaders->total() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'members' ? 'active' : '' }}"
                       href="{{ route('admin.clubs.show', ['club' => $club, 'tab' => 'members']) }}">
                        Approved Members
                        <span class="badge bg-light text-dark ms-1">{{ $approvedMembers->total() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'requests' ? 'active' : '' }}"
                       href="{{ route('admin.clubs.show', ['club' => $club, 'tab' => 'requests']) }}">
                        Pending Requests
                        <span class="badge bg-light text-dark ms-1">{{ $pendingRequests->total() }}</span>
                    </a>
                </li>
            </ul>

            <div class="card mb-4">
                <div class="card-body p-0">
                    @if($activeTab === 'leaders')
                            @if($leaders->isEmpty())
                                <div class="text-center text-muted py-5">
                                    <i class="ti ti-users-off fs-1 d-block mb-2"></i>
                                    No leaders assigned yet.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover mb-0 align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 60px;">#</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>School</th>
                                                <th class="text-end" style="width: 140px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($leaders as $leader)
                                                <tr>
                                                    <td>{{ $leaders->firstItem() + $loop->index }}</td>
                                                    <td class="fw-semibold">{{ $leader->user->name }}</td>
                                                    <td>{{ $leader->user->email }}</td>
                                                    <td>{{ $leader->school->name ?? 'N/A' }}</td>
                                                    <td class="text-end">
                                                        <button class="btn btn-sm btn-outline-primary"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#transferLeadershipModal"
                                                                data-school-id="{{ $leader->school_id }}"
                                                                data-school-name="{{ $leader->school->name ?? 'N/A' }}">
                                                            <i class="ti ti-arrow-right me-1"></i>
                                                            Transfer
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if($leaders->hasPages())
                                    <div class="card-footer d-flex justify-content-end">
                                        {{ $leaders->links('pagination::bootstrap-5') }}
                                    </div>
                                @endif
                            @endif
                    @elseif($activeTab === 'members')
                            <div class="d-flex justify-content-end p-3 border-bottom bg-light">
                                <a href="{{ route('admin.clubs.members.export', $club) }}" class="btn btn-primary btn-sm">
                                    <i class="ti ti-download me-1"></i>
                                    Download List
                                </a>
                            </div>

                            @if($approvedMembers->isEmpty())
                                <div class="text-center text-muted py-5">
                                    <i class="ti ti-users-off fs-1 d-block mb-2"></i>
                                    No approved members yet.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover mb-0 align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 60px;">#</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>School</th>
                                                <th style="width: 140px;">Joined Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($approvedMembers as $member)
                                                <tr>
                                                    <td>{{ $approvedMembers->firstItem() + $loop->index }}</td>
                                                    <td class="fw-semibold">{{ $member->user->name }}</td>
                                                    <td>{{ $member->user->email }}</td>
                                                    <td>{{ $member->school->name ?? 'N/A' }}</td>
                                                    <td>{{ $member->created_at->format('M d, Y') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if($approvedMembers->hasPages())
                                    <div class="card-footer d-flex justify-content-end">
                                        {{ $approvedMembers->links('pagination::bootstrap-5') }}
                                    </div>
                                @endif
                            @endif
                    @else
                            @if($pendingRequests->isEmpty())
                                <div class="text-center text-muted py-5">
                                    <i class="ti ti-clock fs-1 d-block mb-2"></i>
                                    No pending requests.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-hover mb-0 align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 60px;">#</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>School</th>
                                                <th style="width: 140px;">Request Date</th>
                                                <th class="text-end" style="width: 180px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pendingRequests as $request)
                                                <tr>
                                                    <td>{{ $pendingRequests->firstItem() + $loop->index }}</td>
                                                    <td class="fw-semibold">{{ $request->user->name }}</td>
                                                    <td>{{ $request->user->email }}</td>
                                                    <td>{{ $request->school->name ?? 'N/A' }}</td>
                                                    <td>{{ $request->created_at->format('M d, Y') }}</td>
                                                    <td class="text-end">
                                                        <form method="POST" action="{{ route('admin.clubs.approve-request', $request) }}" class="d-inline me-1">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this request?')">
                                                                <i class="ti ti-check me-1"></i>
                                                                Approve
                                                            </button>
                                                        </form>
                                                        <form method="POST" action="{{ route('admin.clubs.reject-request', $request) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Reject this request?')">
                                                                <i class="ti ti-x me-1"></i>
                                                                Reject
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if($pendingRequests->hasPages())
                                    <div class="card-footer d-flex justify-content-end">
                                        {{ $pendingRequests->links('pagination::bootstrap-5') }}
                                    </div>
                                @endif
                            @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Transfer Leadership Modal -->
    <div class="modal fade" id="transferLeadershipModal" tabindex="-1" aria-labelledby="transferLeadershipModalLabel" aria-hidden="true" data-members-by-school='@json($membersBySchoolJson)'>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="transferLeadershipModalLabel">Transfer School Leadership</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.clubs.transfer-leadership', $club) }}">
                    @csrf
                    <input type="hidden" name="school_id" id="modal_school_id">
                    <div class="modal-body">
                        <p class="mb-3">Select a teacher from <strong id="modal_school_name"></strong> to transfer leadership to:</p>
                        <div class="mb-3">
                            <label for="teacher_club_id" class="form-label">Teacher</label>
                            <select class="form-select @error('teacher_club_id') is-invalid @enderror" id="teacher_club_id" name="teacher_club_id" required>
                                <option value="">Choose a teacher...</option>
                            </select>
                            @error('teacher_club_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('school_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Transfer Leadership</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const transferModal = document.getElementById('transferLeadershipModal');
            const teacherSelect = document.getElementById('teacher_club_id');
            const schoolIdInput = document.getElementById('modal_school_id');
            const schoolNameSpan = document.getElementById('modal_school_name');

            const membersBySchool = JSON.parse(transferModal?.dataset.membersBySchool ?? '[]');

            if (transferModal) {
                transferModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const schoolId = button.getAttribute('data-school-id');
                    const schoolName = button.getAttribute('data-school-name');

                    schoolIdInput.value = schoolId;
                    schoolNameSpan.textContent = schoolName;

                    teacherSelect.innerHTML = '<option value="">Choose a teacher...</option>';

                    const schoolData = membersBySchool.find(function(s) {
                        return s.school_id == schoolId;
                    });

                    if (schoolData && schoolData.members) {
                        schoolData.members.forEach(function(member) {
                            const option = document.createElement('option');
                            option.value = member.id;
                            option.textContent = member.name + ' (' + member.email + ')';
                            teacherSelect.appendChild(option);
                        });
                    }
                });
            }
        });
    </script>
    @endpush
</x-admin-layout>
