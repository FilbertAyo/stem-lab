<x-app-layout>
    <div class="col-lg-9">

        <div class="dashboard-content-header mb-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('client.competitions.index') }}">Competitions</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('client.competitions.show', $competition) }}">{{ $competition->name }}</a></li>
                    <li class="breadcrumb-item active">Register</li>
                </ol>
            </nav>
            <h2 class="title mb-0">Register for Competition</h2>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-none">
                        <div class="card-header bg-light-soft">
                        <h5 class="mb-0">{{ $competition->name }}</h5>
                        <small class="text-muted">{{ $competition->club->name }}</small>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="background-color: #ffebeb; border-color: #ffc2c2;">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-exclamation-triangle me-2 fs-5 mt-1"></i>
                                    <div>
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('client.competitions.register.store', $competition) }}" method="POST">
                            @csrf

                            <!-- Team Information -->
                            <div class="mb-4">
                                <h6 class="fw-semibold border-bottom pb-2 mb-3">
                                    <i class="bi bi-people me-2"></i>Team Information
                                </h6>
                                
                                <div class="mb-3">
                                    <label for="team_name" class="form-label">Team Name</label>
                                    <input type="text" class="form-control @error('team_name') is-invalid @enderror" 
                                           id="team_name" name="team_name" value="{{ old('team_name') }}"
                                           placeholder="e.g., Science Innovators">
                                    @error('team_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Optional. If not provided, your team will be named automatically.</small>
                                </div>

                                <div class="mb-3">
                                    <label for="team_description" class="form-label">Team Description</label>
                                    <textarea class="form-control @error('team_description') is-invalid @enderror" 
                                              id="team_description" name="team_description" rows="3"
                                              placeholder="Briefly describe your team and what you hope to achieve...">{{ old('team_description') }}</textarea>
                                    @error('team_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Student Selection -->
                            <div class="mb-4">
                                <h6 class="fw-semibold border-bottom pb-2 mb-3">
                                    <i class="bi bi-person-check me-2"></i>Select Students
                                    <span class="badge bg-info ms-2">{{ $competition->min_members }}-{{ $competition->max_members ?? '∞' }} required</span>
                                </h6>

                                @if($students->isEmpty())
                                    <div class="alert alert-warning alert-dismissible fade show" role="alert" style="background-color: #fff3cd; border-color: #ffeeba;">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        @if($competition->min_age || $competition->max_age)
                                            No students match the age requirements ({{ $competition->min_age ?? 'Any' }}-{{ $competition->max_age ?? 'Any' }} years). 
                                            Please <a href="{{ route('client.students.index') }}">register eligible students</a> first.
                                        @else
                                            You have no students registered. Please <a href="{{ route('client.students.index') }}">register students</a> first.
                                        @endif
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @else
                                    <div class="alert alert-info small mb-3 alert-dismissible fade show" role="alert" style="background-color: #e7f5ff; border-color: #b6d4fe;">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Select between {{ $competition->min_members }} and {{ $competition->max_members ?? 'unlimited' }} students for your team.
                                        @if($competition->min_age || $competition->max_age)
                                            <br>Age requirement: {{ $competition->min_age ?? 'Any' }} - {{ $competition->max_age ?? 'Any' }} years.
                                        @endif
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-hover table-light-soft">
                                            <thead>
                                                <tr>
                                                    <th style="width: 50px;">Select</th>
                                                    <th>Student Name</th>
                                                    <th>Age</th>
                                                    <th>Class</th>
                                                    <th>Role (Optional)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($students as $student)
                                                    <tr>
                                                        <td>
                                                            <div class="form-check">
                                                                <input class="form-check-input student-checkbox" type="checkbox" 
                                                                       name="student_ids[]" value="{{ $student->id }}"
                                                                       id="student_{{ $student->id }}"
                                                                       {{ in_array($student->id, old('student_ids', [])) ? 'checked' : '' }}>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <label for="student_{{ $student->id }}" class="mb-0 fw-semibold">
                                                                {{ $student->name }}
                                                            </label>
                                                        </td>
                                                        <td>{{ $student->age }} years</td>
                                                        <td>{{ $student->schoolClass->name ?? '—' }}</td>
                                                        <td>
                                                            <input type="text" class="form-control form-control-sm" 
                                                                   name="student_roles[{{ $student->id }}]"
                                                                   value="{{ old("student_roles.{$student->id}") }}"
                                                                   placeholder="e.g., Team Leader">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <span class="text-muted">
                                            Selected: <strong id="selectedCount">0</strong> / {{ $competition->max_members ?? '∞' }}
                                        </span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="selectAll">Select All</button>
                                    </div>
                                @endif
                            </div>

                            <!-- School Info -->
                            <div class="mb-4">
                                <h6 class="fw-semibold border-bottom pb-2 mb-3">
                                    <i class="bi bi-building me-2"></i>School Information
                                </h6>
                                <div class="bg-light rounded p-3">
                                    <p class="mb-1"><strong>School:</strong> {{ $teacherClub->school->name ?? 'Not specified' }}</p>
                                    <p class="mb-0"><strong>Club:</strong> {{ $teacherClub->club->name }}</p>
                                </div>
                            </div>

                            <!-- Submit -->
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" {{ $students->isEmpty() ? 'disabled' : '' }}>
                                    <i class="bi bi-check-circle me-1"></i> Register Team
                                </button>
                                <a href="{{ route('client.competitions.show', $competition) }}" class="btn btn-secondary text-white">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-none mb-4">
                    <div class="card-header bg-light-soft">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Requirements</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                <strong>Team Size:</strong> {{ $competition->min_members }}-{{ $competition->max_members ?? '∞' }} members
                            </li>
                            @if($competition->min_age || $competition->max_age)
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    <strong>Age Range:</strong> {{ $competition->min_age ?? 'Any' }}-{{ $competition->max_age ?? 'Any' }} years
                                </li>
                            @endif
                            <li class="mb-2">
                                <i class="bi bi-clock text-warning me-2"></i>
                                <strong>Deadline:</strong> {{ $competition->registration_deadline?->format('d M Y, H:i') ?? 'No deadline' }}
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card border-0 shadow-none bg-light-soft">
                    <div class="card-body">
                        <h6><i class="bi bi-lightbulb me-2"></i>Tips</h6>
                        <ul class="small text-muted mb-0">
                            <li>Select students who meet the age requirements</li>
                            <li>Assign roles to help organize your team</li>
                            <li>Your registration will be reviewed by admins</li>
                            <li>You'll be notified once approved</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const checkboxes = document.querySelectorAll('.student-checkbox');
                const countEl = document.getElementById('selectedCount');
                const selectAllBtn = document.getElementById('selectAll');
                const maxMembers = {{ $competition->max_members ?? 'null' }};

                function updateCount() {
                    const checked = document.querySelectorAll('.student-checkbox:checked').length;
                    countEl.textContent = checked;
                }

                checkboxes.forEach(cb => {
                    cb.addEventListener('change', function() {
                        if (maxMembers !== null) {
                            const checked = document.querySelectorAll('.student-checkbox:checked').length;
                            if (checked > maxMembers) {
                                this.checked = false;
                                alert(`Maximum ${maxMembers} students allowed.`);
                            }
                        }
                        updateCount();
                    });
                });

                selectAllBtn?.addEventListener('click', function() {
                    const allChecked = document.querySelectorAll('.student-checkbox:checked').length === checkboxes.length;
                    let count = 0;
                    checkboxes.forEach(cb => {
                        if (allChecked) {
                            cb.checked = false;
                        } else if (maxMembers === null || count < maxMembers) {
                            cb.checked = true;
                            count++;
                        }
                    });
                    updateCount();
                });

                updateCount();
            });
        </script>
    @endpush
</x-app-layout>

