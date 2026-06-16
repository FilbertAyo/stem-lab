<x-app-layout>
    <div class="col-lg-9">

        <div class="dashboard-content-header">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <a href="{{ route('client.clubs.index') }}" class="text-muted small text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i>Back to clubs
                    </a>
                    <h2 class="title mb-1 mt-1">{{ $club->name }}</h2>
                    @if($school)
                        <p class="text-muted mb-0 small">{{ $school->name }}</p>
                    @endif
                </div>
                @if($teacherClub->is_leader)
                    <span class="badge bg-primary">Club Leader</span>
                @endif
            </div>
        </div>

        <section class="row">
            <div class="col-12">

                @include('elements.client-validation')

                <div class="card border-0 shadow-none mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            @if($club->image_path)
                                <div class="col-md-4">
                                    <img src="{{ asset('storage/' . $club->image_path) }}"
                                         alt="{{ $club->name }}"
                                         class="w-100 rounded"
                                         style="max-height: 180px; object-fit: cover;"
                                         loading="lazy">
                                </div>
                            @endif
                            <div class="{{ $club->image_path ? 'col-md-8' : 'col-12' }}">
                                @if($club->description)
                                    <p class="text-muted mb-3">{{ $club->description }}</p>
                                @endif
                                @if($school)
                                    <dl class="row mb-0 small">
                                        <dt class="col-sm-3 text-muted">School</dt>
                                        <dd class="col-sm-9 mb-2">{{ $school->name }}</dd>
                                        @if($school->district)
                                            <dt class="col-sm-3 text-muted">District</dt>
                                            <dd class="col-sm-9 mb-2">{{ $school->district }}</dd>
                                        @endif
                                        @if($school->address)
                                            <dt class="col-sm-3 text-muted">Address</dt>
                                            <dd class="col-sm-9 mb-2">{{ $school->address }}</dd>
                                        @endif
                                    </dl>
                                @endif
                                @if($teacherClub->club_code)
                                    <div class="mt-2">
                                        <span class="text-muted small">Club code:</span>
                                        <code>{{ $teacherClub->club_code }}</code>
                                        <p class="text-muted small mb-0 mt-1">Share this code with students when they register on the platform.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <ul class="nav nav-tabs mb-4" id="membersTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a href="{{ route('client.clubs.show', $teacherClub) }}" class="nav-link {{ request('tab') !== 'teachers' ? 'active' : '' }}">
                            Students
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="{{ route('client.clubs.show', ['teacherClub' => $teacherClub, 'tab' => 'teachers']) }}" class="nav-link {{ request('tab') === 'teachers' ? 'active' : '' }}">
                            Teachers
                        </a>
                    </li>
                </ul>

                @if(request('tab') !== 'teachers')
                    <div class="card border-0 shadow-none">
                        <div class="card-header bg-light-soft">
                            <div>
                                <h5 class="mb-0">Registered Students</h5>
                            </div>
                            <div class="mt-3 gap-2 d-flex flex-wrap align-items-center" role="group">
                                <button type="button" class="badge bg-success text-white border-0" data-bs-toggle="modal" data-bs-target="#bulkUploadModal">
                                    <i class="bi bi-upload me-1"></i>Bulk Upload
                                </button>
                                <button type="button" class="badge bg-primary text-white border-0" data-bs-toggle="modal" data-bs-target="#registerStudentModal">
                                    <i class="bi bi-plus-circle me-1"></i>Register Student
                                </button>
                                @if($students->isNotEmpty())
                                    <button type="submit"
                                            form="bulkDeleteStudentsForm"
                                            class="badge bg-danger text-white border-0 d-none"
                                            id="deleteSelectedStudentsBtn">
                                        <i class="bi bi-trash me-1"></i>Delete selected
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            @if($students->isEmpty())
                                <div class="text-center py-4">
                                    <i class="bi bi-inbox display-6 text-muted mb-3"></i>
                                    <p class="text-muted mb-2">No students have been registered yet.</p>
                                    <p class="text-muted mb-3">Click "Register Student" above to add your first student.</p>
                                </div>
                            @else
                                <form method="POST" action="{{ route('client.students.bulk-destroy') }}" id="bulkDeleteStudentsForm" class="mb-0">
                                    @csrf
                                    <input type="hidden" name="teacher_club_id" value="{{ $teacherClub->id }}">
                                    <input type="hidden" name="redirect_to" value="{{ request()->getRequestUri() }}">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover table-light-soft align-middle">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th class="text-center bg-light" style="width: 2.75rem;">
                                                        <input type="checkbox" class="form-check-input" id="selectAllStudents" title="Select all" aria-label="Select all students">
                                                    </th>
                                                    <th>#</th>
                                                    <th>Admission No.</th>
                                                    <th>Student</th>
                                                    <th>Age</th>
                                                    <th>Gender</th>
                                                    <th>Class</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($students as $student)
                                                    <tr>
                                                        <td class="text-center">
                                                            <input type="checkbox"
                                                                   class="form-check-input student-row-checkbox"
                                                                   name="student_ids[]"
                                                                   value="{{ $student->id }}"
                                                                   aria-label="Select {{ $student->name }}">
                                                        </td>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td><code>{{ $student->admission_number }}</code></td>
                                                        <td class="fw-semibold">{{ $student->name }}</td>
                                                        <td>{{ $student->age }}</td>
                                                        <td>{{ ucfirst($student->gender) }}</td>
                                                        <td>{{ $student->schoolClass->name ?? '—' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="card border-0 shadow-none">
                        <div class="card-header">
                            <h5 class="mb-0">Teachers at this school club</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                @if($isLeader && $transferCandidates->isNotEmpty())
                                    <div class="mb-3">
                                        <button type="button"
                                                class="badge bg-primary text-white border-0"
                                                data-bs-toggle="modal"
                                                data-bs-target="#transferLeaderModal">
                                            <i class="bi bi-arrow-left-right me-1"></i>Transfer leadership
                                        </button>
                                    </div>
                                @endif
                                @if($approvedTeachers->isEmpty())
                                    <p class="text-muted">No approved teachers yet.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover table-light-soft align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Role</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($approvedTeachers as $membership)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td class="fw-semibold">
                                                            {{ $membership->user->name }}
                                                            @if($membership->user_id === auth()->id())
                                                                <span class="text-muted small fw-normal">(you)</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $membership->user->email }}</td>
                                                        <td>
                                                            @if($membership->is_leader)
                                                                <span class="badge bg-primary">Club Leader</span>
                                                            @else
                                                                <span class="badge bg-secondary-subtle text-secondary-emphasis border">Member</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            @if($pendingRequests->isNotEmpty())
                                <hr>
                                <h6 class="fw-semibold mb-3">
                                    <i class="bi bi-clock-history text-warning me-2"></i>
                                    Pending Join Requests ({{ $pendingRequests->count() }})
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Request Date</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pendingRequests as $request)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td class="fw-semibold">{{ $request->user->name }}</td>
                                                    <td>{{ $request->user->email }}</td>
                                                    <td>{{ $request->created_at->format('M d, Y') }}</td>
                                                    <td class="text-end">
                                                        <form method="POST" action="{{ route('client.clubs.approve-request', $request) }}" class="d-inline me-2">
                                                            @csrf
                                                            <button type="submit" class="badge bg-success text-white border-0" onclick="return confirm('Approve this request?')">
                                                                <i class="bi bi-check-circle me-1"></i>Approve
                                                            </button>
                                                        </form>
                                                        <form method="POST" action="{{ route('client.clubs.reject-request', $request) }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="badge bg-danger text-white border-0" onclick="return confirm('Reject this request?')">
                                                                <i class="bi bi-x-circle me-1"></i>Reject
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>

    @if(request('tab') !== 'teachers')
        <div class="modal fade" id="registerStudentModal" tabindex="-1" aria-labelledby="registerStudentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg mt-5">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="registerStudentModalLabel">
                            <i class="bi bi-person-plus me-2"></i>Register New Student
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('client.students.store') }}" id="registerStudentForm">
                        @csrf
                        <input type="hidden" name="teacher_club_id" value="{{ $teacherClub->id }}">
                        <input type="hidden" name="redirect_to" value="{{ request()->getRequestUri() }}">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="name" class="form-label">Student Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control border p-2 @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control border p-2 @error('date_of_birth') is-invalid @enderror" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" max="{{ now()->toDateString() }}" required>
                                    @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                    <select class="form-select border p-2 @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                                        <option value="">Select gender...</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                    @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="school_class_id" class="form-label">Class <span class="text-danger">*</span></label>
                                <select class="form-select border p-2 @error('school_class_id') is-invalid @enderror" id="school_class_id" name="school_class_id" required>
                                    <option value="">Select class...</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ old('school_class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                    @endforeach
                                </select>
                                @error('school_class_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="badge bg-primary text-white border-0">Register Student</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="bulkUploadModal" tabindex="-1" aria-labelledby="bulkUploadModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg mt-5">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bulkUploadModalLabel">
                            <i class="bi bi-upload me-2"></i>Bulk Upload Students
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('client.students.bulk-store') }}" enctype="multipart/form-data" id="bulkUploadForm">
                        @csrf
                        <input type="hidden" name="teacher_club_id" value="{{ $teacherClub->id }}">
                        <input type="hidden" name="redirect_to" value="{{ request()->getRequestUri() }}">
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                CSV columns: <code>name,date_of_birth,gender,class_name</code>
                            </div>
                            <div class="mb-3">
                                <button type="button" class="badge bg-dark text-white border-0" id="downloadStudentTemplateBtn">
                                    <i class="bi bi-download me-1"></i>Download CSV Template
                                </button>
                            </div>
                            <div class="mb-3">
                                <label for="csv_file" class="form-label">CSV File <span class="text-danger">*</span></label>
                                <input type="file" class="form-control border @error('csv_file') is-invalid @enderror" id="csv_file" name="csv_file" accept=".csv,.txt" required>
                                @error('csv_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="badge bg-primary text-white border-0">
                                <i class="bi bi-upload me-1"></i>Upload CSV
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if($isLeader && $transferCandidates->isNotEmpty())
        <div class="modal fade" id="transferLeaderModal" tabindex="-1" aria-labelledby="transferLeaderModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="transferLeaderModalLabel">
                            <i class="bi bi-arrow-left-right me-2"></i>Transfer leadership
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('client.clubs.transfer-leadership') }}">
                        @csrf
                        <input type="hidden" name="redirect_teacher_club_id" value="{{ $teacherClub->id }}">
                        <div class="modal-body">
                            <p class="text-muted small mb-3">
                                Choose the approved teacher who will become club leader for <strong>{{ $club->name }}</strong>@if($school) ({{ $school->name }})@endif.
                            </p>
                            <label for="new_leader_teacher_club_id" class="form-label">New leader <span class="text-danger">*</span></label>
                            <select class="form-select border" id="new_leader_teacher_club_id" name="new_leader_teacher_club_id" required>
                                <option value="" selected disabled>Select a teacher…</option>
                                @foreach($transferCandidates as $candidate)
                                    <option value="{{ $candidate->id }}">{{ $candidate->user->name }} — {{ $candidate->user->email }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="badge bg-primary text-white border-0">Confirm transfer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <input type="hidden" id="has_register_errors" value="{{ ($errors->has('name') || $errors->has('date_of_birth') || $errors->has('gender') || $errors->has('school_class_id') || ($errors->has('teacher_club_id') && !$errors->has('csv_file'))) ? '1' : '0' }}">
    <input type="hidden" id="has_bulk_errors" value="{{ ($errors->has('csv_file') || ($errors->has('teacher_club_id') && !$errors->has('name') && !$errors->has('date_of_birth'))) ? '1' : '0' }}">

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const templateButton = document.getElementById('downloadStudentTemplateBtn');
            const hasRegisterErrors = document.getElementById('has_register_errors')?.value === '1';
            const hasBulkErrors = document.getElementById('has_bulk_errors')?.value === '1';

            if (templateButton) {
                templateButton.addEventListener('click', function () {
                    const csvContent = [
                        'name,date_of_birth,gender,class_name',
                        'Jane Doe,14-07-2011,female,Form 1',
                        'John Smith,03-10-2010,male,Form 2'
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
                const el = document.getElementById('registerStudentModal');
                if (el) new bootstrap.Modal(el).show();
            }
            if (hasBulkErrors) {
                const el = document.getElementById('bulkUploadModal');
                if (el) new bootstrap.Modal(el).show();
            }

            const bulkDeleteForm = document.getElementById('bulkDeleteStudentsForm');
            const selectAllStudents = document.getElementById('selectAllStudents');
            const deleteSelectedBtn = document.getElementById('deleteSelectedStudentsBtn');

            function getStudentRowCheckboxes() {
                return bulkDeleteForm ? bulkDeleteForm.querySelectorAll('.student-row-checkbox') : [];
            }

            function syncSelectAllAndDeleteButton() {
                const boxes = getStudentRowCheckboxes();
                if (!deleteSelectedBtn) return;
                let checked = 0;
                boxes.forEach(cb => { if (cb.checked) checked++; });
                deleteSelectedBtn.classList.toggle('d-none', checked === 0);
                if (selectAllStudents) {
                    selectAllStudents.checked = boxes.length > 0 && checked === boxes.length;
                    selectAllStudents.indeterminate = checked > 0 && checked < boxes.length;
                }
            }

            if (bulkDeleteForm && deleteSelectedBtn) {
                if (selectAllStudents) {
                    selectAllStudents.addEventListener('change', function () {
                        getStudentRowCheckboxes().forEach(cb => { cb.checked = selectAllStudents.checked; });
                        syncSelectAllAndDeleteButton();
                    });
                }
                bulkDeleteForm.addEventListener('change', function (e) {
                    if (e.target?.classList?.contains('student-row-checkbox')) syncSelectAllAndDeleteButton();
                });
                bulkDeleteForm.addEventListener('submit', function (e) {
                    const selected = bulkDeleteForm.querySelectorAll('.student-row-checkbox:checked');
                    if (selected.length === 0) { e.preventDefault(); return; }
                    if (!window.confirm('Delete ' + selected.length + ' student(s)? This cannot be undone.')) e.preventDefault();
                });
                syncSelectAllAndDeleteButton();
            }
        });
    </script>
</x-app-layout>
