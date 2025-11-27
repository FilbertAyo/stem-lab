<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">{{ $school->name }}</h5>
                    <p class="text-muted mb-0">School Details</p>
                </div>
                <a href="{{ route('admin.schools.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left"></i> Back to Schools
                </a>
            </div>

            <!-- School Information Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="ti ti-building"></i> School Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
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
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small">District</label>
                                <p class="mb-0">{{ $school->district ?? '—' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Region</label>
                                <p class="mb-0">{{ $school->region ?? '—' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Status</label>
                                <p class="mb-0">
                                    <span class="badge {{ $school->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $school->is_active ? 'Active' : 'Disabled' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-0">
                                <label class="text-muted small">Address</label>
                                <p class="mb-0">{{ $school->address ?? '—' }}</p>
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
                            <span class="badge bg-primary">{{ $teachers->count() }}</span>
                        </div>
                        <div class="card-body p-0">
                            @if($teachers->isEmpty())
                                <div class="text-center text-muted py-4">
                                    <i class="ti ti-users-off fs-1 d-block mb-2"></i>
                                    No teachers registered for this school yet.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Clubs</th>
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
                                                    <td>{{ $teacher->email }}</td>
                                                    <td>
                                                        @foreach($teacherClubs as $tc)
                                                            <span class="badge bg-info">{{ $tc->club->name ?? 'N/A' }}</span>
                                                        @endforeach
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
                            <span class="badge bg-primary">{{ $students->count() }}</span>
                        </div>
                        <div class="card-body p-0">
                            @if($students->isEmpty())
                                <div class="text-center text-muted py-4">
                                    <i class="ti ti-user-off fs-1 d-block mb-2"></i>
                                    No students registered for this school yet.
                                </div>
                            @else
                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table table-hover mb-0">
                                        <thead class="sticky-top bg-white">
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Age</th>
                                                <th>Gender</th>
                                                <th>Class</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($students as $student)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td class="fw-semibold">{{ $student->name }}</td>
                                                    <td>{{ $student->age ?? '—' }}</td>
                                                    <td>
                                                        @if($student->gender)
                                                            <span class="badge {{ $student->gender === 'male' ? 'bg-info' : 'bg-pink' }}">
                                                                {{ ucfirst($student->gender) }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $student->schoolClass->name ?? '—' }}</td>
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

            <!-- Summary Stats -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="ti ti-users fs-1"></i>
                            <h3 class="mt-2 mb-0">{{ $teachers->count() }}</h3>
                            <p class="mb-0">Total Teachers</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="ti ti-school fs-1"></i>
                            <h3 class="mt-2 mb-0">{{ $students->count() }}</h3>
                            <p class="mb-0">Total Students</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <i class="ti ti-certificate fs-1"></i>
                            <h3 class="mt-2 mb-0">{{ $school->teacherClubs->pluck('club_id')->unique()->count() }}</h3>
                            <p class="mb-0">Clubs Active</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

