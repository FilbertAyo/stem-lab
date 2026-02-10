<x-app-layout>
    <div class="col-lg-9">

        <div class="dashboard-content-header">
            <h2 class="title mb-1">Members</h2>
        </div>

        <section class="row">
            <div class="col-12">
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <pre class="mb-0" style="white-space: pre-wrap;">{{ session('error') }}</pre>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($teacherClubs->isEmpty())
                    <div class="card border-0 shadow-none">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-people display-4 text-muted mb-3"></i>
                            <h5 class="fw-semibold mb-2">No clubs joined yet</h5>
                            <p class="text-muted mb-3">Join a club to start managing members.</p>
                            <a href="{{ route('client.clubs.index') }}" class="btn btn-primary text-white btn-sm">
                                <i class="bi bi-school me-1"></i>Browse Clubs
                            </a>
                        </div>
                    </div>
                @else
                    <!-- Tabs Navigation -->
                    <ul class="nav nav-tabs mb-4" id="membersTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a href="{{ route('client.students.index') }}" class="nav-link {{ request('tab') !== 'teachers' ? 'active' : '' }}">
                                Students
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a href="{{ route('client.students.index', ['tab' => 'teachers']) }}" class="nav-link {{ request('tab') === 'teachers' ? 'active' : '' }}">
                              Teachers
                            </a>
                        </li>
                    </ul>

                    <!-- Tabs Content -->
                    <div class="tab-content" id="membersTabContent">
                        <!-- Students Tab -->
                        @if(request('tab') !== 'teachers')
                        <div class="tab-pane fade show active" id="students" role="tabpanel" aria-labelledby="students-tab">
                            <div class="card border-0 shadow-none">
                                <div class="card-header bg-light-soft">
                                    <div>
                                        <h5 class="mb-0">All Registered Students</h5>
                                        <small class="text-muted">Students across all your schools and clubs.</small>
                                    </div>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-success text-white" data-bs-toggle="modal" data-bs-target="#bulkUploadModal">
                                            <i class="bi bi-upload me-1"></i>Bulk Upload
                                        </button>
                                        <button type="button" class="btn btn-primary text-white" data-bs-toggle="modal" data-bs-target="#registerStudentModal">
                                            <i class="bi bi-plus-circle me-1"></i>Register Student
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    @if($students->isEmpty())
                                        <div class="text-center py-4">
                                            <i class="bi bi-inbox display-6 text-muted mb-3"></i>
                                            <p class="text-muted mb-2">No students have been registered yet.</p>
                                            <p class="text-muted mb-3">Click the "Register Student" button above to add your first student.</p>
                                        </div>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover table-light-soft align-middle">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Student</th>
                                                        <th>Age</th>
                                                        <th>Gender</th>
                                                        <th>Class</th>
                                                        <th>Club</th>
                                                        <th>School</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($students as $student)
                                                        <tr>
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td class="fw-semibold">{{ $student->name }}</td>
                                                            <td>{{ $student->age }}</td>
                                                            <td>{{ ucfirst($student->gender) }}</td>
                                                            <td>{{ $student->schoolClass->name ?? '—' }}</td>
                                                            <td>{{ $student->teacherClub->club->name ?? '—' }}</td>
                                                            <td>{{ $student->teacherClub->school->name ?? '—' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Teachers Tab -->
                        @if(request('tab') === 'teachers')
                        <div class="tab-pane fade show active" id="teachers" role="tabpanel" aria-labelledby="teachers-tab">
                            @if($clubsWithTeachers->isEmpty())
                                <div class="card border-0 shadow-none">
                                    <div class="card-body text-center py-5">
                                        <i class="bi bi-person-badge display-4 text-muted mb-3"></i>
                                        <h5 class="fw-semibold mb-2">No club memberships</h5>
                                        <p class="text-muted mb-3">Join a club to see teachers and members.</p>
                                    </div>
                                </div>
                            @else
                                @foreach($clubsWithTeachers as $clubData)
                                    <div class="card border-0 shadow-none mb-4">
                                        <div class="card-header">
                                            <h5 class="mb-0">
                                                <i class="bi bi-school me-2"></i>
                                                {{ $clubData['club']->name }}
                                            </h5>
                                            @if(isset($clubData['school']))
                                                <small class="text-muted">School: {{ $clubData['school']->name }}</small>
                                            @endif
                                        </div>
                                        <div class="card-body">
                                            <!-- Approved Teachers Section -->
                                            <div class="mb-4">
                                                <h6 class="fw-semibold mb-3">
                                                    <i class="bi bi-check-circle text-success me-2"></i>
                                                    Approved Teachers ({{ $clubData['approvedTeachers']->count() }})
                                                </h6>
                                                @if($clubData['approvedTeachers']->isEmpty())
                                                    <p class="text-muted">No approved teachers yet.</p>
                                                @else
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-hover mb-0">
                                                            <thead class="table-light-soft">
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Name</th>
                                                                    <th>Email</th>
                                                                    <th>School</th>
                                                                    <th>Role</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($clubData['approvedTeachers'] as $teacherClub)
                                                                    <tr>
                                                                        <td>{{ $loop->iteration }}</td>
                                                                        <td class="fw-semibold">
                                                                            {{ $teacherClub->user->name }}
                                                                          
                                                                        </td>
                                                                        <td>{{ $teacherClub->user->email }}</td>
                                                                        <td>{{ $teacherClub->school->name ?? 'N/A' }}</td>
                                                                        <td>
                                                                            @if($teacherClub->is_leader)
                                                                                <span class="badge bg-primary">Club Leader</span>
                                                                            @else
                                                                                <span class="badge bg-info">Member</span>
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Pending Requests Section -->
                                            @if($clubData['pendingRequests']->isNotEmpty())
                                                <hr>
                                                <div>
                                                    <h6 class="fw-semibold mb-3">
                                                        <i class="bi bi-clock-history text-warning me-2"></i>
                                                        Pending Join Requests ({{ $clubData['pendingRequests']->count() }})
                                                    </h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-hover mb-0">
                                                            <thead class="table-light-soft">
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Name</th>
                                                                    <th>Email</th>
                                                                    <th>School</th>
                                                                    <th>Request Date</th>
                                                                    <th class="text-end">Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($clubData['pendingRequests'] as $request)
                                                                    <tr>
                                                                        <td>{{ $loop->iteration }}</td>
                                                                        <td class="fw-semibold">{{ $request->user->name }}</td>
                                                                        <td>{{ $request->user->email }}</td>
                                                                        <td>{{ $request->school->name ?? 'N/A' }}</td>
                                                                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                                                                        <td class="text-end">
                                                                            <form method="POST" action="{{ route('client.clubs.approve-request', $request) }}" class="d-inline me-2">
                                                                                @csrf
                                                                                <button type="submit" class="btn btn-sm btn-success text-white" onclick="return confirm('Approve this request?')">
                                                                                    <i class="bi bi-check-circle me-1"></i>
                                                                                    Approve
                                                                                </button>
                                                                            </form>
                                                                            <form method="POST" action="{{ route('client.clubs.reject-request', $request) }}" class="d-inline">
                                                                                @csrf
                                                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Reject this request?')">
                                                                                    <i class="bi bi-x-circle me-1"></i>
                                                                                    Reject
                                                                                </button>
                                                                            </form>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        @endif
                    </div>
                @endif
            </div>
        </section>
    </div>

    <!-- Register Student Modal -->
    @if($teacherClubs->isNotEmpty())
        <div class="modal fade" id="registerStudentModal" tabindex="-1" aria-labelledby="registerStudentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="registerStudentModalLabel">
                            <i class="bi bi-person-plus me-2"></i>
                            Register New Student
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('client.students.store') }}" id="registerStudentForm">
                        @csrf
                        <div class="modal-body">
                            @if($teacherClubs->count() > 1)
                                <div class="mb-3">
                                    <label for="teacher_club_id" class="form-label">
                                        Select Club & School <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('teacher_club_id') is-invalid @enderror"
                                            id="teacher_club_id"
                                            name="teacher_club_id"
                                            required>
                                        <option value="">Choose a club and school...</option>
                                        @foreach($teacherClubs as $tc)
                                            <option value="{{ $tc->id }}" {{ old('teacher_club_id') == $tc->id ? 'selected' : '' }}>
                                                {{ $tc->club->name }} - {{ $tc->school->name ?? 'N/A' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('teacher_club_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Select the club and school where this student belongs.</small>
                                </div>
                            @else
                                <input type="hidden" name="teacher_club_id" value="{{ $teacherClubs->first()->id }}">
                            @endif

                            <div class="mb-3">
                                <label for="name" class="form-label">
                                    Student Name <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name') }}"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="age" class="form-label">
                                        Age <span class="text-danger">*</span>
                                    </label>
                                    <input type="number"
                                           class="form-control @error('age') is-invalid @enderror"
                                           id="age"
                                           name="age"
                                           value="{{ old('age') }}"
                                           min="1"
                                           max="150"
                                           required>
                                    @error('age')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="gender" class="form-label">
                                        Gender <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('gender') is-invalid @enderror"
                                            id="gender"
                                            name="gender"
                                            required>
                                        <option value="">Select gender...</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="school_class_id" class="form-label">
                                    Class <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('school_class_id') is-invalid @enderror"
                                        id="school_class_id"
                                        name="school_class_id"
                                        required>
                                    <option value="">Select class...</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ old('school_class_id') == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('school_class_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>
                                Register Student
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bulk Upload Modal -->
        <div class="modal fade" id="bulkUploadModal" tabindex="-1" aria-labelledby="bulkUploadModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bulkUploadModalLabel">
                            <i class="bi bi-upload me-2"></i>
                            Bulk Upload Students
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('client.students.bulk-store') }}" enctype="multipart/form-data" id="bulkUploadForm">
                        @csrf
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>CSV Format Required:</strong> Your CSV file must include columns: name, age, gender, class_name
                            </div>

                            @if($teacherClubs->count() > 1)
                                <div class="mb-3">
                                    <label for="bulk_teacher_club_id" class="form-label">
                                        Select Club & School <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('teacher_club_id') is-invalid @enderror"
                                            id="bulk_teacher_club_id"
                                            name="teacher_club_id"
                                            required>
                                        <option value="">Choose a club and school...</option>
                                        @foreach($teacherClubs as $tc)
                                            <option value="{{ $tc->id }}" {{ old('teacher_club_id') == $tc->id ? 'selected' : '' }}>
                                                {{ $tc->club->name }} - {{ $tc->school->name ?? 'N/A' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('teacher_club_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @else
                                <input type="hidden" name="teacher_club_id" value="{{ $teacherClubs->first()->id }}">
                            @endif

                            <div class="mb-3">
                                <label for="csv_file" class="form-label">
                                    CSV File <span class="text-danger">*</span>
                                </label>
                                <input type="file"
                                       class="form-control @error('csv_file') is-invalid @enderror"
                                       id="csv_file"
                                       name="csv_file"
                                       accept=".csv,.txt"
                                       required>
                                @error('csv_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Maximum file size: 5MB. Accepted formats: CSV, TXT</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-upload me-1"></i>
                                Upload CSV
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</x-app-layout>