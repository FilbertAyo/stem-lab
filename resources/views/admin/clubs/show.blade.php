<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">{{ $club->name }}</h5>
                    <p class="text-muted mb-0">Club Details & Management</p>
                </div>
                <a href="{{ route('admin.clubs.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>
                    Back 
                </a>
            </div>

    

            <!-- Club Information Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Club Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>Name:</strong> {{ $club->name }}</p>
                            <p><strong>Status:</strong>
                                @if($club->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-8">
                            <p><strong>Description:</strong></p>
                            <p>{{ $club->description ?? 'No description provided.' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <ul class="nav nav-pills mb-3" id="club-tabs" role="tablist" aria-label="Club management sections">
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $activeTab === 'leaders' ? 'active' : '' }}"
                       id="pills-leaders-tab"
                       data-bs-toggle="pill"
                       href="#pills-leaders"
                       role="tab"
                       aria-controls="pills-leaders"
                       aria-selected="{{ $activeTab === 'leaders' ? 'true' : 'false' }}">
                        School Leaders
                        <span class="badge bg-light text-dark ms-1">{{ $leaders->total() }}</span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $activeTab === 'members' ? 'active' : '' }}"
                       id="pills-members-tab"
                       data-bs-toggle="pill"
                       href="#pills-members"
                       role="tab"
                       aria-controls="pills-members"
                       aria-selected="{{ $activeTab === 'members' ? 'true' : 'false' }}">
                        Approved Members
                        <span class="badge bg-light text-dark ms-1">{{ $approvedMembers->total() }}</span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $activeTab === 'requests' ? 'active' : '' }}"
                       id="pills-requests-tab"
                       data-bs-toggle="pill"
                       href="#pills-requests"
                       role="tab"
                       aria-controls="pills-requests"
                       aria-selected="{{ $activeTab === 'requests' ? 'true' : 'false' }}">
                        Pending Requests
                        <span class="badge bg-light text-dark ms-1">{{ $pendingRequests->total() }}</span>
                    </a>
                </li>
            </ul>

            <div class="tab-content" id="club-tabsContent">
                <div class="tab-pane fade {{ $activeTab === 'leaders' ? 'show active' : '' }}" id="pills-leaders" role="tabpanel" aria-labelledby="pills-leaders-tab">
                    <div class="card mb-4">
                        <div class="card-body p-0">
                            <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 p-3 border-bottom bg-light">
                                <form method="GET" action="{{ route('admin.clubs.show', $club) }}" class="row g-2 align-items-end flex-grow-1">
                                    <input type="hidden" name="tab" value="leaders">
                                    <div class="col-md-6 col-lg-5">
                                        <label class="form-label small mb-1" for="leaders_search">Search leaders</label>
                                        <input
                                            type="search"
                                            id="leaders_search"
                                            name="leaders_search"
                                            class="form-control form-control-sm"
                                            value="{{ $leadersSearch }}"
                                            placeholder="Name, email, school, or club code..."
                                        >
                                    </div>
                                    <div class="col-auto">
                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-search"></i> Search
                                        </button>
                                        @if($leadersSearch !== '')
                                            <a href="{{ route('admin.clubs.show', ['club' => $club, 'tab' => 'leaders']) }}" class="btn btn-sm btn-outline-secondary">
                                                Clear
                                            </a>
                                        @endif
                                    </div>
                                </form>
                                <a href="{{ route('admin.clubs.leaders.export', array_filter(['club' => $club, 'leaders_search' => $leadersSearch ?: null])) }}" class="btn btn-sm btn-danger">
                                    <i class="ti ti-download me-1"></i>
                                    Download PDF
                                </a>
                            </div>

                            @if($leaders->isEmpty())
                                <div class="text-center text-muted py-5">
                                    <i class="ti ti-users-off fs-1 d-block mb-2"></i>
                                    {{ $leadersSearch !== '' ? 'No leaders match your search.' : 'No leaders assigned yet.' }}
                                </div>
                            @else
                                <div class="table-responsive p-3">
                                    <table class="table table-sm table-bordered table-hover mb-0 align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 60px;">#</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>School</th>
                                                <th>Club code</th>
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
                                                    <td>@if($leader->club_code)<code>{{ $leader->club_code }}</code>@else<span class="text-muted">—</span>@endif</td>
                                                    <td class="text-end">
                                                        <x-admin.table-actions :actions="[
                                                            [
                                                                'type' => 'button',
                                                                'label' => 'Transfer',
                                                                'icon' => 'ti ti-arrow-right',
                                                                'attributes' => [
                                                                    'data-bs-toggle' => 'modal',
                                                                    'data-bs-target' => '#transferLeadershipModal',
                                                                    'data-school-id' => $leader->school_id,
                                                                    'data-school-name' => $leader->school->name ?? 'N/A',
                                                                ],
                                                            ],
                                                        ]" />
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
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade {{ $activeTab === 'members' ? 'show active' : '' }}" id="pills-members" role="tabpanel" aria-labelledby="pills-members-tab">
                    <div class="card mb-4">
                        <div class="card-body p-0">
                            <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 p-3 border-bottom bg-light">
                                <form method="GET" action="{{ route('admin.clubs.show', $club) }}" class="row g-2 align-items-end flex-grow-1">
                                    <input type="hidden" name="tab" value="members">
                                    <div class="col-md-6 col-lg-5">
                                        <label class="form-label small mb-1" for="members_search">Search members</label>
                                        <input
                                            type="search"
                                            id="members_search"
                                            name="members_search"
                                            class="form-control form-control-sm"
                                            value="{{ $membersSearch }}"
                                            placeholder="Name, email, school, or club code..."
                                        >
                                    </div>
                                    <div class="col-auto">
                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-search"></i> Search
                                        </button>
                                        @if($membersSearch !== '')
                                            <a href="{{ route('admin.clubs.show', ['club' => $club, 'tab' => 'members']) }}" class="btn btn-sm btn-outline-secondary">
                                                Clear
                                            </a>
                                        @endif
                                    </div>
                                </form>
                                <a href="{{ route('admin.clubs.members.export', array_filter(['club' => $club, 'members_search' => $membersSearch ?: null])) }}" class="btn btn-sm btn-danger">
                                    <i class="ti ti-download me-1"></i>
                                    Download PDF
                                </a>
                            </div>

                            @if($approvedMembers->isEmpty())
                                <div class="text-center text-muted py-5">
                                    <i class="ti ti-users-off fs-1 d-block mb-2"></i>
                                    {{ $membersSearch !== '' ? 'No members match your search.' : 'No approved members yet.' }}
                                </div>
                            @else
                                <div class="table-responsive p-3">
                                    <table class="table table-sm table-bordered table-hover mb-0 align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 60px;">#</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>School</th>
                                                <th>Club code</th>
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
                                                    <td>@if($member->club_code)<code>{{ $member->club_code }}</code>@else<span class="text-muted">—</span>@endif</td>
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
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade {{ $activeTab === 'requests' ? 'show active' : '' }}" id="pills-requests" role="tabpanel" aria-labelledby="pills-requests-tab">
                    <div class="card mb-4">
                        <div class="card-body p-0">
                            <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 p-3 border-bottom bg-light">
                                <form method="GET" action="{{ route('admin.clubs.show', $club) }}" class="row g-2 align-items-end flex-grow-1">
                                    <input type="hidden" name="tab" value="requests">
                                    <div class="col-md-6 col-lg-5">
                                        <label class="form-label small mb-1" for="requests_search">Search requests</label>
                                        <input
                                            type="search"
                                            id="requests_search"
                                            name="requests_search"
                                            class="form-control form-control-sm"
                                            value="{{ $requestsSearch }}"
                                            placeholder="Name, email, school, or club code..."
                                        >
                                    </div>
                                    <div class="col-auto">
                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-search"></i> Search
                                        </button>
                                        @if($requestsSearch !== '')
                                            <a href="{{ route('admin.clubs.show', ['club' => $club, 'tab' => 'requests']) }}" class="btn btn-sm btn-outline-secondary">
                                                Clear
                                            </a>
                                        @endif
                                    </div>
                                </form>
                                <a href="{{ route('admin.clubs.requests.export', array_filter(['club' => $club, 'requests_search' => $requestsSearch ?: null])) }}" class="btn btn-sm btn-danger">
                                    <i class="ti ti-download me-1"></i>
                                    Download PDF
                                </a>
                            </div>

                            @if($pendingRequests->isEmpty())
                                <div class="text-center text-muted py-5">
                                    <i class="ti ti-clock fs-1 d-block mb-2"></i>
                                    {{ $requestsSearch !== '' ? 'No requests match your search.' : 'No pending requests.' }}
                                </div>
                            @else
                                <div class="table-responsive p-3">
                                    <table class="table table-sm table-bordered table-hover mb-0 align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 60px;">#</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>School</th>
                                                <th>Club code</th>
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
                                                    <td>@if($request->club_code)<code>{{ $request->club_code }}</code>@else<span class="text-muted">—</span>@endif</td>
                                                    <td>{{ $request->created_at->format('M d, Y') }}</td>
                                                    <td class="text-end">
                                                        <x-admin.table-actions :actions="[
                                                            [
                                                                'type' => 'form',
                                                                'label' => 'Approve',
                                                                'icon' => 'ti ti-check',
                                                                'url' => route('admin.clubs.approve-request', $request),
                                                                'confirm' => 'Approve this request?',
                                                            ],
                                                            [
                                                                'type' => 'form',
                                                                'label' => 'Reject',
                                                                'icon' => 'ti ti-x',
                                                                'url' => route('admin.clubs.reject-request', $request),
                                                                'confirm' => 'Reject this request?',
                                                                'danger' => true,
                                                            ],
                                                        ]" />
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
                        </div>
                    </div>
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
