<x-app-layout>
    <div class="page-heading d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('client.weekly-sessions.index') }}">Weekly Sessions</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('client.weekly-sessions.show', $weeklySession) }}">{{ $weeklySession->title }}</a></li>
                    <li class="breadcrumb-item active">{{ $report ? 'Edit Report' : 'Submit Report' }}</li>
                </ol>
            </nav>
            <h3 class="mb-1">{{ $report ? 'Edit Session Report' : 'Submit Session Report' }}</h3>
            <p class="text-muted mb-0">{{ $weeklySession->title }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('client.weekly-sessions.show', $weeklySession) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Session
            </a>
        </div>
    </div>

    <div class="page-content">
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ $report ? route('client.weekly-sessions.report.update', $weeklySession) : route('client.weekly-sessions.report.store', $weeklySession) }}" method="POST" enctype="multipart/form-data" id="assessmentForm">
            @csrf
            @if($report)
                @method('PUT')
            @endif

            <!-- Progress Indicator -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body py-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <small class="text-muted">Complete the sections below. All fields are optional.</small>
                        <small class="text-muted" id="progressText">Section 1 of 12</small>
                    </div>
                </div>
            </div>

            <!-- Session Info (Read-only) -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3"><i class="bi bi-info-circle me-2"></i>Session Information</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Title/Topic</span>
                            <span class="fw-semibold">{{ $weeklySession->title }}</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Date</span>
                            <span class="fw-semibold">{{ $weeklySession->session_date->format('M d, Y') }}</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Club</span>
                            <span class="badge bg-info">{{ $weeklySession->club->name }}</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Classes</span>
                            <div>
                                @foreach($weeklySession->classes as $class)
                                    <span class="badge bg-primary me-1">{{ $class->name }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 1: Attendance -->
            <div class="card border-0 shadow-sm mb-4" id="section-1">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="bi bi-people me-2"></i>1. Attendance</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="total_students_registered" class="form-label">Total Students Registered</label>
                            <input type="number" class="form-control" 
                                   id="total_students_registered" 
                                   value="{{ $totalStudentsRegistered ?? 0 }}" 
                                   readonly
                                   style="background-color: #e9ecef; cursor: not-allowed;">
                            <input type="hidden" name="total_students_registered" value="{{ $totalStudentsRegistered ?? 0 }}">
                            <small class="text-muted">This is the total number of students registered in your club</small>
                        </div>
                        <div class="col-md-4">
                            <label for="students_present" class="form-label">Students Present Today</label>
                            <input type="number" class="form-control @error('students_present') is-invalid @enderror" 
                                   id="students_present" name="students_present" 
                                   value="{{ old('students_present', $report?->attendance_count ?? $report?->students_present ?? '') }}" 
                                   min="0">
                            @error('students_present')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="girls_present" class="form-label">Girls Present Today</label>
                            <input type="number" class="form-control @error('girls_present') is-invalid @enderror" 
                                   id="girls_present" name="girls_present" 
                                   value="{{ old('girls_present', $report?->girls_present ?? '') }}" 
                                   min="0">
                            @error('girls_present')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Were there any absent students?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="had_absent_students" id="had_absent_yes" value="1" 
                                   {{ old('had_absent_students', $report?->had_absent_students) == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="had_absent_yes">Yes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="had_absent_students" id="had_absent_no" value="0" 
                                   {{ old('had_absent_students', $report?->had_absent_students) === 0 || (old('had_absent_students') === null && (!$report || $report?->had_absent_students === null)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="had_absent_no">No</label>
                        </div>
                    </div>

                    <div id="absenceReasonsSection" class="mt-3" style="display: {{ old('had_absent_students', $report?->had_absent_students) == 1 ? 'block' : 'none' }};">
                        <label class="form-label">Main reason for absence (Select all that apply):</label>
                        @php
                            $absenceReasons = old('absence_reasons', $report?->absence_reasons ?? []);
                        @endphp
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="absence_reasons[]" value="sickness" id="absence_sickness" 
                                   {{ in_array('sickness', $absenceReasons) ? 'checked' : '' }}>
                            <label class="form-check-label" for="absence_sickness">Sickness</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="absence_reasons[]" value="family_reasons" id="absence_family" 
                                   {{ in_array('family_reasons', $absenceReasons) ? 'checked' : '' }}>
                            <label class="form-check-label" for="absence_family">Family reasons</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="absence_reasons[]" value="school_activities" id="absence_school" 
                                   {{ in_array('school_activities', $absenceReasons) ? 'checked' : '' }}>
                            <label class="form-check-label" for="absence_school">School activities</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="absence_reasons[]" value="weather_transport" id="absence_weather" 
                                   {{ in_array('weather_transport', $absenceReasons) ? 'checked' : '' }}>
                            <label class="form-check-label" for="absence_weather">Weather/transport issues</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="absence_reasons[]" value="no_reason" id="absence_no_reason" 
                                   {{ in_array('no_reason', $absenceReasons) ? 'checked' : '' }}>
                            <label class="form-check-label" for="absence_no_reason">No reason given</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="absence_reasons[]" value="other" id="absence_other" 
                                   {{ in_array('other', $absenceReasons) ? 'checked' : '' }}>
                            <label class="form-check-label" for="absence_other">Other</label>
                        </div>
                        <div id="absenceOtherSection" class="mt-2" style="display: {{ in_array('other', $absenceReasons) ? 'block' : 'none' }};">
                            <input type="text" class="form-control" name="absence_reason_other" 
                                   value="{{ old('absence_reason_other', $report?->absence_reason_other ?? '') }}" 
                                   placeholder="Please specify">
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Session Details -->
            <div class="card border-0 shadow-sm mb-4" id="section-2">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="bi bi-clipboard-data me-2"></i>2. Session Details</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Type of activity:</label>
                        @php
                            $activityType = old('activity_type', $report?->activity_type ?? '');
                        @endphp
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="activity_type" id="activity_hands_on" value="hands_on_experiment" 
                                   {{ $activityType === 'hands_on_experiment' ? 'checked' : '' }}>
                            <label class="form-check-label" for="activity_hands_on">Hands-on experiment</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="activity_type" id="activity_project" value="project_work" 
                                   {{ $activityType === 'project_work' ? 'checked' : '' }}>
                            <label class="form-check-label" for="activity_project">Project work</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="activity_type" id="activity_discussion" value="group_discussion" 
                                   {{ $activityType === 'group_discussion' ? 'checked' : '' }}>
                            <label class="form-check-label" for="activity_discussion">Group discussion/brainstorming</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="activity_type" id="activity_competition" value="competition_challenge" 
                                   {{ $activityType === 'competition_challenge' ? 'checked' : '' }}>
                            <label class="form-check-label" for="activity_competition">Competition/challenge</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="activity_type" id="activity_guest" value="guest_speaker" 
                                   {{ $activityType === 'guest_speaker' ? 'checked' : '' }}>
                            <label class="form-check-label" for="activity_guest">Guest speaker/field trip</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="activity_type" id="activity_video" value="video_demonstration" 
                                   {{ $activityType === 'video_demonstration' ? 'checked' : '' }}>
                            <label class="form-check-label" for="activity_video">Video/demonstration</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Did you complete the planned activity?</label>
                        @php
                            $activityCompleted = old('activity_completed', $report?->activity_completed ?? '');
                        @endphp
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="activity_completed" id="completed_yes" value="fully_completed" 
                                   {{ $activityCompleted === 'fully_completed' ? 'checked' : '' }}>
                            <label class="form-check-label" for="completed_yes">Yes, fully completed</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="activity_completed" id="completed_partial" value="partially_completed" 
                                   {{ $activityCompleted === 'partially_completed' ? 'checked' : '' }}>
                            <label class="form-check-label" for="completed_partial">Partially completed</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="activity_completed" id="completed_no" value="not_completed" 
                                   {{ $activityCompleted === 'not_completed' ? 'checked' : '' }}>
                            <label class="form-check-label" for="completed_no">No, we could not complete it</label>
                        </div>
                    </div>

                    <div id="incompletionReasonSection" style="display: {{ in_array($activityCompleted, ['partially_completed', 'not_completed']) ? 'block' : 'none' }};">
                        <label for="incompletion_reason" class="form-label">If not completed, what was the reason?</label>
                        <textarea class="form-control @error('incompletion_reason') is-invalid @enderror" 
                                  id="incompletion_reason" name="incompletion_reason" rows="3">{{ old('incompletion_reason', $report?->incompletion_reason ?? '') }}</textarea>
                        @error('incompletion_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Continue with remaining sections... Due to length, I'll create them systematically -->
            
            <!-- SECTION 3: Student Engagement -->
            <div class="card border-0 shadow-sm mb-4" id="section-3">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="bi bi-people-fill me-2"></i>3. Student Engagement & Participation</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">How engaged were students during the session?</label>
                        @php
                            $engagementLevel = old('engagement_level', $report?->engagement_level ?? '');
                        @endphp
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="engagement_level" id="engagement_very" value="very_engaged" {{ $engagementLevel === 'very_engaged' ? 'checked' : '' }}>
                            <label class="form-check-label" for="engagement_very">Very engaged (most students actively participating)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="engagement_level" id="engagement_moderate" value="moderately_engaged" {{ $engagementLevel === 'moderately_engaged' ? 'checked' : '' }}>
                            <label class="form-check-label" for="engagement_moderate">Moderately engaged (some students participating)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="engagement_level" id="engagement_low" value="low_engagement" {{ $engagementLevel === 'low_engagement' ? 'checked' : '' }}>
                            <label class="form-check-label" for="engagement_low">Low engagement (few students participating)</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">How many students asked questions or shared ideas?</label>
                        @php
                            $questionsParticipation = old('questions_participation', $report?->questions_participation ?? '');
                        @endphp
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="questions_participation" id="questions_most" value="most_students_75pct" {{ $questionsParticipation === 'most_students_75pct' ? 'checked' : '' }}>
                            <label class="form-check-label" for="questions_most">Most students (more than 75%)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="questions_participation" id="questions_half" value="about_half_50_75pct" {{ $questionsParticipation === 'about_half_50_75pct' ? 'checked' : '' }}>
                            <label class="form-check-label" for="questions_half">About half (50-75%)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="questions_participation" id="questions_some" value="some_students_25_50pct" {{ $questionsParticipation === 'some_students_25_50pct' ? 'checked' : '' }}>
                            <label class="form-check-label" for="questions_some">Some students (25-50%)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="questions_participation" id="questions_few" value="very_few_25pct" {{ $questionsParticipation === 'very_few_25pct' ? 'checked' : '' }}>
                            <label class="form-check-label" for="questions_few">Very few (less than 25%)</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Did girls participate actively in today's session?</label>
                        @php
                            $girlsParticipation = old('girls_participation', $report?->girls_participation ?? '');
                        @endphp
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="girls_participation" id="girls_very" value="very_active" {{ $girlsParticipation === 'very_active' ? 'checked' : '' }}>
                            <label class="form-check-label" for="girls_very">Yes, very actively</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="girls_participation" id="girls_moderate" value="moderately" {{ $girlsParticipation === 'moderately' ? 'checked' : '' }}>
                            <label class="form-check-label" for="girls_moderate">Yes, moderately</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="girls_participation" id="girls_not_much" value="not_much" {{ $girlsParticipation === 'not_much' ? 'checked' : '' }}>
                            <label class="form-check-label" for="girls_not_much">Not much</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="girls_participation" id="girls_none" value="no_girls_present" {{ $girlsParticipation === 'no_girls_present' ? 'checked' : '' }}>
                            <label class="form-check-label" for="girls_none">No girls were present</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Did any students show special interest or talent today?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="students_special_interest" id="special_yes" value="1" 
                                   {{ old('students_special_interest', $report?->students_special_interest) == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="special_yes">Yes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="students_special_interest" id="special_no" value="0" 
                                   {{ old('students_special_interest', $report?->students_special_interest) === 0 || (old('students_special_interest') === null && (!$report || $report?->students_special_interest === null)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="special_no">No</label>
                        </div>
                    </div>

                    <div id="specialInterestDetailsSection" style="display: {{ old('students_special_interest', $report?->students_special_interest) == 1 ? 'block' : 'none' }};">
                        <label for="special_interest_details" class="form-label">If yes, please mention their names and what they did:</label>
                        <textarea class="form-control @error('special_interest_details') is-invalid @enderror" 
                                  id="special_interest_details" name="special_interest_details" rows="3">{{ old('special_interest_details', $report?->special_interest_details ?? '') }}</textarea>
                        @error('special_interest_details')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Continue building the remaining sections... This is getting long, let me continue with key sections and ensure the structure is complete -->
            
            <!-- SECTION 4: Learning Outcomes -->
            <div class="card border-0 shadow-sm mb-4" id="section-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="bi bi-book me-2"></i>4. Learning Outcomes</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Did students learn the key concepts from session?</label>
                        @php
                            $learningUnderstanding = old('learning_understanding', $report?->learning_understanding ?? '');
                        @endphp
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="learning_understanding" id="learn_all" value="all_understood" {{ $learningUnderstanding === 'all_understood' ? 'checked' : '' }}>
                            <label class="form-check-label" for="learn_all">Yes, all students understood</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="learning_understanding" id="learn_most" value="most_understood" {{ $learningUnderstanding === 'most_understood' ? 'checked' : '' }}>
                            <label class="form-check-label" for="learn_most">Most students understood</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="learning_understanding" id="learn_some" value="some_understood" {{ $learningUnderstanding === 'some_understood' ? 'checked' : '' }}>
                            <label class="form-check-label" for="learn_some">Some students understood</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="learning_understanding" id="learn_struggled" value="struggled" {{ $learningUnderstanding === 'struggled' ? 'checked' : '' }}>
                            <label class="form-check-label" for="learn_struggled">Students struggled to understand</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">How did you check student understanding? (Select all that apply):</label>
                        @php
                            $checkMethods = old('understanding_check_methods', $report?->understanding_check_methods ?? []);
                        @endphp
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="understanding_check_methods[]" value="questions" id="check_questions" {{ in_array('questions', $checkMethods) ? 'checked' : '' }}>
                            <label class="form-check-label" for="check_questions">Asked questions during session</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="understanding_check_methods[]" value="observed_work" id="check_observed" {{ in_array('observed_work', $checkMethods) ? 'checked' : '' }}>
                            <label class="form-check-label" for="check_observed">Observed their work/projects</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="understanding_check_methods[]" value="quiz_test" id="check_quiz" {{ in_array('quiz_test', $checkMethods) ? 'checked' : '' }}>
                            <label class="form-check-label" for="check_quiz">Quick quiz/test</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="understanding_check_methods[]" value="presentations" id="check_presentations" {{ in_array('presentations', $checkMethods) ? 'checked' : '' }}>
                            <label class="form-check-label" for="check_presentations">Student presentations</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="understanding_check_methods[]" value="group_discussions" id="check_discussions" {{ in_array('group_discussions', $checkMethods) ? 'checked' : '' }}>
                            <label class="form-check-label" for="check_discussions">Group discussions</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="understanding_check_methods[]" value="other" id="check_other" {{ in_array('other', $checkMethods) ? 'checked' : '' }}>
                            <label class="form-check-label" for="check_other">Other</label>
                        </div>
                        <div id="checkOtherSection" class="mt-2" style="display: {{ in_array('other', $checkMethods) ? 'block' : 'none' }};">
                            <input type="text" class="form-control" name="understanding_check_other" 
                                   value="{{ old('understanding_check_other', $report?->understanding_check_other ?? '') }}" 
                                   placeholder="Please specify">
                        </div>
                    </div>

                    <div class="mb-0">
                        <label for="students_learned" class="form-label">What did students learn today? (In simple words)</label>
                        <textarea class="form-control @error('students_learned') is-invalid @enderror" 
                                  id="students_learned" name="students_learned" rows="4">{{ old('students_learned', $report?->students_learned ?? '') }}</textarea>
                        @error('students_learned')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- SECTION 5: Challenges -->
            <div class="card border-0 shadow-sm mb-4" id="section-5">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>5. Challenges & Problems</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Did you face any challenges during session?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="had_challenges" id="challenges_yes" value="1" 
                                   {{ old('had_challenges', $report?->had_challenges) == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="challenges_yes">Yes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="had_challenges" id="challenges_no" value="0" 
                                   {{ old('had_challenges', $report?->had_challenges) === 0 || (old('had_challenges') === null && $report?->had_challenges === null) ? 'checked' : '' }}>
                            <label class="form-check-label" for="challenges_no">No</label>
                        </div>
                    </div>

                    <div id="challengeTypesSection" style="display: {{ old('had_challenges', $report?->had_challenges) == 1 ? 'block' : 'none' }};">
                        <div class="mb-3">
                            <label class="form-label">What challenges did you face? (Select all that apply):</label>
                            @php
                                $challengeTypes = old('challenge_types', $report?->challenge_types ?? []);
                            @endphp
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="challenge_types[]" value="not_enough_materials" id="challenge_materials" {{ in_array('not_enough_materials', $challengeTypes) ? 'checked' : '' }}>
                                <label class="form-check-label" for="challenge_materials">Not enough materials/equipment</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="challenge_types[]" value="equipment_not_working" id="challenge_equipment" {{ in_array('equipment_not_working', $challengeTypes) ? 'checked' : '' }}>
                                <label class="form-check-label" for="challenge_equipment">Equipment not working properly</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="challenge_types[]" value="too_difficult" id="challenge_difficult" {{ in_array('too_difficult', $challengeTypes) ? 'checked' : '' }}>
                                <label class="form-check-label" for="challenge_difficult">Students found activity too difficult</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="challenge_types[]" value="too_easy" id="challenge_easy" {{ in_array('too_easy', $challengeTypes) ? 'checked' : '' }}>
                                <label class="form-check-label" for="challenge_easy">Students found activity too easy</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="challenge_types[]" value="classroom_management" id="challenge_management" {{ in_array('classroom_management', $challengeTypes) ? 'checked' : '' }}>
                                <label class="form-check-label" for="challenge_management">Classroom management issues</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="challenge_types[]" value="not_enough_time" id="challenge_time" {{ in_array('not_enough_time', $challengeTypes) ? 'checked' : '' }}>
                                <label class="form-check-label" for="challenge_time">Not enough time</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="challenge_types[]" value="internet_electricity" id="challenge_internet" {{ in_array('internet_electricity', $challengeTypes) ? 'checked' : '' }}>
                                <label class="form-check-label" for="challenge_internet">Internet/electricity problems</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="challenge_types[]" value="other" id="challenge_other" {{ in_array('other', $challengeTypes) ? 'checked' : '' }}>
                                <label class="form-check-label" for="challenge_other">Other</label>
                            </div>
                            <div id="challengeOtherSection" class="mt-2" style="display: {{ in_array('other', $challengeTypes) ? 'block' : 'none' }};">
                                <input type="text" class="form-control" name="challenge_other" 
                                       value="{{ old('challenge_other', $report?->challenge_other ?? '') }}" 
                                       placeholder="Please specify">
                            </div>
                        </div>

                        <div class="mb-0">
                            <label for="challenge_description" class="form-label">Please describe the main challenge in detail (optional):</label>
                            <textarea class="form-control @error('challenge_description') is-invalid @enderror" 
                                      id="challenge_description" name="challenge_description" rows="3">{{ old('challenge_description', $report?->challenge_description ?? '') }}</textarea>
                            @error('challenge_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 6: Resources -->
            <div class="card border-0 shadow-sm mb-4" id="section-6">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="bi bi-box-seam me-2"></i>6. Resources Used</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="resources_used" class="form-label">What materials/resources did you use today? (List everything)</label>
                        <textarea class="form-control @error('resources_used') is-invalid @enderror" 
                                  id="resources_used" name="resources_used" rows="4">{{ old('resources_used', $report?->resources_used ?? '') }}</textarea>
                        @error('resources_used')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Do you need any additional materials for upcoming sessions?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="needs_additional_materials" id="materials_yes" value="1" 
                                   {{ old('needs_additional_materials', $report?->needs_additional_materials) == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="materials_yes">Yes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="needs_additional_materials" id="materials_no" value="0" 
                                   {{ old('needs_additional_materials', $report?->needs_additional_materials) === 0 || (old('needs_additional_materials') === null && $report?->needs_additional_materials === null) ? 'checked' : '' }}>
                            <label class="form-check-label" for="materials_no">No</label>
                        </div>
                    </div>

                    <div id="additionalMaterialsSection" style="display: {{ old('needs_additional_materials', $report?->needs_additional_materials) == 1 ? 'block' : 'none' }};">
                        <label for="additional_materials_needed" class="form-label">If yes, what do you need?</label>
                        <textarea class="form-control @error('additional_materials_needed') is-invalid @enderror" 
                                  id="additional_materials_needed" name="additional_materials_needed" rows="3">{{ old('additional_materials_needed', $report?->additional_materials_needed ?? '') }}</textarea>
                        @error('additional_materials_needed')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- SECTION 7: Student Confidence & Attitudes -->
            <div class="card border-0 shadow-sm mb-4" id="section-7">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="bi bi-heart me-2"></i>7. Student Confidence & Attitudes</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Did students show confidence in trying new things today?</label>
                        @php
                            $confidence = old('student_confidence', $report?->student_confidence ?? '');
                        @endphp
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="student_confidence" id="confidence_very" value="very_confident" {{ $confidence === 'very_confident' ? 'checked' : '' }}>
                            <label class="form-check-label" for="confidence_very">Yes, very confident</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="student_confidence" id="confidence_somewhat" value="somewhat_confident" {{ $confidence === 'somewhat_confident' ? 'checked' : '' }}>
                            <label class="form-check-label" for="confidence_somewhat">Somewhat confident</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="student_confidence" id="confidence_not" value="not_very_confident" {{ $confidence === 'not_very_confident' ? 'checked' : '' }}>
                            <label class="form-check-label" for="confidence_not">Not very confident</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="student_confidence" id="confidence_hesitant" value="hesitant_afraid" {{ $confidence === 'hesitant_afraid' ? 'checked' : '' }}>
                            <label class="form-check-label" for="confidence_hesitant">Students were hesitant/afraid to try</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Did students work well in teams/groups?</label>
                        @php
                            $teamwork = old('teamwork_quality', $report?->teamwork_quality ?? '');
                        @endphp
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="teamwork_quality" id="teamwork_excellent" value="excellent_teamwork" {{ $teamwork === 'excellent_teamwork' ? 'checked' : '' }}>
                            <label class="form-check-label" for="teamwork_excellent">Yes, excellent teamwork</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="teamwork_quality" id="teamwork_good" value="good_teamwork" {{ $teamwork === 'good_teamwork' ? 'checked' : '' }}>
                            <label class="form-check-label" for="teamwork_good">Yes, good teamwork</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="teamwork_quality" id="teamwork_issues" value="teamwork_issues" {{ $teamwork === 'teamwork_issues' ? 'checked' : '' }}>
                            <label class="form-check-label" for="teamwork_issues">Some teamwork issues</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="teamwork_quality" id="teamwork_alone" value="preferred_alone" {{ $teamwork === 'preferred_alone' ? 'checked' : '' }}>
                            <label class="form-check-label" for="teamwork_alone">Students preferred working alone</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="teamwork_quality" id="teamwork_na" value="not_applicable" {{ $teamwork === 'not_applicable' ? 'checked' : '' }}>
                            <label class="form-check-label" for="teamwork_na">Not applicable (individual activity)</label>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Did you notice positive attitudes toward STEM today?</label>
                        @php
                            $stemAttitudes = old('stem_attitudes', $report?->stem_attitudes ?? '');
                        @endphp
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="stem_attitudes" id="attitude_positive" value="excited_positive" {{ $stemAttitudes === 'excited_positive' ? 'checked' : '' }}>
                            <label class="form-check-label" for="attitude_positive">Yes, students were excited and positive</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="stem_attitudes" id="attitude_neutral" value="interested_neutral" {{ $stemAttitudes === 'interested_neutral' ? 'checked' : '' }}>
                            <label class="form-check-label" for="attitude_neutral">Students were interested but neutral</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="stem_attitudes" id="attitude_bored" value="bored_uninterested" {{ $stemAttitudes === 'bored_uninterested' ? 'checked' : '' }}>
                            <label class="form-check-label" for="attitude_bored">Students seemed bored or uninterested</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 8: Projects & Competitions -->
            <div class="card border-0 shadow-sm mb-4" id="section-8">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="bi bi-folder me-2"></i>8. Projects & Competitions <span class="text-muted small">(optional)</span></h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Are students currently working on any STEM projects?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="has_active_projects" id="projects_yes" value="1" 
                                   {{ old('has_active_projects', $report?->has_active_projects) == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="projects_yes">Yes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="has_active_projects" id="projects_no" value="0" 
                                   {{ old('has_active_projects', $report?->has_active_projects) === 0 || (old('has_active_projects') === null && $report?->has_active_projects === null) ? 'checked' : '' }}>
                            <label class="form-check-label" for="projects_no">No</label>
                        </div>
                    </div>

                    <div id="projectDetailsSection" style="display: {{ old('has_active_projects', $report?->has_active_projects) == 1 ? 'block' : 'none' }};">
                        <div class="mb-3">
                            <label for="project_description" class="form-label">If yes, please describe the project(s):</label>
                            <textarea class="form-control @error('project_description') is-invalid @enderror" 
                                      id="project_description" name="project_description" rows="3">{{ old('project_description', $report?->project_description ?? '') }}</textarea>
                            @error('project_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Project completion status:</label>
                            @php
                                $projectStatus = old('project_completion_status', $report?->project_completion_status ?? '');
                            @endphp
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="project_completion_status" id="project_started" value="just_started_0_25" {{ $projectStatus === 'just_started_0_25' ? 'checked' : '' }}>
                                <label class="form-check-label" for="project_started">Just started (0-25% complete)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="project_completion_status" id="project_progress" value="in_progress_25_75" {{ $projectStatus === 'in_progress_25_75' ? 'checked' : '' }}>
                                <label class="form-check-label" for="project_progress">In progress (25-75% complete)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="project_completion_status" id="project_almost" value="almost_done_75_100" {{ $projectStatus === 'almost_done_75_100' ? 'checked' : '' }}>
                                <label class="form-check-label" for="project_almost">Almost done (75-100% complete)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="project_completion_status" id="project_completed" value="completed" {{ $projectStatus === 'completed' ? 'checked' : '' }}>
                                <label class="form-check-label" for="project_completed">Completed</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="project_completion_status" id="project_na" value="not_applicable" {{ $projectStatus === 'not_applicable' ? 'checked' : '' }}>
                                <label class="form-check-label" for="project_na">Not applicable</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Did you prepare students for any competition or event this week?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="competition_preparation" id="comp_yes" value="1" 
                                   {{ old('competition_preparation', $report?->competition_preparation) == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="comp_yes">Yes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="competition_preparation" id="comp_no" value="0" 
                                   {{ old('competition_preparation', $report?->competition_preparation) === 0 || (old('competition_preparation') === null && $report?->competition_preparation === null) ? 'checked' : '' }}>
                            <label class="form-check-label" for="comp_no">No</label>
                        </div>
                    </div>

                    <div id="competitionNameSection" style="display: {{ old('competition_preparation', $report?->competition_preparation) == 1 ? 'block' : 'none' }};">
                        <label for="competition_name" class="form-label">If yes, which competition/event?</label>
                        <input type="text" class="form-control @error('competition_name') is-invalid @enderror" 
                               id="competition_name" name="competition_name" 
                               value="{{ old('competition_name', $report?->competition_name ?? '') }}">
                        @error('competition_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- SECTION 9: Highlights -->
            <div class="card border-0 shadow-sm mb-4" id="section-9">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="bi bi-star me-2"></i>9. Highlights & Success Stories</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="session_highlight" class="form-label">What was the best moment or highlight of today's session?</label>
                        <textarea class="form-control @error('session_highlight') is-invalid @enderror" 
                                  id="session_highlight" name="session_highlight" rows="3">{{ old('session_highlight', $report?->session_highlight ?? '') }}</textarea>
                        @error('session_highlight')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-0">
                        <label for="interesting_questions" class="form-label">Did any student say something interesting or ask a great question?</label>
                        <textarea class="form-control @error('interesting_questions') is-invalid @enderror" 
                                  id="interesting_questions" name="interesting_questions" rows="3">{{ old('interesting_questions', $report?->interesting_questions ?? '') }}</textarea>
                        @error('interesting_questions')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- SECTION 10: Photos -->
            <div class="card border-0 shadow-sm mb-4" id="section-10">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="bi bi-camera me-2"></i>10. Photos</h6>
                </div>
                <div class="card-body">
                    @if($report?->photos->count() > 0)
                        <div class="mb-3">
                            <label class="form-label">Current Photos</label>
                            <div class="row g-3">
                                @foreach($report?->photos as $photo)
                                    <div class="col-md-4">
                                        <div class="border rounded p-2">
                                            <img src="{{ Storage::url($photo->file_path) }}" alt="Session photo" class="img-fluid rounded mb-2" style="max-height: 150px; object-fit: cover; width: 100%;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="delete_photos[]" value="{{ $photo->id }}" id="delete_photo_{{ $photo->id }}">
                                                <label class="form-check-label small" for="delete_photo_{{ $photo->id }}">Delete</label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mb-0">
                        <label for="photos" class="form-label">
                            @if($report?->photos->count() > 0)
                                Add More Photos ({{ $report?->photos->count() }}/5 uploaded)
                            @else
                                Upload Photos (maximum 5 photos)
                            @endif
                        </label>
                        <input type="file" class="form-control @error('photos.*') is-invalid @enderror" 
                               id="photos" name="photos[]" multiple accept="image/*">
                        @error('photos.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Supported: JPG, PNG, GIF, WEBP. Max 5MB per photo. Maximum 5 photos total.</small>
                        <div id="photoPreview" class="mt-3 row g-2"></div>
                    </div>
                </div>
            </div>

            <!-- SECTION 11: Additional Comments -->
            <div class="card border-0 shadow-sm mb-4" id="section-11">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="bi bi-chat-dots me-2"></i>11. Additional Comments</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="additional_comments" class="form-label">Any other observations, suggestions, or comments:</label>
                        <textarea class="form-control @error('additional_comments') is-invalid @enderror" 
                                  id="additional_comments" name="additional_comments" rows="4">{{ old('additional_comments', $report?->additional_comments ?? '') }}</textarea>
                        @error('additional_comments')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Do you need any support from the program coordinators?</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="needs_support" id="support_yes" value="1" 
                                   {{ old('needs_support', $report?->needs_support) == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="support_yes">Yes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="needs_support" id="support_no" value="0" 
                                   {{ old('needs_support', $report?->needs_support) === 0 || (old('needs_support') === null && $report?->needs_support === null) ? 'checked' : '' }}>
                            <label class="form-check-label" for="support_no">No</label>
                        </div>
                    </div>

                    <div id="supportNeededSection" style="display: {{ old('needs_support', $report?->needs_support) == 1 ? 'block' : 'none' }};">
                        <label for="support_needed" class="form-label">If yes, what kind of support do you need?</label>
                        <textarea class="form-control @error('support_needed') is-invalid @enderror" 
                                  id="support_needed" name="support_needed" rows="3">{{ old('support_needed', $report?->support_needed ?? '') }}</textarea>
                        @error('support_needed')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- SECTION 12: Teacher Reflection -->
            <div class="card border-0 shadow-sm mb-4" id="section-12">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>12. Teacher Reflection</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">How satisfied are you with today's session?</label>
                        @php
                            $satisfaction = old('session_satisfaction', $report?->session_satisfaction ?? '');
                        @endphp
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="session_satisfaction" id="satisfaction_very" value="very_satisfied" {{ $satisfaction === 'very_satisfied' ? 'checked' : '' }}>
                            <label class="form-check-label" for="satisfaction_very">Very satisfied</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="session_satisfaction" id="satisfaction_satisfied" value="satisfied" {{ $satisfaction === 'satisfied' ? 'checked' : '' }}>
                            <label class="form-check-label" for="satisfaction_satisfied">Satisfied</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="session_satisfaction" id="satisfaction_neutral" value="neutral" {{ $satisfaction === 'neutral' ? 'checked' : '' }}>
                            <label class="form-check-label" for="satisfaction_neutral">Neutral</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="session_satisfaction" id="satisfaction_not" value="not_satisfied" {{ $satisfaction === 'not_satisfied' ? 'checked' : '' }}>
                            <label class="form-check-label" for="satisfaction_not">Not satisfied</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="would_do_differently" class="form-label">What would you do differently next time?</label>
                        <textarea class="form-control @error('would_do_differently') is-invalid @enderror" 
                                  id="would_do_differently" name="would_do_differently" rows="3">{{ old('would_do_differently', $report?->would_do_differently ?? '') }}</textarea>
                        @error('would_do_differently')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-0">
                        <label for="what_worked_well" class="form-label">What worked really well today that you want to repeat?</label>
                        <textarea class="form-control @error('what_worked_well') is-invalid @enderror" 
                                  id="what_worked_well" name="what_worked_well" rows="3">{{ old('what_worked_well', $report?->what_worked_well ?? '') }}</textarea>
                        @error('what_worked_well')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-check-circle me-1"></i> {{ $report ? 'Update Report' : 'Submit Report' }}
                        </button>
                        <a href="{{ route('client.weekly-sessions.show', $weeklySession) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x me-1"></i> Cancel
                        </a>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-info-circle me-1"></i>
                        All fields are optional. Fill in what you can - your feedback helps improve the program.
                    </small>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Conditional field visibility
            const conditionalFields = {
                'had_absent_students': {
                    '1': ['absenceReasonsSection'],
                },
                'students_special_interest': {
                    '1': ['specialInterestDetailsSection'],
                },
                'activity_completed': {
                    'partially_completed': ['incompletionReasonSection'],
                    'not_completed': ['incompletionReasonSection'],
                },
                'had_challenges': {
                    '1': ['challengeTypesSection'],
                },
                'needs_additional_materials': {
                    '1': ['additionalMaterialsSection'],
                },
                'has_active_projects': {
                    '1': ['projectDetailsSection'],
                },
                'competition_preparation': {
                    '1': ['competitionNameSection'],
                },
                'needs_support': {
                    '1': ['supportNeededSection'],
                },
            };

            // Set up conditional field handlers
            Object.keys(conditionalFields).forEach(fieldName => {
                const radios = document.querySelectorAll(`input[name="${fieldName}"]`);
                radios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        const value = this.value;
                        const sections = conditionalFields[fieldName][value] || [];
                        
                        // Hide all related sections first
                        Object.values(conditionalFields[fieldName]).flat().forEach(sectionId => {
                            const section = document.getElementById(sectionId);
                            if (section) section.style.display = 'none';
                        });
                        
                        // Show relevant sections
                        sections.forEach(sectionId => {
                            const section = document.getElementById(sectionId);
                            if (section) section.style.display = 'block';
                        });
                    });
                });
            });

            // Handle "other" checkbox options
            const otherCheckboxes = {
                'absence_other': 'absenceOtherSection',
                'check_other': 'checkOtherSection',
                'challenge_other': 'challengeOtherSection',
            };

            Object.keys(otherCheckboxes).forEach(checkboxId => {
                const checkbox = document.getElementById(checkboxId);
                if (checkbox) {
                    checkbox.addEventListener('change', function() {
                        const section = document.getElementById(otherCheckboxes[checkboxId]);
                        if (section) {
                            section.style.display = this.checked ? 'block' : 'none';
                        }
                    });
                }
            });

            // Photo preview
            const photoInput = document.getElementById('photos');
            const photoPreview = document.getElementById('photoPreview');
            
            if (photoInput && photoPreview) {
                photoInput.addEventListener('change', function(e) {
                    photoPreview.innerHTML = '';
                    const files = Array.from(e.target.files).slice(0, 5);
                    
                    files.forEach((file, index) => {
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const col = document.createElement('div');
                                col.className = 'col-md-4';
                                col.innerHTML = `
                                    <div class="border rounded p-2">
                                        <img src="${e.target.result}" class="img-fluid rounded mb-2" style="max-height: 150px; object-fit: cover; width: 100%;">
                                        <small class="text-muted">${file.name}</small>
                                    </div>
                                `;
                                photoPreview.appendChild(col);
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                });
            }

            // Update progress text on scroll
            const sections = document.querySelectorAll('[id^="section-"]');
            const progressText = document.getElementById('progressText');
            
            function updateProgress() {
                if (!progressText) return;
                
                let currentSection = 1;
                sections.forEach((section, index) => {
                    const rect = section.getBoundingClientRect();
                    if (rect.top <= 100) {
                        currentSection = index + 1;
                    }
                });
                progressText.textContent = `Section ${currentSection} of ${sections.length}`;
            }
            
            window.addEventListener('scroll', updateProgress);
            updateProgress();
        });
    </script>
    @endpush
</x-app-layout>
