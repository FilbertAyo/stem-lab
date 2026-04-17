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

            <!-- Flash Messages -->
            @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ti ti-check-circle me-2"></i>
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ti ti-alert-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

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
                                <p class="mb-0">{{ $school->district ?? '—' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Ward</label>
                                <p class="mb-0">{{ $school->ward ?? '—' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Region</label>
                                <p class="mb-0">{{ $school->regionModel?->name ?? $school->region ?? '—' }}</p>
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
                        <div class="col-4">
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
                                    <table class="table table-hover table-bordered mb-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Clubs</th>
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
                                                    <td>{{ $teacher->email }}</td>
                                                    <td>
                                                        @foreach($teacherClubs as $tc)
                                                            <div class="d-inline-flex align-items-center me-2 mb-2">
                                                                <span class="badge bg-info">{{ $tc->club->name ?? 'N/A' }}</span>
                                                            
                                                            </div>
                                                        @endforeach
                                                    </td>
                                                    <td>
                                                                        <form
                                                                            action="{{ route('admin.schools.remove-teacher-from-club', $tc) }}"
                                                                            method="POST"
                                                                            class="d-inline ms-1"
                                                                            onsubmit="return confirm('Are you sure you want to remove this teacher from this club? This action cannot be undone.')"
                                                                        >
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button 
                                                                                type="submit" 
                                                                                class="btn btn-sm btn-outline-danger p-1" 
                                                                                title="Remove from club"
                                                                            >
                                                                                remove
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
                                    <table class="table table-hover table-bordered mb-0">
                                        <thead class="sticky-top bg-white">
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Age</th>
                                                <th>Gender</th>
                                                <th>Class</th>
                                                <th>Actions</th>
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
                                                    <td>
                                                        <form
                                                            action="{{ route('admin.schools.remove-student', [$school, $student]) }}"
                                                            method="POST"
                                                            class="d-inline"
                                                            onsubmit="return confirm('Are you sure you want to delete {{ $student->name }}? This action cannot be undone.')"
                                                        >
                                                            @csrf
                                                            @method('DELETE')
                                                            <button
                                                                type="submit"
                                                                class="btn btn-sm btn-outline-danger p-1"
                                                                title="Delete student"
                                                            >
                                                                delete
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
                </div>
            </div>

         
        </div>
    </div>
</x-admin-layout>

