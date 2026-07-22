<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">{{ $school->name }}</h5>
                    <p class="text-muted mb-0">School Details</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if($teachers->isEmpty() && $students->isEmpty())
                        <button
                            type="button"
                            class="btn btn-outline-danger btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteSchoolModal"
                        >
                            <i class="ti ti-trash"></i> Delete School
                        </button>
                    @endif
                    <a href="{{ route('admin.schools.index') }}" class="text-muted">
                        <i class="ti ti-arrow-left"></i> Back to Schools
                    </a>
                </div>
            </div>

            <!-- School Information Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="ti ti-building"></i> School Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="text-muted small">School Name</label>
                                <p class="mb-0 fw-semibold">{{ $school->name }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Email</label>
                                <p class="mb-0">{{ $school->email ?? '—' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Phone</label>
                                <p class="mb-0">{{ $school->phone ?? '—' }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="text-muted small">District</label>
                                <p class="mb-0">{{ $school->districtName() ?? '—' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Ward</label>
                                <p class="mb-0">{{ $school->ward ?? '—' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Region</label>
                                <p class="mb-0">{{ $school->regionModel?->name ?? '—' }}</p>
                            </div>
                           
                        </div>
                        <div class="col-4">
                        <div class="mb-3">
                                <label class="text-muted small">Status</label>
                                <p class="mb-0">
                                    <span class="badge {{ $school->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $school->is_active ? 'Active' : 'Disabled' }}
                                    </span>
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Coordinates</label>
                                @if($school->latitude !== null && $school->longitude !== null)
                                    <p class="mb-0">
                                        <a href="https://www.google.com/maps?q={{ $school->latitude }},{{ $school->longitude }}" target="_blank" rel="noopener noreferrer">
                                            {{ $school->latitude }}, {{ $school->longitude }}
                                            <i class="ti ti-external-link"></i>
                                        </a>
                                    </p>
                                @else
                                    <p class="mb-0">—</p>
                                @endif
                            </div>
                            <div class="mb-0">
                                <label class="text-muted small">Ownership</label>
                                @include('admin.partials.ownership-meta', ['record' => $school, 'compact' => true, 'class' => 'mt-1'])
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Teachers Card -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="ti ti-users"></i> Teachers</h6>
                            <div class="d-flex align-items-center gap-2">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#addTeacherModal"
                                    @disabled($clubs->isEmpty() || $unassignedTeachers->isEmpty())
                                >
                                    <i class="ti ti-plus me-1"></i> Add Teacher
                                </button>
                                <span class="badge bg-primary">{{ $teachers->count() }}</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @if($teachers->isEmpty())
                                <div class="text-center text-muted py-4">
                                    <i class="ti ti-users-off fs-1 d-block mb-2"></i>
                                    No teachers registered for this school yet.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered mb-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Phone & Email</th>
                                                <th>Clubs & Dedicated Day</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($teachers as $teacher)
                                                @php
                                                    $teacherClubs = $school->teacherClubs->where('user_id', $teacher->id);
                                                @endphp
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td class="fw-semibold">{{ $teacher->name }}</td>
                                                    <td>
                                                   {{ $teacher->phone ?? '—' }} <br>
                                                       <span class="text-muted small">{{ $teacher->email }}</span>
                                                   
                                                    </td>
                                                    <td>
                                                        @if($teacherClubs->isEmpty())
                                                            <span class="text-muted">—</span>
                                                        @else
                                                            <div class="d-flex flex-column gap-2">
                                                @foreach($teacherClubs as $tc)
                                                                    @php
                                                                        $sharedDay = $sharedClubDaysByClubId[$tc->club_id] ?? $tc->club_day;
                                                                    @endphp
                                                                    <div class="border rounded p-2">
                                                                        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                                                            <div>
                                                                                <span class="badge bg-info">{{ $tc->club->name ?? 'N/A' }}</span>
                                                                                @if($tc->is_leader)
                                                                                    <span class="badge bg-success">Leader</span>
                                                                                @endif
                                                                                @if($tc->club_code)
                                                                                    <small class="text-muted d-block mt-1">Code: <code>{{ $tc->club_code }}</code></small>
                                                                                @endif
                                                                                <small class="text-muted d-block">Shared club day for all teachers in this club</small>
                                                                            </div>
                                                                            <form
                                                                                action="{{ route('admin.schools.teacher-clubs.update-club-day', [$school, $tc]) }}"
                                                                                method="POST"
                                                                                class="d-flex align-items-center gap-2 js-club-day-form"
                                                                                data-initial-day="{{ $sharedDay ?? '' }}"
                                                                            >
                                                                                @csrf
                                                                                @method('PUT')
                                                                                <select
                                                                                    name="club_day"
                                                                                    class="form-select form-select-sm js-club-day-select"
                                                                                    style="min-width: 170px;"
                                                                                    aria-label="Dedicated day"
                                                                                >
                                                                                    <option value="">Select day</option>
                                                                                    @foreach(['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday'] as $dayValue => $dayLabel)
                                                                                        <option value="{{ $dayValue }}" @selected($sharedDay === $dayValue)>{{ $dayLabel }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                                <button
                                                                                    type="submit"
                                                                                    class="btn btn-sm btn-outline-primary px-2 js-club-day-save-btn"
                                                                                    @if($sharedDay) style="display: none;" @endif
                                                                                >
                                                                                    Save
                                                                                </button>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($teacherClubs->isEmpty())
                                                            <span class="text-muted">—</span>
                                                        @else
                                                            <div class="d-flex flex-column gap-2">
                                                                @foreach($teacherClubs as $tc)
                                                                    <form
                                                                        action="{{ route('admin.schools.remove-teacher-from-club', $tc) }}"
                                                                        method="POST"
                                                                        class="d-inline"
                                                                        onsubmit="return confirm('Are you sure you want to remove this teacher from this club? This action cannot be undone.')"
                                                                    >
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button
                                                                            type="submit"
                                                                            class="btn btn-sm btn-outline-danger p-1"
                                                                            title="Remove from {{ $tc->club->name ?? 'club' }}"
                                                                        >
                                                                            Remove 
                                                                        </button>
                                                                    </form>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Students Card -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="ti ti-school"></i> Students</h6>
                            <div class="d-flex align-items-center gap-2">
                                @if($students->isNotEmpty())
                                    <button
                                        type="submit"
                                        form="bulkDeleteStudentsForm"
                                        class="btn btn-sm btn-outline-danger d-none"
                                        id="deleteSelectedStudentsBtn"
                                    >
                                        <i class="ti ti-trash me-1"></i> Delete selected
                                    </button>
                                @endif
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#bulkUploadStudentsModal"
                                >
                                    <i class="ti ti-upload me-1"></i> Bulk Upload
                                </button>
                                <span class="badge bg-primary">{{ $students->count() }}</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @if($students->isEmpty())
                                <div class="text-center text-muted py-4">
                                    <i class="ti ti-user-off fs-1 d-block mb-2"></i>
                                    No students registered for this school yet.
                                </div>
                            @else
                                <form
                                    method="POST"
                                    action="{{ route('admin.schools.students.bulk-destroy', $school) }}"
                                    id="bulkDeleteStudentsForm"
                                    class="mb-0"
                                >
                                    @csrf
                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-hover table-bordered mb-0">
                                            <thead class="sticky-top bg-white">
                                                <tr>
                                                    <th class="text-center" style="width: 2.75rem;">
                                                        <input
                                                            type="checkbox"
                                                            class="form-check-input"
                                                            id="selectAllStudents"
                                                            title="Select all"
                                                            aria-label="Select all students"
                                                        >
                                                    </th>
                                                    <th>#</th>
                                                    <th>Admission No.</th>
                                                    <th>Name</th>
                                                    <th>Age</th>
                                                    <th>Gender</th>
                                                    <th>Class</th>
                                                    <th>Club</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($students as $student)
                                                    <tr>
                                                        <td class="text-center">
                                                            <input
                                                                type="checkbox"
                                                                class="form-check-input student-row-checkbox"
                                                                name="student_ids[]"
                                                                value="{{ $student->id }}"
                                                                aria-label="Select {{ $student->name }}"
                                                            >
                                                        </td>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td><code>{{ $student->admission_number }}</code></td>
                                                        <td class="fw-semibold">{{ $student->name }}</td>
                                                        <td>{{ $student->age ?? '—' }}</td>
                                                        <td>
                                                            @php
                                                                $gender = strtolower((string) $student->gender);
                                                                $genderBadgeClass = match ($gender) {
                                                                    'male' => 'bg-info',
                                                                    'female' => 'bg-danger',
                                                                    'other' => 'bg-secondary',
                                                                    default => 'bg-light text-dark border',
                                                                };
                                                            @endphp
                                                            @if($gender !== '')
                                                                <span class="badge {{ $genderBadgeClass }}">
                                                                    {{ ucfirst($gender) }}
                                                                </span>
                                                            @else
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $student->schoolClass->name ?? '—' }}</td>
                                                        <td>
                                                            <span class="badge bg-light text-dark">
                                                                {{ $student->teacherClub?->club?->name ?? '—' }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <button
                                                                type="submit"
                                                                form="delete-student-{{ $student->id }}"
                                                                class="btn btn-sm btn-outline-danger p-1"
                                                                title="Delete student"
                                                                onclick="return confirm('Are you sure you want to delete {{ $student->name }}? This action cannot be undone.')"
                                                            >
                                                                delete
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </form>
                                @foreach($students as $student)
                                    <form
                                        id="delete-student-{{ $student->id }}"
                                        action="{{ route('admin.schools.remove-student', [$school, $student]) }}"
                                        method="POST"
                                        class="d-none"
                                    >
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>

         
        </div>
    </div>

    @if($teachers->isEmpty() && $students->isEmpty())
        <div class="modal fade" id="deleteSchoolModal" tabindex="-1" aria-labelledby="deleteSchoolModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteSchoolModalLabel">Delete School</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('admin.schools.destroy', $school) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="modal-body">
                            <p class="mb-0">Are you sure you want to delete <strong>{{ $school->name }}</strong>? This action cannot be undone.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Delete School</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <div class="modal fade" id="addTeacherModal" tabindex="-1" aria-labelledby="addTeacherModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg mt-5">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTeacherModalLabel">
                        <i class="ti ti-user-plus me-2"></i>
                        Add Teacher to {{ $school->name }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form
                    method="POST"
                    action="{{ route('admin.schools.teachers.store', $school) }}"
                    id="addTeacherForm"
                >
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-1"></i>
                            Select a registered teacher who is not yet assigned to any school, then choose a club at {{ $school->name }}.
                        </div>

                        @if($clubs->isEmpty())
                            <div class="alert alert-warning mb-0">
                                No active clubs are available. Create a club before adding teachers.
                            </div>
                        @elseif($unassignedTeachers->isEmpty())
                            <div class="alert alert-warning mb-0">
                                No unassigned teachers are available. Teachers must register first and not belong to any school yet.
                            </div>
                        @else
                            <div class="mb-3">
                                <label for="teacher_user_id" class="form-label">
                                    Teacher <span class="text-danger">*</span>
                                </label>
                                <select
                                    class="form-select border @error('user_id') is-invalid @enderror"
                                    id="teacher_user_id"
                                    name="user_id"
                                    required
                                >
                                    <option value="">Select teacher...</option>
                                    @foreach($unassignedTeachers as $unassignedTeacher)
                                        <option value="{{ $unassignedTeacher->id }}" @selected(old('user_id') == $unassignedTeacher->id)>
                                            {{ $unassignedTeacher->name }} ({{ $unassignedTeacher->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="teacher_club_id" class="form-label">
                                            Club <span class="text-danger">*</span>
                                        </label>
                                        <select
                                            class="form-select border @error('club_id') is-invalid @enderror"
                                            id="teacher_club_id"
                                            name="club_id"
                                            required
                                        >
                                            <option value="">Select club...</option>
                                            @foreach($clubs as $club)
                                                <option value="{{ $club->id }}" @selected(old('club_id') == $club->id)>
                                                    {{ $club->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('club_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="teacher_club_day" class="form-label">Dedicated Club Day</label>
                                        <select
                                            class="form-select border @error('club_day') is-invalid @enderror"
                                            id="teacher_club_day"
                                            name="club_day"
                                        >
                                            <option value="">Select day (optional — shared by club)</option>
                                            @foreach(['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday'] as $dayValue => $dayLabel)
                                                <option value="{{ $dayValue }}" @selected(old('club_day') === $dayValue)>{{ $dayLabel }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">If this club already has a day, new teachers inherit it automatically.</small>
                                        @error('club_day')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" @disabled($clubs->isEmpty() || $unassignedTeachers->isEmpty())>
                            <i class="ti ti-user-plus me-1"></i> Add Teacher
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="bulkUploadStudentsModal" tabindex="-1" aria-labelledby="bulkUploadStudentsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg mt-5">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkUploadStudentsModalLabel">
                        <i class="ti ti-upload me-2"></i>
                        Bulk Upload Students
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form
                    method="POST"
                    action="{{ route('admin.schools.students.bulk-import', $school) }}"
                    enctype="multipart/form-data"
                    id="adminBulkUploadForm"
                >
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-1"></i>
                            CSV columns required:
                            <code>name,date_of_birth,gender,class_name</code>
                            (or <code>name,age,gender,class_name</code>).
                            Students are saved under the <strong>club leader</strong> and shared with every approved teacher in that club at this school.
                        </div>

                        <div class="mb-3">
                            <button type="button" class="btn btn-sm btn-dark" id="downloadAdminStudentTemplateBtn">
                                <i class="ti ti-download me-1"></i> Download CSV Template
                            </button>
                        </div>

                        <div class="mb-3">
                            <label for="bulk_upload_teacher_club_id" class="form-label">
                                Club (shared roster) <span class="text-danger">*</span>
                            </label>
                            <select
                                class="form-select border @error('teacher_club_id') is-invalid @enderror"
                                id="bulk_upload_teacher_club_id"
                                name="teacher_club_id"
                                required
                            >
                                <option value="">Select club...</option>
                                @foreach($bulkUploadTeacherClubs as $teacherClub)
                                    <option
                                        value="{{ $teacherClub->id }}"
                                        @selected(old('teacher_club_id', $bulkUploadTeacherClubs->count() === 1 ? $teacherClub->id : null) == $teacherClub->id)
                                    >
                                        {{ $teacherClub->club->name ?? 'Club' }}
                                        — Leader: {{ $teacherClub->user->name ?? 'Teacher' }}
                                        @if($teacherClub->club_code) ({{ $teacherClub->club_code }})@endif
                                    </option>
                                @endforeach
                            </select>
                            @error('teacher_club_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($bulkUploadTeacherClubs->isEmpty())
                                <small class="text-danger">No club leaders found for this school. Add or approve a teacher first.</small>
                            @else
                                <small class="text-muted">Uploads attach to the leader and appear for all co-teachers in the same club.</small>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="csv_file" class="form-label">
                                CSV File <span class="text-danger">*</span>
                            </label>
                            <input
                                type="file"
                                class="form-control border @error('csv_file') is-invalid @enderror"
                                id="csv_file"
                                name="csv_file"
                                accept=".csv,.txt"
                                required
                            >
                            @error('csv_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Maximum file size: 5MB. Accepted formats: CSV, TXT.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" @disabled($bulkUploadTeacherClubs->isEmpty())>
                            <i class="ti ti-upload me-1"></i> Upload CSV
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <input
        type="hidden"
        id="has_admin_bulk_upload_errors"
        value="{{ ($errors->has('teacher_club_id') || $errors->has('csv_file')) ? '1' : '0' }}"
    >
    <input
        type="hidden"
        id="has_add_teacher_errors"
        value="{{ ($errors->has('user_id') || $errors->has('club_id') || $errors->has('club_day')) ? '1' : '0' }}"
    >
</x-admin-layout>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.js-club-day-form').forEach(function (form) {
            var select = form.querySelector('.js-club-day-select');
            var saveButton = form.querySelector('.js-club-day-save-btn');

            if (!select || !saveButton) {
                return;
            }

            var initialDay = form.dataset.initialDay || '';

            select.addEventListener('change', function () {
                saveButton.style.display = select.value === initialDay ? 'none' : 'inline-block';
            });

            form.addEventListener('submit', function () {
                saveButton.style.display = 'none';
            });
        });

        var templateButton = document.getElementById('downloadAdminStudentTemplateBtn');
        if (templateButton) {
            templateButton.addEventListener('click', function () {
                var csvContent = [
                    'name,date_of_birth,gender,class_name',
                    'Jane Doe,14-07-2011,female,Form 1',
                    'John Smith,03-10-2010,male,Form 2'
                ].join('\n');

                var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                var url = URL.createObjectURL(blob);
                var link = document.createElement('a');
                link.href = url;
                link.download = 'students_bulk_upload_template.csv';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            });
        }

        var hasBulkErrors = document.getElementById('has_admin_bulk_upload_errors')?.value === '1';
        if (hasBulkErrors) {
            var bulkModalElement = document.getElementById('bulkUploadStudentsModal');
            if (bulkModalElement) {
                var bulkModal = new bootstrap.Modal(bulkModalElement);
                bulkModal.show();
            }
        }

        var hasAddTeacherErrors = document.getElementById('has_add_teacher_errors')?.value === '1';
        if (hasAddTeacherErrors) {
            var addTeacherModalElement = document.getElementById('addTeacherModal');
            if (addTeacherModalElement) {
                var addTeacherModal = new bootstrap.Modal(addTeacherModalElement);
                addTeacherModal.show();
            }
        }

        var bulkDeleteForm = document.getElementById('bulkDeleteStudentsForm');
        var selectAllStudents = document.getElementById('selectAllStudents');
        var deleteSelectedBtn = document.getElementById('deleteSelectedStudentsBtn');

        function getStudentRowCheckboxes() {
            return bulkDeleteForm ? bulkDeleteForm.querySelectorAll('.student-row-checkbox') : [];
        }

        function syncSelectAllAndDeleteButton() {
            var boxes = getStudentRowCheckboxes();
            if (!deleteSelectedBtn) {
                return;
            }

            var checked = 0;
            boxes.forEach(function (cb) {
                if (cb.checked) {
                    checked++;
                }
            });

            deleteSelectedBtn.classList.toggle('d-none', checked === 0);

            if (selectAllStudents) {
                selectAllStudents.checked = boxes.length > 0 && checked === boxes.length;
                selectAllStudents.indeterminate = checked > 0 && checked < boxes.length;
            }
        }

        if (bulkDeleteForm && deleteSelectedBtn) {
            if (selectAllStudents) {
                selectAllStudents.addEventListener('change', function () {
                    getStudentRowCheckboxes().forEach(function (cb) {
                        cb.checked = selectAllStudents.checked;
                    });
                    syncSelectAllAndDeleteButton();
                });
            }

            bulkDeleteForm.addEventListener('change', function (e) {
                if (e.target && e.target.classList && e.target.classList.contains('student-row-checkbox')) {
                    syncSelectAllAndDeleteButton();
                }
            });

            bulkDeleteForm.addEventListener('submit', function (e) {
                var selected = bulkDeleteForm.querySelectorAll('.student-row-checkbox:checked');
                if (selected.length === 0) {
                    e.preventDefault();
                    return;
                }

                if (!window.confirm('Delete ' + selected.length + ' student(s)? This cannot be undone.')) {
                    e.preventDefault();
                }
            });

            syncSelectAllAndDeleteButton();
        }
    });
</script>

