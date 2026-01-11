<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a href="{{ route('admin.weekly-sessions.reports.index') }}">Session Reports</a></li>
                            <li class="breadcrumb-item active">Report Details</li>
                        </ol>
                    </nav>
                    <h5 class="mb-0">Session Report Details</h5>
                    <p class="text-muted mb-0">Report for {{ $report->weeklySession->title }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.weekly-sessions.reports.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left"></i> Back to Reports
                    </a>
                    <a href="{{ route('admin.weekly-sessions.show', $report->weeklySession) }}" class="btn btn-outline-info">
                        <i class="ti ti-clipboard-check"></i> View Session
                    </a>
                </div>
            </div>

            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Report Metadata -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Session</span>
                            <a href="{{ route('admin.weekly-sessions.show', $report->weeklySession) }}" class="fw-semibold text-dark">
                                {{ $report->weeklySession->title }}
                            </a>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Club</span>
                            <span class="badge bg-info">{{ $report->weeklySession->club->name }}</span>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Teacher</span>
                            <div>{{ $report->teacher->name }}</div>
                            <small class="text-muted">{{ $report->teacher->email }}</small>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Submitted At</span>
                            <div>{{ $report->submitted_at->format('M d, Y') }}</div>
                            <small class="text-muted">{{ $report->submitted_at->format('H:i A') }}</small>
                        </div>
                        @if($report->conducted_at)
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Session Date</span>
                            <div>{{ $report->weeklySession->session_date->format('M d, Y') }}</div>
                            <small class="text-muted">Conducted: {{ $report->conducted_at->format('M d, Y') }}</small>
                        </div>
                        @endif
                        @if($report->teacherClub->school)
                        <div class="col-md-3">
                            <span class="text-muted small d-block">School</span>
                            <div>{{ $report->teacherClub->school->name }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Attendance Section -->
            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ti ti-users me-2"></i>Attendance</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @if($report->total_students_registered)
                        <div class="col-md-4">
                            <span class="text-muted small d-block">Total Students Registered</span>
                            <div class="fw-semibold">{{ $report->total_students_registered }}</div>
                        </div>
                        @endif
                        @if($report->attendance_count)
                        <div class="col-md-4">
                            <span class="text-muted small d-block">Students Present</span>
                            <div class="fw-semibold">{{ $report->attendance_count }}</div>
                        </div>
                        @endif
                        @if($report->girls_present !== null)
                        <div class="col-md-4">
                            <span class="text-muted small d-block">Girls Present</span>
                            <div class="fw-semibold">{{ $report->girls_present }}</div>
                        </div>
                        @endif
                        @if($report->had_absent_students !== null)
                        <div class="col-md-12">
                            <span class="text-muted small d-block">Had Absent Students</span>
                            <span class="badge {{ $report->had_absent_students ? 'bg-warning' : 'bg-success' }}">
                                {{ $report->had_absent_students ? 'Yes' : 'No' }}
                            </span>
                            @if($report->had_absent_students && $report->absence_reasons)
                                <div class="mt-2">
                                    <span class="text-muted small d-block">Reasons:</span>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($report->absence_reasons as $reason)
                                            <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $reason)) }}</span>
                                        @endforeach
                                        @if($report->absence_reason_other)
                                            <span class="badge bg-secondary">{{ $report->absence_reason_other }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Session Details -->
            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ti ti-clipboard-data me-2"></i>Session Details</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @if($report->activity_type)
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Activity Type</span>
                            @php
                                $activityTypes = [
                                    'hands_on_experiment' => 'Hands-on Experiment',
                                    'project_work' => 'Project Work',
                                    'group_discussion' => 'Group Discussion',
                                    'competition_challenge' => 'Competition/Challenge',
                                    'guest_speaker' => 'Guest Speaker/Field Trip',
                                    'video_demonstration' => 'Video/Demonstration',
                                ];
                                $activityLabel = $activityTypes[$report->activity_type] ?? ucfirst(str_replace('_', ' ', $report->activity_type));
                            @endphp
                            <span class="badge bg-primary">{{ $activityLabel }}</span>
                        </div>
                        @endif
                        @if($report->activity_completed)
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Activity Completion</span>
                            @php
                                $completionBadges = [
                                    'fully_completed' => ['bg-success', 'Fully Completed'],
                                    'partially_completed' => ['bg-warning text-dark', 'Partially Completed'],
                                    'not_completed' => ['bg-danger', 'Not Completed'],
                                ];
                                $badge = $completionBadges[$report->activity_completed] ?? ['bg-secondary', ucfirst(str_replace('_', ' ', $report->activity_completed))];
                            @endphp
                            <span class="badge {{ $badge[0] }}">{{ $badge[1] }}</span>
                        </div>
                        @endif
                        @if($report->incompletion_reason)
                        <div class="col-md-12">
                            <span class="text-muted small d-block">Incompletion Reason</span>
                            <div>{{ $report->incompletion_reason }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Student Engagement -->
            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ti ti-users-group me-2"></i>Student Engagement & Participation</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @if($report->engagement_level)
                        <div class="col-md-4">
                            <span class="text-muted small d-block">Engagement Level</span>
                            @php
                                $engagementBadges = [
                                    'very_engaged' => ['bg-success', 'Very Engaged'],
                                    'moderately_engaged' => ['bg-info', 'Moderately Engaged'],
                                    'low_engagement' => ['bg-warning text-dark', 'Low Engagement'],
                                ];
                                $badge = $engagementBadges[$report->engagement_level] ?? ['bg-secondary', ucfirst(str_replace('_', ' ', $report->engagement_level))];
                            @endphp
                            <span class="badge {{ $badge[0] }}">{{ $badge[1] }}</span>
                        </div>
                        @endif
                        @if($report->questions_participation)
                        <div class="col-md-4">
                            <span class="text-muted small d-block">Questions Participation</span>
                            @php
                                $participationLabels = [
                                    'most_students_75pct' => 'Most Students (>75%)',
                                    'about_half_50_75pct' => 'About Half (50-75%)',
                                    'some_students_25_50pct' => 'Some Students (25-50%)',
                                    'very_few_25pct' => 'Very Few (<25%)',
                                ];
                                $label = $participationLabels[$report->questions_participation] ?? ucfirst(str_replace('_', ' ', $report->questions_participation));
                            @endphp
                            <div>{{ $label }}</div>
                        </div>
                        @endif
                        @if($report->girls_participation)
                        <div class="col-md-4">
                            <span class="text-muted small d-block">Girls Participation</span>
                            @php
                                $girlsLabels = [
                                    'very_active' => 'Very Active',
                                    'moderately' => 'Moderately',
                                    'not_much' => 'Not Much',
                                    'no_girls_present' => 'No Girls Present',
                                ];
                                $label = $girlsLabels[$report->girls_participation] ?? ucfirst(str_replace('_', ' ', $report->girls_participation));
                            @endphp
                            <div>{{ $label }}</div>
                        </div>
                        @endif
                        @if($report->students_special_interest !== null)
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Students Showed Special Interest</span>
                            <span class="badge {{ $report->students_special_interest ? 'bg-success' : 'bg-secondary' }}">
                                {{ $report->students_special_interest ? 'Yes' : 'No' }}
                            </span>
                        </div>
                        @endif
                        @if($report->special_interest_details)
                        <div class="col-md-12">
                            <span class="text-muted small d-block">Special Interest Details</span>
                            <div>{{ $report->special_interest_details }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Learning Outcomes -->
            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ti ti-book me-2"></i>Learning Outcomes</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @if($report->learning_understanding)
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Understanding Level</span>
                            @php
                                $understandingLabels = [
                                    'all_understood' => 'All Understood',
                                    'most_understood' => 'Most Understood',
                                    'some_understood' => 'Some Understood',
                                    'struggled' => 'Students Struggled',
                                ];
                                $label = $understandingLabels[$report->learning_understanding] ?? ucfirst(str_replace('_', ' ', $report->learning_understanding));
                            @endphp
                            <div>{{ $label }}</div>
                        </div>
                        @endif
                        @if($report->understanding_check_methods)
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Check Methods</span>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($report->understanding_check_methods as $method)
                                    <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $method)) }}</span>
                                @endforeach
                                @if($report->understanding_check_other)
                                    <span class="badge bg-info">{{ $report->understanding_check_other }}</span>
                                @endif
                            </div>
                        </div>
                        @endif
                        @if($report->students_learned)
                        <div class="col-md-12">
                            <span class="text-muted small d-block">What Students Learned</span>
                            <div>{{ $report->students_learned }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Challenges -->
            @if($report->had_challenges !== null)
            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ti ti-alert-triangle me-2"></i>Challenges & Problems</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Faced Challenges</span>
                            <span class="badge {{ $report->had_challenges ? 'bg-warning' : 'bg-success' }}">
                                {{ $report->had_challenges ? 'Yes' : 'No' }}
                            </span>
                        </div>
                        @if($report->had_challenges && $report->challenge_types)
                        <div class="col-md-12">
                            <span class="text-muted small d-block">Challenge Types</span>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($report->challenge_types as $challenge)
                                    <span class="badge bg-warning text-dark">{{ ucfirst(str_replace('_', ' ', $challenge)) }}</span>
                                @endforeach
                                @if($report->challenge_other)
                                    <span class="badge bg-warning text-dark">{{ $report->challenge_other }}</span>
                                @endif
                            </div>
                        </div>
                        @endif
                        @if($report->challenge_description)
                        <div class="col-md-12">
                            <span class="text-muted small d-block">Challenge Description</span>
                            <div>{{ $report->challenge_description }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Resources -->
            @if($report->resources_used || $report->needs_additional_materials !== null)
            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ti ti-tool me-2"></i>Resources Used</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @if($report->resources_used)
                        <div class="col-md-12">
                            <span class="text-muted small d-block">Materials/Resources Used</span>
                            <div>{{ $report->resources_used }}</div>
                        </div>
                        @endif
                        @if($report->needs_additional_materials !== null)
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Needs Additional Materials</span>
                            <span class="badge {{ $report->needs_additional_materials ? 'bg-warning' : 'bg-success' }}">
                                {{ $report->needs_additional_materials ? 'Yes' : 'No' }}
                            </span>
                        </div>
                        @endif
                        @if($report->additional_materials_needed)
                        <div class="col-md-12">
                            <span class="text-muted small d-block">Additional Materials Needed</span>
                            <div>{{ $report->additional_materials_needed }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Student Confidence & Attitudes -->
            @if($report->student_confidence || $report->teamwork_quality || $report->stem_attitudes)
            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ti ti-heart me-2"></i>Student Confidence & Attitudes</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @if($report->student_confidence)
                        <div class="col-md-4">
                            <span class="text-muted small d-block">Student Confidence</span>
                            @php
                                $confidenceLabels = [
                                    'very_confident' => 'Very Confident',
                                    'somewhat_confident' => 'Somewhat Confident',
                                    'not_very_confident' => 'Not Very Confident',
                                    'hesitant_afraid' => 'Hesitant/Afraid',
                                ];
                                $label = $confidenceLabels[$report->student_confidence] ?? ucfirst(str_replace('_', ' ', $report->student_confidence));
                            @endphp
                            <div>{{ $label }}</div>
                        </div>
                        @endif
                        @if($report->teamwork_quality)
                        <div class="col-md-4">
                            <span class="text-muted small d-block">Teamwork Quality</span>
                            @php
                                $teamworkLabels = [
                                    'excellent_teamwork' => 'Excellent Teamwork',
                                    'good_teamwork' => 'Good Teamwork',
                                    'teamwork_issues' => 'Teamwork Issues',
                                    'preferred_alone' => 'Preferred Working Alone',
                                    'not_applicable' => 'Not Applicable',
                                ];
                                $label = $teamworkLabels[$report->teamwork_quality] ?? ucfirst(str_replace('_', ' ', $report->teamwork_quality));
                            @endphp
                            <div>{{ $label }}</div>
                        </div>
                        @endif
                        @if($report->stem_attitudes)
                        <div class="col-md-4">
                            <span class="text-muted small d-block">STEM Attitudes</span>
                            @php
                                $attitudeLabels = [
                                    'excited_positive' => 'Excited & Positive',
                                    'interested_neutral' => 'Interested but Neutral',
                                    'bored_uninterested' => 'Bored/Uninterested',
                                ];
                                $label = $attitudeLabels[$report->stem_attitudes] ?? ucfirst(str_replace('_', ' ', $report->stem_attitudes));
                            @endphp
                            <div>{{ $label }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Projects & Competitions -->
            @if($report->has_active_projects !== null || $report->competition_preparation !== null)
            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ti ti-folder me-2"></i>Projects & Competitions</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @if($report->has_active_projects !== null)
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Active Projects</span>
                            <span class="badge {{ $report->has_active_projects ? 'bg-success' : 'bg-secondary' }}">
                                {{ $report->has_active_projects ? 'Yes' : 'No' }}
                            </span>
                        </div>
                        @endif
                        @if($report->project_description)
                        <div class="col-md-12">
                            <span class="text-muted small d-block">Project Description</span>
                            <div>{{ $report->project_description }}</div>
                        </div>
                        @endif
                        @if($report->project_completion_status)
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Project Completion Status</span>
                            @php
                                $statusLabels = [
                                    'just_started_0_25' => 'Just Started (0-25%)',
                                    'in_progress_25_75' => 'In Progress (25-75%)',
                                    'almost_done_75_100' => 'Almost Done (75-100%)',
                                    'completed' => 'Completed',
                                    'not_applicable' => 'Not Applicable',
                                ];
                                $label = $statusLabels[$report->project_completion_status] ?? ucfirst(str_replace('_', ' ', $report->project_completion_status));
                            @endphp
                            <div>{{ $label }}</div>
                        </div>
                        @endif
                        @if($report->competition_preparation !== null)
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Competition Preparation</span>
                            <span class="badge {{ $report->competition_preparation ? 'bg-info' : 'bg-secondary' }}">
                                {{ $report->competition_preparation ? 'Yes' : 'No' }}
                            </span>
                        </div>
                        @endif
                        @if($report->competition_name)
                        <div class="col-md-12">
                            <span class="text-muted small d-block">Competition Name</span>
                            <div class="fw-semibold">{{ $report->competition_name }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Highlights -->
            @if($report->session_highlight || $report->interesting_questions)
            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ti ti-star me-2"></i>Highlights & Success Stories</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @if($report->session_highlight)
                        <div class="col-md-12">
                            <span class="text-muted small d-block">Session Highlight</span>
                            <div>{{ $report->session_highlight }}</div>
                        </div>
                        @endif
                        @if($report->interesting_questions)
                        <div class="col-md-12">
                            <span class="text-muted small d-block">Interesting Questions/Comments</span>
                            <div>{{ $report->interesting_questions }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Photos -->
            @if($report->photos->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ti ti-photo me-2"></i>Photos ({{ $report->photos->count() }})</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($report->photos as $photo)
                            <div class="col-md-4">
                                <div class="border rounded p-2">
                                    <a href="{{ Storage::url($photo->file_path) }}" target="_blank" class="d-block">
                                        <img src="{{ Storage::url($photo->file_path) }}" 
                                             alt="Session photo" 
                                             class="img-fluid rounded" 
                                             style="max-height: 200px; width: 100%; object-fit: cover; cursor: pointer;">
                                    </a>
                                    @if($photo->original_filename)
                                        <small class="text-muted d-block mt-2">{{ $photo->original_filename }}</small>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Additional Comments -->
            @if($report->additional_comments || $report->needs_support !== null)
            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ti ti-message-circle me-2"></i>Additional Comments</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @if($report->additional_comments)
                        <div class="col-md-12">
                            <span class="text-muted small d-block">Comments/Observations</span>
                            <div>{{ $report->additional_comments }}</div>
                        </div>
                        @endif
                        @if($report->needs_support !== null)
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Needs Support</span>
                            <span class="badge {{ $report->needs_support ? 'bg-warning' : 'bg-success' }}">
                                {{ $report->needs_support ? 'Yes' : 'No' }}
                            </span>
                        </div>
                        @endif
                        @if($report->support_needed)
                        <div class="col-md-12">
                            <span class="text-muted small d-block">Support Needed</span>
                            <div>{{ $report->support_needed }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Teacher Reflection -->
            @if($report->session_satisfaction || $report->would_do_differently || $report->what_worked_well)
            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ti ti-lightbulb me-2"></i>Teacher Reflection</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @if($report->session_satisfaction)
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Session Satisfaction</span>
                            @php
                                $satisfactionLabels = [
                                    'very_satisfied' => 'Very Satisfied',
                                    'satisfied' => 'Satisfied',
                                    'neutral' => 'Neutral',
                                    'not_satisfied' => 'Not Satisfied',
                                ];
                                $label = $satisfactionLabels[$report->session_satisfaction] ?? ucfirst(str_replace('_', ' ', $report->session_satisfaction));
                                $satisfactionBadges = [
                                    'very_satisfied' => 'bg-success',
                                    'satisfied' => 'bg-info',
                                    'neutral' => 'bg-secondary',
                                    'not_satisfied' => 'bg-danger',
                                ];
                                $badgeClass = $satisfactionBadges[$report->session_satisfaction] ?? 'bg-secondary';
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $label }}</span>
                        </div>
                        @endif
                        @if($report->would_do_differently)
                        <div class="col-md-12">
                            <span class="text-muted small d-block">What Would You Do Differently Next Time?</span>
                            <div>{{ $report->would_do_differently }}</div>
                        </div>
                        @endif
                        @if($report->what_worked_well)
                        <div class="col-md-12">
                            <span class="text-muted small d-block">What Worked Really Well Today?</span>
                            <div>{{ $report->what_worked_well }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Legacy Fields (if present) -->
            @if($report->feedback || $report->engagement_notes)
            <div class="card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ti ti-notes me-2"></i>Additional Notes</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @if($report->feedback)
                        <div class="col-md-12">
                            <span class="text-muted small d-block">Feedback</span>
                            <div>{{ $report->feedback }}</div>
                        </div>
                        @endif
                        @if($report->engagement_notes)
                        <div class="col-md-12">
                            <span class="text-muted small d-block">Engagement Notes</span>
                            <div>{{ $report->engagement_notes }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-admin-layout>
