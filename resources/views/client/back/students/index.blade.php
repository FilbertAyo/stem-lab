<x-app-layout>


    <div class="page-content">
        <section class="row">
            <div class="col-12">
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($teacherClubs->isEmpty())
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-people display-4 text-muted mb-3"></i>
                            <h5 class="fw-semibold mb-2">No clubs joined yet</h5>
                            <p class="text-muted mb-3">Join a club to start registering students from your school.</p>
                            <a href="{{ route('client.clubs.index') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-school me-1"></i>Browse Clubs
                            </a>
                        </div>
                    </div>
                @else
                    <div class="card border-0 shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><i class="bi bi-people me-2"></i>All Registered Students</h5>
                                <small class="text-muted">Students across all your schools and clubs.</small>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#registerStudentModal">
                                <i class="bi bi-plus-circle me-1"></i>Register Student
                            </button>
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
                                    <table class="table table-bordered table-hover align-middle">
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
                                                    <td><span class="badge bg-secondary">{{ ucfirst($student->gender) }}</span></td>
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
                                <div class="alert alert-info mb-3">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Club:</strong> {{ $teacherClubs->first()->club->name }}<br>
                                    <strong>School:</strong> {{ $teacherClubs->first()->school->name ?? 'N/A' }}
                                </div>
                            @endif

                            <div class="mb-3">
                                <label for="student_name" class="form-label">
                                    Student Name <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="student_name"
                                       name="name"
                                       value="{{ old('name') }}"
                                       placeholder="Enter student's full name"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="student_age" class="form-label">
                                        Age <span class="text-danger">*</span>
                                    </label>
                                    <input type="number"
                                           class="form-control @error('age') is-invalid @enderror"
                                           id="student_age"
                                           name="age"
                                           value="{{ old('age') }}"
                                           min="1"
                                           max="150"
                                           placeholder="e.g. 15"
                                           required>
                                    @error('age')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="student_gender" class="form-label">
                                        Gender <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('gender') is-invalid @enderror"
                                            id="student_gender"
                                            name="gender"
                                            required>
                                        <option value="">Select Gender</option>

                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
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
                                    <option value="">Select Class</option>
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
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i>
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>
                                Register Student
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            // Reopen modal with validation errors if form submission failed
            @if($errors->any() && (old('teacher_club_id') || $teacherClubs->count() == 1))
                document.addEventListener('DOMContentLoaded', function() {
                    const modalElement = document.getElementById('registerStudentModal');
                    if (modalElement) {
                        const modal = new bootstrap.Modal(modalElement);
                        modal.show();
                    }
                });
            @endif
        </script>
    @endpush
</x-app-layout>

