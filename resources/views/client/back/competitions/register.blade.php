<x-app-layout>
    <div class="col-12">
        @php($girlsInPool = $students->where('gender', 'female')->count())
        @php($poolCount = $students->count())
        @php($poolRatio = $poolCount > 0 ? round(($girlsInPool / $poolCount) * 100, 2) : 0)
        @php($poolMin = max(1, (int) $competition->min_members))
        @php($poolMax = $competition->max_members !== null ? (int) $competition->max_members : null)
        @php($repCount = max(1, (int) ($competition->representative_count ?? 10)))
        @php($poolOk = $poolCount >= $poolMin && $poolRatio >= 70 && ($poolMax === null || $poolCount <= $poolMax))
        @php($registerReturnPath = route('client.competitions.register', $competition, absolute: false))

        
        <div class="dashboard-content-header mb-3">
            <h4 class="mb-1">Upload students</h4>
     
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('client.competitions.index') }}">Competitions</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('client.competitions.show', $competition) }}">{{ $competition->name }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Participants</li>
                </ol>
            </nav>
           
        </div> 

        <div class="row">
            <div class="col-lg-8">
                @include('elements.client-validation')

                @if($poolOk)
                    <div class="alert alert-success small mb-4">
                        <strong>Pool requirements met.</strong> Confirm school details below, then submit for admin approval.
                    </div>
                @else
                    <div class="alert alert-warning small mb-4">
                        <strong>In progress.</strong> Import students and adjust your list until the sidebar rules are satisfied (size, girls %, classes, ages). You can submit only after the pool qualifies.
                    </div>
                @endif

                <div class="card border shadow-none mb-4">
                    <div class="card-header bg-light-soft d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <h5 class="mb-0"> Students CSV import</h5>
                        <a href="{{ route('client.competitions.register.csv-template', $competition) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-download me-1"></i>Download template
                        </a>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            Required columns: <code>name</code>, <code>gender</code> (<code>male</code> / <code>female</code>), <code>class_name</code>, and either <code>date_of_birth</code> (<strong>DD-MM-YYYY</strong>).
                            @if(! empty($allowedClassIds))
                                Rows must use a class in this competition&rsquo;s allowed range.
                            @endif
                            @if($competition->min_age !== null || $competition->max_age !== null)
                                Ages must fall within {{ $competition->min_age ?? '—' }}–{{ $competition->max_age ?? '—' }}.
                            @endif
                        </p>
                        <form action="{{ route('client.competitions.register.bulk-students', $competition) }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
                            @csrf
                            <div class="col-md-8">
                                <label class="form-label small mb-1" for="csv_file">CSV file</label>
                                <input type="file" name="csv_file" id="csv_file" accept=".csv,.txt" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-sm btn-primary text-white">
                                    <i class="bi bi-upload me-1"></i>Import students
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card border shadow-none mb-4">
                    <div class="card-header bg-light-soft d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <h5 class="mb-0">Students in your pool</h5>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-white text-muted">{{ $poolCount }} {{ Str::plural('student', $poolCount) }}</span>
                            @if($students->isNotEmpty())
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#clearPoolStudentsModal">
                                    Delete all students
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        @if($students->isEmpty())
                            <div class="text-center text-muted py-5 px-3">
                                <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                <p class="mb-1 fw-medium">No students yet</p>
                                <p class="small mb-0">Download the template, fill it in, and import a CSV. Imported students will appear here immediately.</p>
                            </div>
                        @else
                            <p class="text-muted small mb-3">
                                Need to fix many records quickly? Use <strong>Delete all in pool</strong> to clear this competition pool, then upload a corrected CSV.
                            </p>
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-light-soft align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Gender</th>
                                            <th>Class</th>
                                            <th>Age</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($students as $index => $student)
                                            <tr>
                                                <td class="text-muted small">{{ $index + 1 }}</td>
                                                <td>{{ $student->name }}</td>
                                                <td>{{ ucfirst((string) $student->gender) }}</td>
                                                <td>{{ $student->schoolClass->name ?? '—' }}</td>
                                                <td>{{ $student->date_of_birth?->age ?? $student->age ?? '—' }}</td>
                                                <td>
                                                    <a role="button"
                                                       href="javascript:void(0)"
                                                       class="small text-primary text-decoration-none me-3"
                                                       data-bs-toggle="modal"
                                                       data-bs-target="#editStudentModal"
                                                       data-update-url="{{ route('client.students.update', $student) }}"
                                                       data-student-name="{{ e($student->name) }}"
                                                       data-student-gender="{{ e($student->gender) }}"
                                                       data-student-class-id="{{ $student->school_class_id }}"
                                                       data-student-dob="{{ $student->date_of_birth?->format('Y-m-d') }}">Edit</a>
                                                    <a role="button"
                                                       href="javascript:void(0)"
                                                       class="small text-danger text-decoration-none"
                                                       data-bs-toggle="modal"
                                                       data-bs-target="#deleteStudentModal"
                                                       data-destroy-url="{{ route('client.students.destroy', $student) }}"
                                                       data-student-name="{{ e($student->name) }}">Delete</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card border shadow-none">
                    <div class="card-header bg-light-soft">
                        <h5 class="mb-0">School &amp; teacher contact</h5>
                        <small class="text-muted">Included when you submit for admin approval (after the pool qualifies).</small>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('client.competitions.register.store', $competition) }}" method="POST">
                            @csrf

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small">Teacher name</label>
                                    <input type="text" class="form-control bg-light" value="{{ auth()->user()->name }}" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Teacher email</label>
                                    <input type="text" class="form-control bg-light" value="{{ auth()->user()->email }}" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small" for="teacher_phone">Teacher phone</label>
                                    <input type="text" id="teacher_phone" class="form-control bg-light" value="{{ auth()->user()->phone ?: '—' }}" disabled>
                                    <small class="text-muted">Uses your account phone number from profile settings.</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small" for="school_phone">School phone</label>
                                    <input type="text" id="school_phone" class="form-control bg-light" value="{{ $teacherClub->school?->phone ?: '—' }}" disabled>
                                    <small class="text-muted">Pulled from the school profile.</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small" for="school_name">School name <span class="text-danger">*</span></label>
                                    <input type="text" id="school_name" class="form-control" name="school_name" value="{{ old('school_name', $registration->school_name ?? $teacherClub->school?->name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small" for="school_district">District</label>
                                    <input type="text" id="school_district" class="form-control" name="school_district" value="{{ old('school_district', $registration->school_district ?? $teacherClub->school?->district) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small" for="school_ward">Ward</label>
                                    <input type="text" id="school_ward" class="form-control" name="school_ward" value="{{ old('school_ward', $registration->school_ward ?? $teacherClub->school?->ward) }}">
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mt-4">
                                <button type="submit" class="btn btn-sm btn-primary text-white" @if(! $poolOk) disabled title="Meet eligibility in the sidebar first" @endif>
                                    Submit registration for approval
                                </button>
                          </div>
                            @if(! $poolOk)
                                <p class="text-danger small mt-2 mb-0">
                                    Meet the pool rules in the sidebar first: at least {{ $poolMin }} students
                                    @if($poolMax !== null)
                                        and at most {{ $poolMax }}
                                    @endif
                                    , and at least 70% girls, before you can continue.
                                </p>
                            @endif
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border shadow-none mb-3">
                    <div class="card-header bg-light-soft"><h6 class="mb-0">Eligibility (competition pool)</h6></div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Students in pool:</strong> {{ $poolCount }} (min {{ $poolMin }}@if($poolMax !== null), max {{ $poolMax }}@endif)</p>
                        <p class="mb-1"><strong>Girls in pool:</strong> {{ $poolRatio }}% / 70% min</p>
                    </div>
                </div>
                <div class="card border shadow-none mb-3">
                    <div class="card-header bg-light-soft"><h6 class="mb-0">Class range</h6></div>
                    <div class="card-body">
                        @if(empty($allowedClassIds))
                            <span class="text-muted small">All classes count toward this competition&rsquo;s pool.</span>
                        @else
                            <p class="text-muted small mb-2">Only students in these classes are counted for eligibility:</p>
                            <ul class="small mb-0 ps-3">
                                @foreach($competition->classRequirements as $req)
                                    <li class="mb-1">{{ $req->schoolClass->name ?? 'Class #'.$req->school_class_id }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
                <div class="card border shadow-none">
                    <div class="card-header bg-light-soft"><h6 class="mb-0">After registration closes</h6></div>
                    <div class="card-body">
                        <p class="text-muted small mb-0">
                            Submitting sends your participant list and school details for admin review. When this registration phase ends, <strong>no further uploads</strong> are allowed here—you&rsquo;ll follow the competition schedule for the next stage from your hub.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editStudentModalLabel">Edit student</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="editStudentForm" method="POST" action="#">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="redirect_to" value="{{ $registerReturnPath }}">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label small" for="editStudentName">Name</label>
                                <input type="text" name="name" id="editStudentName" class="form-control" required maxlength="255">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small" for="editStudentGender">Gender</label>
                                <select name="gender" id="editStudentGender" class="form-select" required>
                                <option value="female">Female</option>    
                                <option value="male">Male</option>
                               
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small" for="editStudentClassId">Class</label>
                                <select name="school_class_id" id="editStudentClassId" class="form-select" required>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-0">
                                <label class="form-label small" for="editStudentDob">Date of birth</label>
                                <input type="date" name="date_of_birth" id="editStudentDob" class="form-control" required max="{{ now()->format('Y-m-d') }}">
                                <p class="text-muted small mt-1 mb-0">Age is calculated from this date and stays correct each year.</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                         <button type="submit" class="btn btn-sm btn-primary text-white">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="deleteStudentModal" tabindex="-1" aria-labelledby="deleteStudentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteStudentModalLabel">Remove student</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="deleteStudentForm" method="POST" action="#">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="redirect_to" value="{{ $registerReturnPath }}">
                        <div class="modal-body">
                            <p class="mb-0">Remove <strong id="deleteStudentNameDisplay"></strong> from your club roster? They will disappear from this pool. If they were linked to a draft registration, those links are removed too.</p>
                        </div>
                        <div class="modal-footer">
                         <button type="submit" class="btn btn-sm btn-danger text-white">Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="clearPoolStudentsModal" tabindex="-1" aria-labelledby="clearPoolStudentsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="clearPoolStudentsModalLabel">Delete all pool students</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('client.competitions.register.pool-students.clear', $competition) }}">
                        @csrf
                        @method('DELETE')
                        <div class="modal-body">
                            <p class="mb-2">
                                Remove all <strong>{{ $poolCount }}</strong> students currently listed in this competition pool?
                            </p>
                            <p class="text-muted small mb-0">
                                This is intended for fixing bulk import mistakes quickly before you upload a corrected CSV.
                            </p>
                        </div>
                        <div class="modal-footer">
                         <button type="submit" class="btn btn-sm btn-danger text-white">Delete all</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const editModal = document.getElementById('editStudentModal');
                if (editModal) {
                    editModal.addEventListener('show.bs.modal', function (event) {
                        const btn = event.relatedTarget;
                        if (!btn) {
                            return;
                        }
                        const form = document.getElementById('editStudentForm');
                        form.action = btn.getAttribute('data-update-url') || '#';
                        document.getElementById('editStudentName').value = btn.getAttribute('data-student-name') || '';
                        document.getElementById('editStudentGender').value = btn.getAttribute('data-student-gender') || 'male';
                        const classId = btn.getAttribute('data-student-class-id');
                        const classSelect = document.getElementById('editStudentClassId');
                        if (classId && classSelect.querySelector('option[value="' + classId + '"]')) {
                            classSelect.value = classId;
                        }
                        const dob = btn.getAttribute('data-student-dob');
                        document.getElementById('editStudentDob').value = dob && dob.length ? dob : '';
                    });
                }

                const deleteModal = document.getElementById('deleteStudentModal');
                if (deleteModal) {
                    deleteModal.addEventListener('show.bs.modal', function (event) {
                        const btn = event.relatedTarget;
                        if (!btn) {
                            return;
                        }
                        document.getElementById('deleteStudentForm').action = btn.getAttribute('data-destroy-url') || '#';
                        document.getElementById('deleteStudentNameDisplay').textContent = btn.getAttribute('data-student-name') || '';
                    });
                }
            });
        </script>
    </div>
</x-app-layout>
