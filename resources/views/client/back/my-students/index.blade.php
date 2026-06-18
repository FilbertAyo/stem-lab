<x-app-layout>
    <div class="col-lg-9">

        <div class="dashboard-content-header">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h4 class="mb-1">My Students</h4>
                    <p class="text-muted mb-0 small">Monitor club students who have joined the platform and track their learning progress.</p>
                </div>
                <a href="{{ route('client.clubs.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-people me-1"></i>Manage roster
                </a>
            </div>
        </div>

        @include('elements.client-validation')

        @if($teacherClubs->isEmpty())
            <div class="card border shadow-none mt-3">
                <div class="card-body text-center py-5">
                    <i class="bi bi-people display-4 text-muted mb-3"></i>
                    <h5 class="fw-semibold mb-2">Join a club first</h5>
                    <p class="text-muted mb-3">You need an approved club membership before you can monitor students.</p>
                    <a href="{{ route('client.clubs.index') }}" class="btn btn-primary text-white">Go to Clubs</a>
                </div>
            </div>
        @else
            <form method="GET" action="{{ route('client.my-students.index') }}" class="card border shadow-none mt-3 mb-4">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="club" class="form-label small text-muted mb-1">Club</label>
                            <select name="club" id="club" class="form-select">
                                <option value="all" @selected(($filters['club'] ?? 'all') === 'all')>All my clubs</option>
                                @foreach($teacherClubs as $membership)
                                    <option value="{{ $membership->id }}" @selected((string) ($filters['club'] ?? '') === (string) $membership->id)>
                                        {{ $membership->club->name ?? 'Club' }}
                                        @if($membership->school)
                                            — {{ $membership->school->name }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="class" class="form-label small text-muted mb-1">Class</label>
                            <select name="class" id="class" class="form-select">
                                <option value="">All classes</option>
                                @foreach($classes as $schoolClass)
                                    <option value="{{ $schoolClass->id }}" @selected((string) ($filters['class'] ?? '') === (string) $schoolClass->id)>
                                        {{ $schoolClass->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="registration" class="form-label small text-muted mb-1">Account</label>
                            <select name="registration" id="registration" class="form-select">
                                <option value="all" @selected(($filters['registration'] ?? 'all') === 'all')>All</option>
                                <option value="registered" @selected(($filters['registration'] ?? '') === 'registered')>Registered</option>
                                <option value="not_registered" @selected(($filters['registration'] ?? '') === 'not_registered')>Not registered</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="activity" class="form-label small text-muted mb-1">Activity</label>
                            <select name="activity" id="activity" class="form-select">
                                <option value="all" @selected(($filters['activity'] ?? 'all') === 'all')>All</option>
                                <option value="active" @selected(($filters['activity'] ?? '') === 'active')>Active (7 days)</option>
                                <option value="inactive" @selected(($filters['activity'] ?? '') === 'inactive')>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="attention" class="form-label small text-muted mb-1">Support</label>
                            <select name="attention" id="attention" class="form-select">
                                <option value="all" @selected(($filters['attention'] ?? 'all') === 'all')>All</option>
                                <option value="needs_support" @selected(($filters['attention'] ?? '') === 'needs_support')>Needs attention</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="q" class="form-label small text-muted mb-1">Search</label>
                            <input type="search" name="q" id="q" class="form-control" value="{{ $filters['q'] ?? '' }}" placeholder="Name or admission no.">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary text-white w-100">Apply filters</button>
                        </div>
                    </div>
                </div>
            </form>

            <section class="row g-3 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="card border shadow-none h-100">
                        <div class="card-body">
                            <h6 class="text-muted small mb-1">On roster</h6>
                            <h4 class="mb-0 fw-bold">{{ $analytics['total_roster'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card border shadow-none h-100">
                        <div class="card-body">
                            <h6 class="text-muted small mb-1">Registered</h6>
                            <h4 class="mb-0 fw-bold">{{ $analytics['registered'] }}</h4>
                            @if($analytics['total_roster'] > 0)
                                <small class="text-muted">{{ round(($analytics['registered'] / $analytics['total_roster']) * 100) }}% joined</small>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card border shadow-none h-100">
                        <div class="card-body">
                            <h6 class="text-muted small mb-1">Active (7 days)</h6>
                            <h4 class="mb-0 fw-bold">{{ $analytics['active'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card border shadow-none h-100">
                        <div class="card-body">
                            <h6 class="text-muted small mb-1">Needs attention</h6>
                            <h4 class="mb-0 fw-bold text-danger">{{ $analytics['needs_attention'] }}</h4>
                        </div>
                    </div>
                </div>
            </section>

            <div class="card border shadow-none">
                <div class="card-header bg-light-soft d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="mb-0">Students</h5>
                    @if($analytics['avg_vr_score'] !== null)
                        <span class="text-muted small">Avg quiz score: {{ $analytics['avg_vr_score'] }}%</span>
                    @endif
                </div>
                <div class="card-body">
                    @if($students->isEmpty())
                        <div class="text-center py-4">
                            <i class="bi bi-search display-6 text-muted mb-3"></i>
                            <p class="text-muted mb-2">No students match your filters.</p>
                            <a href="{{ route('client.clubs.index') }}" class="btn btn-outline-primary btn-sm">Upload student roster</a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-light-soft align-middle mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Student</th>
                                        <th>Class</th>
                                        <th>Account</th>
                                        <th>Lessons</th>
                                        <th>Quiz avg</th>
                                        <th>Resources</th>
                                        <th>Last activity</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $student)
                                        @php($summary = $student->progressSummary)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $student->name }}</div>
                                                <code class="small">{{ $student->admission_number }}</code>
                                                @if($summary && $summary->needsAttention())
                                                    <span class="badge bg-warning text-dark ms-1" title="Needs follow-up">!</span>
                                                @endif
                                            </td>
                                            <td>{{ $student->schoolClass->name ?? '—' }}</td>
                                            <td>
                                                @if($student->isLinked())
                                                    <span class="badge bg-success">Registered</span>
                                                @else
                                                    <span class="badge bg-secondary">Not registered</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($student->isLinked() && $summary)
                                                    {{ $summary->models_completed }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>
                                                @if($student->isLinked() && $summary && $summary->avg_vr_score !== null)
                                                    {{ number_format($summary->avg_vr_score, 0) }}%
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>
                                                @if($student->isLinked() && $summary)
                                                    {{ $summary->resources_accessed + $summary->projects_viewed }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>
                                                @if($student->isLinked() && $summary?->last_activity_at)
                                                    {{ $summary->last_activity_at->diffForHumans() }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if($student->isLinked())
                                                    <a href="{{ route('client.my-students.show', $student) }}" class="btn btn-sm btn-primary text-white">View progress</a>
                                                @else
                                                    <span class="text-muted small">Awaiting signup</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $students->links() }}
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
