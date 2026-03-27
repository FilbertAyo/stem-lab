@php
    $reportData = $report ?? null;
    $existingPhotos = $reportData?->photos ?? collect();
    $absenceReasons = old('absence_reasons', $reportData?->absence_reasons ?? []);
    $understandingMethods = old('understanding_check_methods', $reportData?->understanding_check_methods ?? []);
    $challengeTypes = old('challenge_types', $reportData?->challenge_types ?? []);
@endphp

<div class="card border-0 shadow-none mb-4 report-section">
    <div class="card-body">
        <h6 class="fw-semibold mb-3">1. Attendance</h6>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Total Students Registered</label>
                <input type="number" min="0" name="total_students_registered" class="form-control border p-2" value="{{ old('total_students_registered', $reportData?->total_students_registered ?? $totalStudentsRegistered ?? 0) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Students Present</label>
                <input type="number" min="0" name="students_present" class="form-control border p-2" value="{{ old('students_present', $reportData?->attendance_count) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Girls Present</label>
                <input type="number" min="0" name="girls_present" class="form-control border p-2" value="{{ old('girls_present', $reportData?->girls_present) }}">
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-none mb-4 report-section">
    <div class="card-body">
        <h6 class="fw-semibold mb-3">2. Session Delivery</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Activity Type</label>
                <select name="activity_type" class="form-select border p-2">
                    <option value="">Select</option>
                    @foreach([
                        'hands_on_experiment' => 'Hands-on Experiment',
                        'project_work' => 'Project Work',
                        'group_discussion' => 'Group Discussion',
                        'competition_challenge' => 'Competition Challenge',
                        'guest_speaker' => 'Guest Speaker',
                        'video_demonstration' => 'Video Demonstration',
                    ] as $value => $label)
                        <option value="{{ $value }}" @selected(old('activity_type', $reportData?->activity_type) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Completion Status</label>
                <select name="activity_completed" id="activity_completed" class="form-select border p-2">
                    <option value="">Select</option>
                    @foreach([
                        'fully_completed' => 'Fully Completed',
                        'partially_completed' => 'Partially Completed',
                        'not_completed' => 'Not Completed',
                    ] as $value => $label)
                        <option value="{{ $value }}" @selected(old('activity_completed', $reportData?->activity_completed) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 d-none" id="incompletion_reason_wrap">
                <label class="form-label">Reason (if not fully completed)</label>
                <textarea name="incompletion_reason" rows="3" class="form-control border p-2">{{ old('incompletion_reason', $reportData?->incompletion_reason) }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-none mb-4 report-section">
    <div class="card-body">
        <h6 class="fw-semibold mb-3">3. Student Engagement</h6>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Engagement Level</label>
                <select name="engagement_level" class="form-select border p-2">
                    <option value="">Select</option>
                    <option value="very_engaged" @selected(old('engagement_level', $reportData?->engagement_level) === 'very_engaged')>Very Engaged</option>
                    <option value="moderately_engaged" @selected(old('engagement_level', $reportData?->engagement_level) === 'moderately_engaged')>Moderately Engaged</option>
                    <option value="low_engagement" @selected(old('engagement_level', $reportData?->engagement_level) === 'low_engagement')>Low Engagement</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Questions Participation</label>
                <select name="questions_participation" class="form-select border p-2">
                    <option value="">Select</option>
                    <option value="most_students_75pct" @selected(old('questions_participation', $reportData?->questions_participation) === 'most_students_75pct')>Most students (75%+)</option>
                    <option value="about_half_50_75pct" @selected(old('questions_participation', $reportData?->questions_participation) === 'about_half_50_75pct')>About half (50-75%)</option>
                    <option value="some_students_25_50pct" @selected(old('questions_participation', $reportData?->questions_participation) === 'some_students_25_50pct')>Some students (25-50%)</option>
                    <option value="very_few_25pct" @selected(old('questions_participation', $reportData?->questions_participation) === 'very_few_25pct')>Very few (&lt;25%)</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Girls Participation</label>
                <select name="girls_participation" class="form-select border p-2">
                    <option value="">Select</option>
                    <option value="very_active" @selected(old('girls_participation', $reportData?->girls_participation) === 'very_active')>Very Active</option>
                    <option value="moderately" @selected(old('girls_participation', $reportData?->girls_participation) === 'moderately')>Moderately</option>
                    <option value="not_much" @selected(old('girls_participation', $reportData?->girls_participation) === 'not_much')>Not Much</option>
                    <option value="no_girls_present" @selected(old('girls_participation', $reportData?->girls_participation) === 'no_girls_present')>No Girls Present</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-none mb-4 report-section">
    <div class="card-body">
        <h6 class="fw-semibold mb-3">4. Learning Outcomes</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Learning Understanding</label>
                <select name="learning_understanding" class="form-select border p-2">
                    <option value="">Select</option>
                    <option value="all_understood" @selected(old('learning_understanding', $reportData?->learning_understanding) === 'all_understood')>All Understood</option>
                    <option value="most_understood" @selected(old('learning_understanding', $reportData?->learning_understanding) === 'most_understood')>Most Understood</option>
                    <option value="some_understood" @selected(old('learning_understanding', $reportData?->learning_understanding) === 'some_understood')>Some Understood</option>
                    <option value="struggled" @selected(old('learning_understanding', $reportData?->learning_understanding) === 'struggled')>Struggled</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label d-block">Understanding Check Methods</label>
                @foreach([
                    'questions' => 'Questions',
                    'observed_work' => 'Observed Work',
                    'quiz_test' => 'Quiz/Test',
                    'presentations' => 'Presentations',
                    'group_discussions' => 'Group Discussions',
                    'other' => 'Other',
                ] as $value => $label)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="understanding_check_methods[]" value="{{ $value }}" id="understanding_{{ $value }}" @checked(in_array($value, $understandingMethods, true))>
                        <label class="form-check-label" for="understanding_{{ $value }}">{{ $label }}</label>
                    </div>
                @endforeach
                <input type="text" name="understanding_check_other" class="form-control border p-2 mt-2" placeholder="If other, specify" value="{{ old('understanding_check_other', $reportData?->understanding_check_other) }}">
            </div>
            <div class="col-12">
                <label class="form-label">What did students learn?</label>
                <textarea name="students_learned" rows="3" class="form-control border p-2">{{ old('students_learned', $reportData?->students_learned) }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-none mb-4 report-section">
    <div class="card-body">
        <h6 class="fw-semibold mb-3">5. Challenges and Resources</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label d-block">Challenge Types</label>
                @foreach([
                    'not_enough_materials' => 'Not enough materials',
                    'equipment_not_working' => 'Equipment not working',
                    'too_difficult' => 'Too difficult',
                    'too_easy' => 'Too easy',
                    'classroom_management' => 'Classroom management',
                    'not_enough_time' => 'Not enough time',
                    'internet_electricity' => 'Internet/electricity',
                    'other' => 'Other',
                ] as $value => $label)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="challenge_types[]" value="{{ $value }}" id="challenge_{{ $value }}" @checked(in_array($value, $challengeTypes, true))>
                        <label class="form-check-label" for="challenge_{{ $value }}">{{ $label }}</label>
                    </div>
                @endforeach
                <input type="text" name="challenge_other" class="form-control border p-2 mt-2" placeholder="If other, specify" value="{{ old('challenge_other', $reportData?->challenge_other) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Challenge Description</label>
                <textarea name="challenge_description" rows="4" class="form-control border p-2">{{ old('challenge_description', $reportData?->challenge_description) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Resources Used</label>
                <textarea name="resources_used" rows="3" class="form-control border p-2">{{ old('resources_used', $reportData?->resources_used) }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-none mb-4 report-section">
    <div class="card-body">
        <h6 class="fw-semibold mb-3">6. Reflection and Notes</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Session Satisfaction</label>
                <select name="session_satisfaction" class="form-select border p-2">
                    <option value="">Select</option>
                    <option value="very_satisfied" @selected(old('session_satisfaction', $reportData?->session_satisfaction) === 'very_satisfied')>Very Satisfied</option>
                    <option value="satisfied" @selected(old('session_satisfaction', $reportData?->session_satisfaction) === 'satisfied')>Satisfied</option>
                    <option value="neutral" @selected(old('session_satisfaction', $reportData?->session_satisfaction) === 'neutral')>Neutral</option>
                    <option value="not_satisfied" @selected(old('session_satisfaction', $reportData?->session_satisfaction) === 'not_satisfied')>Not Satisfied</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Session Highlight</label>
                <textarea name="session_highlight" rows="3" class="form-control border p-2">{{ old('session_highlight', $reportData?->session_highlight) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">What Worked Well</label>
                <textarea name="what_worked_well" rows="3" class="form-control border p-2">{{ old('what_worked_well', $reportData?->what_worked_well) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">What Would You Do Differently?</label>
                <textarea name="would_do_differently" rows="3" class="form-control border p-2">{{ old('would_do_differently', $reportData?->would_do_differently) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Additional Comments</label>
                <textarea name="additional_comments" rows="3" class="form-control border p-2">{{ old('additional_comments', $reportData?->additional_comments) }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-none mb-4 report-section">
    <div class="card-body">
        <h6 class="fw-semibold mb-3">7. Photo Evidence (Optional)</h6>

        @if($existingPhotos->isNotEmpty())
            <div class="row g-3 mb-3">
                @foreach($existingPhotos as $photo)
                    <div class="col-md-3 col-6">
                        <div class="border rounded p-2 h-100">
                            <img src="{{ asset('storage/' . $photo->file_path) }}" class="img-fluid rounded mb-2" alt="Session photo">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="delete_photos[]" value="{{ $photo->id }}" id="delete_photo_{{ $photo->id }}">
                                <label class="form-check-label small" for="delete_photo_{{ $photo->id }}">Delete</label>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <label class="form-label">Upload photos (max 5, 5MB each)</label>
        <input type="file" name="photos[]" class="form-control border p-2" accept="image/*" multiple>
        <small class="text-muted">You can upload up to 5 photos in total for this report.</small>
    </div>
</div>
