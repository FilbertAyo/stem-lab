<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Weekly Session Reports</h5>
                    <p class="text-muted mb-0">View all session reports submitted by teachers</p>
                </div>
                <div class="d-flex gap-2">
                    <a
                        href="{{ route('admin.weekly-sessions.reports.weekly-pdf', request()->only(['club_id', 'start_date', 'end_date', 'teacher', 'session'])) }}"
                        class="btn btn-outline-secondary"
                    >
                        <i class="ti ti-download"></i> Weekly PDF
                    </a>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newReportModal">
                        <i class="ti ti-plus"></i> New Report
                    </button>
                </div>
            </div>

       
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
                                            @if($report->weeklySession)
                                                <div>
                                                    <a href="{{ route('admin.weekly-sessions.show', $report->weeklySession) }}" class="fw-semibold text-dark">
                                                        {{ $report->weeklySession->title }}
                                                    </a>
                                                </div>
                                                @if($report->weeklySession->subject)
                                                    <small class="text-muted">{{ $report->weeklySession->subject->subject_name }}</small>
                                                @endif
                                            @else
                                                <span class="fw-semibold">Own School Project</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $report->weeklySession?->club?->name ?? ($report->teacherClub?->club?->name ?? 'N/A') }}</span>
                                        </td>
                                        <td>
                                            <div>{{ $report->teacher->name }}</div>
                                            <small class="text-muted">{{ $report->teacher->email }}</small>
                                        </td>
                                        <td>
                                            {{ $report->weeklySession?->session_date?->format('M d, Y') ?? $report->conducted_at?->format('M d, Y') ?? '—' }}
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
                                            @if($report->weeklySession)
                                                <a href="{{ route('admin.weekly-sessions.reports.show', $report) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="ti ti-eye"></i> View
                                                </a>
                                            @else
                                                <span class="text-muted">Quick Report</span>
                                            @endif
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

<!-- New Report Modal -->
<div class="modal fade" id="newReportModal" tabindex="-1" aria-labelledby="newReportModalLabel" aria-hidden="true" data-open-on-load="{{ $errors->any() ? '1' : '0' }}">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.weekly-sessions.reports.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="newReportModalLabel">Create Call Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <strong>Please fix the errors below:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-12" id="school_search_wrap">
                            <label class="form-label">Search School</label>
                            <input
                                type="text"
                                id="school_search"
                                class="form-control"
                                placeholder="Type school name to filter teacher clubs..."
                                value="{{ old('school_search') }}"
                            >
                            <small class="text-muted">Start here to quickly find a school, then choose teacher club and session.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Weekly Session (Current Week)</label>
                            <select name="weekly_session_id" id="weekly_session_id" class="form-select @error('weekly_session_id') is-invalid @enderror">
                                <option value="">{{ $weeklySessions->isEmpty() ? 'No weekly session this week' : 'No session this week (school own project)' }}</option>
                                @foreach($weeklySessions as $weeklySession)
                                    <option value="{{ $weeklySession->id }}" data-club-id="{{ $weeklySession->club_id }}" @selected(old('weekly_session_id') == $weeklySession->id)>
                                        {{ $weeklySession->session_date?->format('M d, Y') }} - {{ $weeklySession->title }} ({{ $weeklySession->club->name ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Only sessions scheduled in the current week are shown.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teacher Club <span class="text-danger">*</span></label>
                            <select name="teacher_club_id" id="teacher_club_id" class="form-select @error('teacher_club_id') is-invalid @enderror" required>
                                <option value="">Select teacher</option>
                                @foreach($teacherClubs as $teacherClub)
                                    <option
                                        value="{{ $teacherClub->id }}"
                                        data-club-id="{{ $teacherClub->club_id }}"
                                        data-school-id="{{ $teacherClub->school_id }}"
                                        @selected(old('teacher_club_id') == $teacherClub->id)
                                    >
                                        {{ $teacherClub->user->name ?? 'N/A' }} - {{ $teacherClub->club->name ?? 'N/A' }} ({{ $teacherClub->school->name ?? 'No School' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Attendance</label>
                            <input type="number" name="attendance_count" min="0" class="form-control @error('attendance_count') is-invalid @enderror" value="{{ old('attendance_count') }}" placeholder="Present">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Registered</label>
                            <input type="number" name="total_students_registered" min="0" class="form-control @error('total_students_registered') is-invalid @enderror" value="{{ old('total_students_registered') }}" placeholder="Total registered">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Girls Present</label>
                            <input type="number" name="girls_present" min="0" class="form-control @error('girls_present') is-invalid @enderror" value="{{ old('girls_present') }}" placeholder="Girls present">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Call Date</label>
                            <input type="date" name="conducted_at" class="form-control @error('conducted_at') is-invalid @enderror" value="{{ old('conducted_at', now()->toDateString()) }}">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="outside_weekly_session_project"
                                    id="outside_weekly_session_project"
                                    value="1"
                                    @checked(old('outside_weekly_session_project'))
                                >
                                <label class="form-check-label" for="outside_weekly_session_project">
                                    They worked on their own project (not session plan)
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">What students learned</label>
                            <textarea name="students_learned" rows="2" class="form-control @error('students_learned') is-invalid @enderror" placeholder="Short summary">{{ old('students_learned') }}</textarea>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Challenges (select one or more)</label>
                            <select name="challenge_types[]" class="form-select @error('challenge_types') is-invalid @enderror" multiple>
                                @php($challengeOptions = [
                                    'internet' => 'Internet issues',
                                    'lack_of_equipment' => 'Lack of equipment',
                                    'power_outage' => 'Power outage',
                                    'low_attendance' => 'Low attendance',
                                    'time_constraints' => 'Time constraints',
                                    'insufficient_materials' => 'Insufficient materials',
                                    'space_limitations' => 'Space limitations',
                                    'other' => 'Other',
                                ])
                                @foreach($challengeOptions as $challengeValue => $challengeLabel)
                                    <option value="{{ $challengeValue }}" @selected(collect(old('challenge_types', []))->contains($challengeValue))>{{ $challengeLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Other challenge</label>
                            <input type="text" name="challenge_other" class="form-control @error('challenge_other') is-invalid @enderror" value="{{ old('challenge_other') }}" placeholder="If other, specify">
                        </div>
                        <div class="col-12 {{ old('outside_weekly_session_project') ? '' : 'd-none' }}" id="outside_project_notes_wrap">
                            <label class="form-label">Own Project Notes</label>
                            <textarea name="outside_weekly_session_notes" rows="2" class="form-control @error('outside_weekly_session_notes') is-invalid @enderror" placeholder="What they worked on instead">{{ old('outside_weekly_session_notes') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Admin Notes</label>
                            <textarea name="additional_comments" rows="2" class="form-control @error('additional_comments') is-invalid @enderror" placeholder="Any follow-up notes">{{ old('additional_comments') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var sessionSelect = document.getElementById('weekly_session_id');
        var teacherClubSelect = document.getElementById('teacher_club_id');
        var schoolSearchInput = document.getElementById('school_search');
        var schoolSearchWrap = document.getElementById('school_search_wrap');
        var projectCheckbox = document.getElementById('outside_weekly_session_project');
        var projectNotesWrap = document.getElementById('outside_project_notes_wrap');

        function filterTeacherClubsBySessionClub() {
            if (!sessionSelect || !teacherClubSelect) return;

            var selectedSession = sessionSelect.options[sessionSelect.selectedIndex];
            var clubId = selectedSession ? selectedSession.getAttribute('data-club-id') : null;
            var schoolSearchValue = schoolSearchInput ? schoolSearchInput.value.toLowerCase().trim() : '';

            Array.from(teacherClubSelect.options).forEach(function (option, index) {
                if (index === 0) return;
                var optionClubId = option.getAttribute('data-club-id');
                var optionSchoolName = (option.textContent || '').toLowerCase();
                var matchesSchoolSearch = !schoolSearchValue || optionSchoolName.includes(schoolSearchValue);
                var shouldShow = (clubId ? optionClubId === clubId : true) && matchesSchoolSearch;
                option.hidden = !shouldShow;
                if (!shouldShow && option.selected) {
                    option.selected = false;
                }
            });
        }

        function toggleOwnProjectNotes() {
            if (!projectCheckbox || !projectNotesWrap) return;
            projectNotesWrap.classList.toggle('d-none', !projectCheckbox.checked);
        }

        if (sessionSelect) {
            sessionSelect.addEventListener('change', filterTeacherClubsBySessionClub);
            filterTeacherClubsBySessionClub();
        }
        if (schoolSearchInput) {
            schoolSearchInput.addEventListener('input', filterTeacherClubsBySessionClub);
        }

        if (projectCheckbox) {
            projectCheckbox.addEventListener('change', toggleOwnProjectNotes);
            toggleOwnProjectNotes();
        }

        var modalEl = document.getElementById('newReportModal');
        if (modalEl && modalEl.dataset.openOnLoad === '1' && window.bootstrap) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    });
</script>
