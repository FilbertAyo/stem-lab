<x-app-layout>
    <div class="col-lg-9">

        <div class="dashboard-content-header">
            <h2 class="title mb-1">Members</h2>
        </div>

        <section class="row">
            <div class="col-12">

           @include('elements.client-validation')

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
                                   </div>
                                    <div class="btn-group mt-3" role="group">
                                        <button type="button" class="btn btn-sm btn-success text-white" data-bs-toggle="modal" data-bs-target="#bulkUploadModal">
                                            <i class="bi bi-upload me-1"></i>Bulk Upload
                                        </button>
                                        <button type="button" class="btn btn-sm btn-primary text-white" data-bs-toggle="modal" data-bs-target="#registerStudentModal">
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
            <div class="modal-dialog modal-lg mt-5">
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
                                    <select class="form-select border @error('teacher_club_id') is-invalid @enderror"
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
                                       class="form-control border p-2 @error('name') is-invalid @enderror"
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
                                    <label for="date_of_birth" class="form-label">
                                        Date of Birth <span class="text-danger">*</span>
                                    </label>
                                    <input type="date"
                                           class="form-control border p-2 @error('date_of_birth') is-invalid @enderror"
                                           id="date_of_birth"
                                           name="date_of_birth"
                                           value="{{ old('date_of_birth') }}"
                                           max="{{ now()->toDateString() }}"
                                           required>
                                    @error('date_of_birth')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="gender" class="form-label">
                                        Gender <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select border p-2 @error('gender') is-invalid @enderror"
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
                                <select class="form-select border p-2 @error('school_class_id') is-invalid @enderror"
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
                        <button type="submit" class="btn btn-sm btn-primary text-white">
                                Register Student
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bulk Upload Modal -->
        <div class="modal fade" id="bulkUploadModal" tabindex="-1" aria-labelledby="bulkUploadModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg mt-5">
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
                                <strong>CSV Format Required:</strong> Use columns:
                                <code>name,date_of_birth,gender,class_name</code>
                                (date format: YYYY-MM-DD). Use valid classes like <code>Form 1</code>, <code>Form 2</code>, <code>Form 3</code>, <code>Form 4</code>.
                            </div>

                            <div class="mb-3">
                                <button type="button" class="btn btn-sm btn-download-template" id="downloadStudentTemplateBtn">
                                    <i class="bi bi-download me-1"></i>Download CSV Template
                                </button>
                            </div>

                            @if($teacherClubs->count() > 1)
                                <div class="mb-3">
                                    <label for="bulk_teacher_club_id" class="form-label">
                                        Select Club & School <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select border p-2 @error('teacher_club_id') is-invalid @enderror"
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
                                       class="form-control border @error('csv_file') is-invalid @enderror"
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
                            <button type="submit" class="btn btn-sm text-white">
                                <i class="bi bi-upload me-1"></i>
                                Upload CSV
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <input type="hidden" id="has_register_errors" value="{{ ($errors->has('name') || $errors->has('date_of_birth') || $errors->has('gender') || $errors->has('school_class_id') || ($errors->has('teacher_club_id') && !$errors->has('csv_file'))) ? '1' : '0' }}">
    <input type="hidden" id="has_bulk_errors" value="{{ ($errors->has('csv_file') || ($errors->has('teacher_club_id') && !$errors->has('name') && !$errors->has('date_of_birth'))) ? '1' : '0' }}">

    <style>
        .btn-download-template {
            color: #072649;
            border: 1px solid #072649;
            background-color: transparent;
        }

        .btn-download-template:hover,
        .btn-download-template:focus {
            color: #fff;
            background-color: #072649;
            border-color: #072649;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dobInput = document.getElementById('date_of_birth');
            const templateButton = document.getElementById('downloadStudentTemplateBtn');
            const hasRegisterErrors = document.getElementById('has_register_errors')?.value === '1';
            const hasBulkErrors = document.getElementById('has_bulk_errors')?.value === '1';

            if (templateButton) {
                templateButton.addEventListener('click', function () {
                    const csvContent = [
                        'name,date_of_birth,gender,class_name',
                        'Jane Doe,2011-07-14,female,Form 1',
                        'John Smith,2010-10-03,male,Form 2'
                    ].join('\n');

                    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = 'students_bulk_upload_template.csv';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);
                });
            }

            if (hasRegisterErrors) {
                const registerModalEl = document.getElementById('registerStudentModal');
                if (registerModalEl) {
                    const registerModal = new bootstrap.Modal(registerModalEl);
                    registerModal.show();
                }
            }

            if (hasBulkErrors) {
                const bulkModalEl = document.getElementById('bulkUploadModal');
                if (bulkModalEl) {
                    const bulkModal = new bootstrap.Modal(bulkModalEl);
                    bulkModal.show();
                }
            }
        });
    </script>
</x-app-layout>