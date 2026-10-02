<x-admin-layout>
    <style>
        .school-people-card > .card-header {
            padding-bottom: 1rem;
        }
        #school-tabs {
            border-bottom: 0;
        }
        #school-tabs .nav-item {
            margin-bottom: 0;
        }
        #school-tabs .nav-link {
            margin-bottom: 0;
            white-space: nowrap;
            border: 1px solid var(--bs-border-color);
            background: transparent;
            color: var(--bs-body-color);
        }
        #school-tabs .nav-link .badge {
            background-color: #f1f5f9;
            color: #334155;
        }
        #school-tabs .nav-link.active {
            border-color: transparent;
            color: #fff;
            background-color: var(--bs-primary);
        }
        #school-tabs .nav-link.active .badge {
            background-color: #fff;
            color: var(--bs-primary);
        }
    </style>
    @php
        $locationParts = array_filter([
            $school->districtName(),
            $school->ward,
            $school->regionModel?->name,
        ]);
        $needsReview = $school->admin_reviewed_at === null;
    @endphp
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="mb-1">
                        {{ $school->name }}
                        <span class="badge {{ $school->is_active ? 'bg-success' : 'bg-secondary' }} align-middle ms-1">
                            {{ $school->is_active ? 'Active' : 'Disabled' }}
                        </span>
                        @if($needsReview)
                            <span class="badge bg-warning text-dark align-middle">Needs review</span>
                        @endif
                    </h5>
                    <p class="text-muted mb-0">
                        {{ $locationParts !== [] ? implode(' · ', $locationParts) : 'Location not set' }}
                    </p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.schools.profile', $school) }}" class="btn btn-sm btn-primary">
                        <i class="ti ti-download"></i> Download profile
                    </a>
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
                    <a href="{{ route('admin.schools.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i> Back to Schools
                    </a>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="ti ti-building me-1"></i> School Information</h6>
                </div>
                <div class="card-body pb-2">
                    <div class="row g-3">
                        <div class="col-sm-6 col-lg-3">
                            <label class="text-muted small d-block">Email</label>
                            <p class="mb-0">{{ $school->email ?? '—' }}</p>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <label class="text-muted small d-block">Phone</label>
                            <p class="mb-0">{{ $school->phone ?? '—' }}</p>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <label class="text-muted small d-block">Coordinates</label>
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
                        <div class="col-sm-6 col-lg-3">
                            <label class="text-muted small d-block">Ownership</label>
                            @include('admin.partials.ownership-meta', ['record' => $school, 'compact' => true, 'class' => 'mt-1'])
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4 school-people-card">
                <div class="card-header">
                    <ul class="nav nav-pills flex-wrap gap-2 mb-0" id="school-tabs" role="tablist" aria-label="School people">
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link d-inline-flex align-items-center {{ $activeTab === 'students' ? 'active' : '' }}"
                                id="pills-students-tab"
                                data-bs-toggle="pill"
                                data-bs-target="#pills-students"
                                data-tab="students"
                                type="button"
                                role="tab"
                                aria-controls="pills-students"
                                aria-selected="{{ $activeTab === 'students' ? 'true' : 'false' }}"
                            >
                                <i class="ti ti-school me-1"></i>
                                Students
                                <span class="badge ms-2">{{ $students->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link d-inline-flex align-items-center {{ $activeTab === 'teachers' ? 'active' : '' }}"
                                id="pills-teachers-tab"
                                data-bs-toggle="pill"
                                data-bs-target="#pills-teachers"
                                data-tab="teachers"
                                type="button"
                                role="tab"
                                aria-controls="pills-teachers"
                                aria-selected="{{ $activeTab === 'teachers' ? 'true' : 'false' }}"
                            >
                                <i class="ti ti-users me-1"></i>
                                Teachers
                                <span class="badge ms-2">{{ $teachers->count() }}</span>
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content" id="school-tabsContent">
                        <div
                            class="tab-pane fade {{ $activeTab === 'students' ? 'show active' : '' }}"
                            id="pills-students"
                            role="tabpanel"
                            aria-labelledby="pills-students-tab"
                            tabindex="0"
                        >
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 border-bottom bg-light">
                                @if($students->isNotEmpty())
                                    <div class="flex-grow-1" style="max-width: 22rem;">
                                        <label class="visually-hidden" for="students_search">Search students</label>
                                        <input
                                            type="search"
                                            id="students_search"
                                            class="form-control form-control-sm js-table-search"
                                            data-table="#students-table"
                                            data-empty="#students-empty-filter"
                                            placeholder="Search name, admission no., class, or club..."
                                        >
                                    </div>
                                @else
                                    <p class="mb-0 text-muted small">Students registered at this school.</p>
                                @endif
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    @if($students->isNotEmpty())
                                        <span class="small text-muted d-none" id="students-selected-count"></span>
                                        <button
                                            type="submit"
                                            form="bulkDeleteStudentsForm"
                                            formaction="{{ route('admin.schools.students.credentials.bulk', $school) }}"
                                            formmethod="post"
                                            class="btn btn-sm btn-outline-primary d-none"
                                            id="createSelectedLoginsBtn"
                                        >
                                            Create logins
                                        </button>
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
                                        data-bs-target="#addStudentModal"
                                        @disabled($bulkUploadTeacherClubs->isEmpty())
                                    >
                                        <i class="ti ti-user-plus me-1"></i> Add Student
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#bulkUploadStudentsModal"
                                    >
                                        <i class="ti ti-upload me-1"></i> Bulk Upload
                                    </button>
                                </div>
                            </div>

                            @if($students->isEmpty())
                                <div class="text-center text-muted py-5">
                                    <i class="ti ti-user-off fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No students registered for this school yet.</p>
                                    <div class="d-flex flex-wrap justify-content-center gap-2">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#addStudentModal"
                                            @disabled($bulkUploadTeacherClubs->isEmpty())
                                        >
                                            Add a student
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#bulkUploadStudentsModal"
                                        >
                                            Bulk upload students
                                        </button>
                                    </div>
                                </div>
                            @else
                                <form
                                    method="POST"
                                    action="{{ route('admin.schools.students.bulk-destroy', $school) }}"
                                    id="bulkDeleteStudentsForm"
                                    class="mb-0"
                                >
                                    @csrf
                                    <div id="students-empty-filter" class="text-center text-muted py-4 d-none">
                                        No students match your search.
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered table-striped mb-0 align-middle" id="students-table">
                                            <thead class="table-light">
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
                                                    <th style="width: 3rem;">#</th>
                                                    <th>Admission No.</th>
                                                    <th>Name</th>
                                                    <th>Age</th>
                                                    <th>Gender</th>
                                                    <th>Class</th>
                                                    <th>Club</th>
                                                    <th>Login</th>
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
                                                            @if($student->isLinked())
                                                                <span class="badge bg-success">Ready</span>
                                                            @else
                                                                <span class="badge bg-warning text-dark">No login</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="d-flex flex-wrap gap-1">
                                                                @if(! $student->isLinked())
                                                                    <button
                                                                        type="submit"
                                                                        form="create-login-{{ $student->id }}"
                                                                        class="btn btn-sm btn-outline-primary"
                                                                    >
                                                                        Create login
                                                                    </button>
                                                                @elseif($student->user?->usesPinLogin())
                                                                    <button
                                                                        type="submit"
                                                                        form="reset-pin-{{ $student->id }}"
                                                                        class="btn btn-sm btn-outline-secondary"
                                                                        onclick="return confirm('This will replace the current PIN for {{ $student->name }}. Continue?')"
                                                                    >
                                                                        Reset PIN
                                                                    </button>
                                                                @endif
                                                                <button
                                                                    type="submit"
                                                                    form="delete-student-{{ $student->id }}"
                                                                    class="btn btn-sm btn-outline-danger"
                                                                    title="Delete student"
                                                                    onclick="return confirm('Are you sure you want to delete {{ $student->name }}? This action cannot be undone.')"
                                                                >
                                                                    Delete
                                                                </button>
                                                            </div>
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
                                    @if(! $student->isLinked())
                                        <form
                                            id="create-login-{{ $student->id }}"
                                            action="{{ route('admin.schools.students.credentials.store', [$school, $student]) }}"
                                            method="POST"
                                            class="d-none"
                                        >
                                            @csrf
                                        </form>
                                    @elseif($student->user?->usesPinLogin())
                                        <form
                                            id="reset-pin-{{ $student->id }}"
                                            action="{{ route('admin.schools.students.credentials.reset', [$school, $student]) }}"
                                            method="POST"
                                            class="d-none"
                                        >
                                            @csrf
                                        </form>
                                    @endif
                                @endforeach
                            @endif
                        </div>

                        <div
                            class="tab-pane fade {{ $activeTab === 'teachers' ? 'show active' : '' }}"
                            id="pills-teachers"
                            role="tabpanel"
                            aria-labelledby="pills-teachers-tab"
                            tabindex="0"
                        >
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 border-bottom bg-light">
                                @if($teachers->isNotEmpty())
                                    <div class="flex-grow-1" style="max-width: 22rem;">
                                        <label class="visually-hidden" for="teachers_search">Search teachers</label>
                                        <input
                                            type="search"
                                            id="teachers_search"
                                            class="form-control form-control-sm js-table-search"
                                            data-table="#teachers-table"
                                            data-empty="#teachers-empty-filter"
                                            placeholder="Search name, email, phone, or club..."
                                        >
                                    </div>
                                @else
                                    <p class="mb-0 text-muted small">Teachers assigned to clubs at this school.</p>
                                @endif
                                <button
                                    type="button"
                                    class="btn btn-sm btn-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#addTeacherModal"
                                    @disabled($clubs->isEmpty() || $unassignedTeachers->isEmpty())
                                >
                                    <i class="ti ti-plus me-1"></i> Add Teacher
                                </button>
                            </div>

                            @if($teachers->isEmpty())
                                <div class="text-center text-muted py-5">
                                    <i class="ti ti-users-off fs-1 d-block mb-2"></i>
                                    <p class="mb-2">No teachers registered for this school yet.</p>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#addTeacherModal"
                                        @disabled($clubs->isEmpty() || $unassignedTeachers->isEmpty())
                                    >
                                        Add the first teacher
                                    </button>
                                </div>
                            @else
                                <div id="teachers-empty-filter" class="text-center text-muted py-4 d-none">
                                    No teachers match your search.
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered table-striped mb-0 align-middle" id="teachers-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 3rem;">#</th>
                                                <th>Name</th>
                                                <th>Phone &amp; Email</th>
                                                <th>Clubs &amp; Dedicated Day</th>
                                                <th style="width: 8rem;">Actions</th>
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
                                                        {{ $teacher->phone ?? '—' }}<br>
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
                                                                            class="btn btn-sm btn-outline-danger"
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

    <div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg mt-5">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStudentModalLabel">
                        <i class="ti ti-user-plus me-2"></i>
                        Add Student to {{ $school->name }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form
                    method="POST"
                    action="{{ route('admin.schools.students.store', $school) }}"
                    id="addStudentForm"
                >
                    @csrf
                    <input type="hidden" name="_form" value="add_student">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-1"></i>
                            The student is saved under the <strong>club leader</strong> and shared with every approved teacher in that club at this school.
                        </div>

                        <div class="mb-3">
                            <label for="add_student_teacher_club_id" class="form-label">
                                Club (shared roster) <span class="text-danger">*</span>
                            </label>
                            <select
                                class="form-select border @error('teacher_club_id') is-invalid @enderror"
                                id="add_student_teacher_club_id"
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
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="add_student_name" class="form-label">
                                Student Name <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control border @error('name') is-invalid @enderror"
                                id="add_student_name"
                                name="name"
                                value="{{ old('name') }}"
                                required
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="add_student_date_of_birth" class="form-label">
                                        Date of Birth <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        class="form-control border @error('date_of_birth') is-invalid @enderror"
                                        id="add_student_date_of_birth"
                                        name="date_of_birth"
                                        value="{{ old('date_of_birth') }}"
                                        placeholder="DD-MM-YYYY"
                                        inputmode="numeric"
                                        required
                                    >
                                    <small class="text-muted">Use DD-MM-YYYY, e.g. 14-07-2011</small>
                                    @error('date_of_birth')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="add_student_gender" class="form-label">
                                        Gender <span class="text-danger">*</span>
                                    </label>
                                    <select
                                        class="form-select border @error('gender') is-invalid @enderror"
                                        id="add_student_gender"
                                        name="gender"
                                        required
                                    >
                                        <option value="">Select gender...</option>
                                        <option value="male" @selected(old('gender') === 'male')>Male</option>
                                        <option value="female" @selected(old('gender') === 'female')>Female</option>
                                        <option value="other" @selected(old('gender') === 'other')>Other</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label for="add_student_school_class_id" class="form-label">
                                Class <span class="text-danger">*</span>
                            </label>
                            <select
                                class="form-select border @error('school_class_id') is-invalid @enderror"
                                id="add_student_school_class_id"
                                name="school_class_id"
                                required
                            >
                                <option value="">Select class...</option>
                                @foreach($schoolClasses as $schoolClass)
                                    <option value="{{ $schoolClass->id }}" @selected(old('school_class_id') == $schoolClass->id)>
                                        {{ $schoolClass->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('school_class_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" @disabled($bulkUploadTeacherClubs->isEmpty())>
                            <i class="ti ti-user-plus me-1"></i> Add Student
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
        value="{{ (old('_form') !== 'add_student' && ($errors->has('teacher_club_id') || $errors->has('csv_file'))) ? '1' : '0' }}"
    >
    <input
        type="hidden"
        id="has_add_student_errors"
        value="{{ (old('_form') === 'add_student' || $errors->has('name') || $errors->has('date_of_birth') || $errors->has('gender') || $errors->has('school_class_id')) ? '1' : '0' }}"
    >
    <input
        type="hidden"
        id="has_add_teacher_errors"
        value="{{ ($errors->has('user_id') || $errors->has('club_id') || $errors->has('club_day')) ? '1' : '0' }}"
    >

    @include('partials.provisioned-credentials-modal')
</x-admin-layout>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('#school-tabs [data-bs-toggle="pill"]').forEach(function (tab) {
            tab.addEventListener('shown.bs.tab', function (event) {
                var tabName = event.target.getAttribute('data-tab');
                if (!tabName) {
                    return;
                }

                var url = new URL(window.location.href);
                url.searchParams.set('tab', tabName);
                history.replaceState({}, '', url);
            });
        });

        document.querySelectorAll('.js-table-search').forEach(function (input) {
            var table = document.querySelector(input.getAttribute('data-table'));
            var emptyState = document.querySelector(input.getAttribute('data-empty'));
            if (!table) {
                return;
            }

            input.addEventListener('input', function () {
                var query = input.value.toLowerCase().trim();
                var visible = 0;

                table.querySelectorAll('tbody tr').forEach(function (row) {
                    var matches = query === '' || row.textContent.toLowerCase().indexOf(query) !== -1;
                    row.classList.toggle('d-none', !matches);
                    if (matches) {
                        visible++;
                    }
                });

                table.closest('.table-responsive')?.classList.toggle('d-none', visible === 0 && query !== '');
                if (emptyState) {
                    emptyState.classList.toggle('d-none', visible > 0 || query === '');
                }
            });
        });

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

        var hasAddStudentErrors = document.getElementById('has_add_student_errors')?.value === '1';
        if (hasAddStudentErrors) {
            var addStudentModalElement = document.getElementById('addStudentModal');
            if (addStudentModalElement) {
                var addStudentModal = new bootstrap.Modal(addStudentModalElement);
                addStudentModal.show();
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
        var createSelectedBtn = document.getElementById('createSelectedLoginsBtn');
        var selectedCountLabel = document.getElementById('students-selected-count');

        function getStudentRowCheckboxes(visibleOnly) {
            if (!bulkDeleteForm) {
                return [];
            }

            var boxes = Array.prototype.slice.call(bulkDeleteForm.querySelectorAll('.student-row-checkbox'));
            if (!visibleOnly) {
                return boxes;
            }

            return boxes.filter(function (cb) {
                return !cb.closest('tr')?.classList.contains('d-none');
            });
        }

        function syncSelectAllAndDeleteButton() {
            var visibleBoxes = getStudentRowCheckboxes(true);
            var checked = 0;
            visibleBoxes.forEach(function (cb) {
                if (cb.checked) {
                    checked++;
                }
            });

            if (deleteSelectedBtn) {
                deleteSelectedBtn.classList.toggle('d-none', checked === 0);
            }
            if (createSelectedBtn) {
                createSelectedBtn.classList.toggle('d-none', checked === 0);
            }
            if (selectedCountLabel) {
                selectedCountLabel.textContent = checked > 0 ? checked + ' selected' : '';
                selectedCountLabel.classList.toggle('d-none', checked === 0);
            }

            if (selectAllStudents) {
                selectAllStudents.checked = visibleBoxes.length > 0 && checked === visibleBoxes.length;
                selectAllStudents.indeterminate = checked > 0 && checked < visibleBoxes.length;
            }
        }

        if (bulkDeleteForm && deleteSelectedBtn) {
            if (selectAllStudents) {
                selectAllStudents.addEventListener('change', function () {
                    getStudentRowCheckboxes(true).forEach(function (cb) {
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

            var studentsSearch = document.getElementById('students_search');
            if (studentsSearch) {
                studentsSearch.addEventListener('input', syncSelectAllAndDeleteButton);
            }

            bulkDeleteForm.addEventListener('submit', function (e) {
                var selected = bulkDeleteForm.querySelectorAll('.student-row-checkbox:checked');
                if (selected.length === 0) {
                    e.preventDefault();
                    return;
                }

                var submitter = e.submitter;
                var action = (submitter && submitter.getAttribute('formaction')) || bulkDeleteForm.getAttribute('action') || '';
                if (action.indexOf('credentials') !== -1) {
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

