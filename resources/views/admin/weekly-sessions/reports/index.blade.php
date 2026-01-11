<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Weekly Session Reports</h5>
                    <p class="text-muted mb-0">View all session reports submitted by teachers</p>
                </div>
            </div>

            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Statistics Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-1">Total Reports</p>
                                    <h4 class="mb-0">{{ number_format($statistics['total']) }}</h4>
                                </div>
                                <div class="text-primary">
                                    <i class="ti ti-file-text fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-1">This Week</p>
                                    <h4 class="mb-0">{{ number_format($statistics['this_week']) }}</h4>
                                </div>
                                <div class="text-success">
                                    <i class="ti ti-calendar-week fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-1">This Month</p>
                                    <h4 class="mb-0">{{ number_format($statistics['this_month']) }}</h4>
                                </div>
                                <div class="text-info">
                                    <i class="ti ti-calendar-month fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-1">Avg Attendance</p>
                                    <h4 class="mb-0">{{ $statistics['avg_attendance'] }}</h4>
                                </div>
                                <div class="text-warning">
                                    <i class="ti ti-users fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-3">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('admin.weekly-sessions.reports.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small">Club</label>
                            <select name="club_id" class="form-select">
                                <option value="">All Clubs</option>
                                @foreach($clubs as $club)
                                    <option value="{{ $club->id }}" {{ request('club_id') == $club->id ? 'selected' : '' }}>
                                        {{ $club->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Teacher</label>
                            <input type="text" name="teacher" class="form-control" placeholder="Search teacher..." value="{{ request('teacher') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Session</label>
                            <input type="text" name="session" class="form-control" placeholder="Search session..." value="{{ request('session') }}">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-outline-primary w-100">
                                <i class="ti ti-filter"></i> Filter
                            </button>
                        </div>
                        <div class="col-md-1">
                            <a href="{{ route('admin.weekly-sessions.reports.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="ti ti-x"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Reports Table -->
            <div class="card tbl-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Session</th>
                                    <th>Club</th>
                                    <th>Teacher</th>
                                    <th>Session Date</th>
                                    <th>Attendance</th>
                                    <th>Engagement</th>
                                    <th>Activity Type</th>
                                    <th>Photos</th>
                                    <th>Submitted At</th>
                                    <th class="text-center" style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($reports as $report)
                                    <tr>
                                        <td>{{ $loop->iteration + ($reports->currentPage() - 1) * $reports->perPage() }}</td>
                                        <td>
                                            <div>
                                                <a href="{{ route('admin.weekly-sessions.show', $report->weeklySession) }}" class="fw-semibold text-dark">
                                                    {{ $report->weeklySession->title }}
                                                </a>
                                            </div>
                                            @if($report->weeklySession->subject)
                                                <small class="text-muted">{{ $report->weeklySession->subject->subject_name }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $report->weeklySession->club->name }}</span>
                                        </td>
                                        <td>
                                            <div>{{ $report->teacher->name }}</div>
                                            <small class="text-muted">{{ $report->teacher->email }}</small>
                                        </td>
                                        <td>
                                            {{ $report->weeklySession->session_date->format('M d, Y') }}
                                        </td>
                                        <td>
                                            @if($report->attendance_count)
                                                <div>{{ $report->attendance_count }}</div>
                                                @if($report->total_students_registered)
                                                    <small class="text-muted">/ {{ $report->total_students_registered }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($report->engagement_level)
                                                @php
                                                    $engagementBadges = [
                                                        'very_engaged' => ['bg-success', 'Very Engaged'],
                                                        'moderately_engaged' => ['bg-info', 'Moderate'],
                                                        'low_engagement' => ['bg-warning text-dark', 'Low'],
                                                    ];
                                                    $badge = $engagementBadges[$report->engagement_level] ?? ['bg-secondary', ucfirst(str_replace('_', ' ', $report->engagement_level))];
                                                @endphp
                                                <span class="badge {{ $badge[0] }}">{{ $badge[1] }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($report->activity_type)
                                                @php
                                                    $activityTypes = [
                                                        'hands_on_experiment' => 'Hands-on',
                                                        'project_work' => 'Project',
                                                        'group_discussion' => 'Discussion',
                                                        'competition_challenge' => 'Competition',
                                                        'guest_speaker' => 'Guest Speaker',
                                                        'video_demonstration' => 'Video',
                                                    ];
                                                    $label = $activityTypes[$report->activity_type] ?? ucfirst(str_replace('_', ' ', $report->activity_type));
                                                @endphp
                                                <span class="badge bg-primary">{{ $label }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($report->photos->count() > 0)
                                                <span class="badge bg-success">
                                                    <i class="ti ti-camera"></i> {{ $report->photos->count() }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ $report->submitted_at->format('M d, Y') }}</div>
                                            <small class="text-muted">{{ $report->submitted_at->format('H:i') }}</small>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.weekly-sessions.reports.show', $report) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="ti ti-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="ti ti-file-off fs-1 d-block mb-2"></i>
                                                No reports found.
                                                @if(request()->anyFilled(['club_id', 'start_date', 'end_date', 'teacher', 'session']))
                                                    <div class="mt-2">
                                                        <a href="{{ route('admin.weekly-sessions.reports.index') }}" class="btn btn-sm btn-outline-secondary">
                                                            Clear Filters
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($reports->hasPages())
                        <div class="mt-3">
                            {{ $reports->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
