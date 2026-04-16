@php
    $reportData = $report ?? null;
    $existingPhotos = $reportData?->photos ?? collect();
    $challengeTypes = old('challenge_types', $reportData?->challenge_types ?? []);
@endphp

<div class="card border-0 shadow-none mb-4 report-section">
    <div class="card-body">
        <h6 class="fw-semibold mb-3">Quick Report</h6>
       
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Total Students Registered</label>
                <input type="number" min="0" name="total_students_registered" class="form-control border p-2" value="{{ old('total_students_registered', $reportData?->total_students_registered ?? $totalStudentsRegistered ?? 0) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Attendance</label>
                <input type="number" min="0" name="students_present" class="form-control border p-2" value="{{ old('students_present', $reportData?->attendance_count) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Girls Present</label>
                <input type="number" min="0" name="girls_present" class="form-control border p-2" value="{{ old('girls_present', $reportData?->girls_present) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">Girls Participation</label>
                <select name="girls_participation" class="form-select border p-2">
                    <option value="">Select</option>
                    <option value="very_active" @selected(old('girls_participation', $reportData?->girls_participation) === 'very_active')>Very Active</option>
                    <option value="moderately" @selected(old('girls_participation', $reportData?->girls_participation) === 'moderately')>Moderate</option>
                    <option value="not_much" @selected(old('girls_participation', $reportData?->girls_participation) === 'not_much')>Low</option>
                    <option value="no_girls_present" @selected(old('girls_participation', $reportData?->girls_participation) === 'no_girls_present')>No Girls Present</option>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Project Progress</label>
                <select name="activity_completed" class="form-select border p-2">
                    <option value="">Select</option>
                    <option value="fully_completed" @selected(old('activity_completed', $reportData?->activity_completed) === 'fully_completed')>Completed</option>
                    <option value="partially_completed" @selected(old('activity_completed', $reportData?->activity_completed) === 'partially_completed')>Partially Completed</option>
                    <option value="not_completed" @selected(old('activity_completed', $reportData?->activity_completed) === 'not_completed')>Not Completed</option>
                </select>
            </div>

            <div class="col-12">
                <label class="form-label">What did students learn?</label>
                <textarea name="students_learned" rows="3" class="form-control border p-2" placeholder="Briefly summarize the main learning from this session">{{ old('students_learned', $reportData?->students_learned) }}</textarea>
            </div>

            <div class="col-12">
                <label class="form-label">Challenges</label>
                <div class="row g-2">
                    @foreach([
                        'not_enough_materials' => 'Not enough materials',
                        'equipment_not_working' => 'Equipment issue',
                        'too_difficult' => 'Too difficult',
                        'too_easy' => 'Too easy',
                        'classroom_management' => 'Class management',
                        'not_enough_time' => 'Not enough time',
                        'internet_electricity' => 'Internet/electricity',
                        'other' => 'Other',
                    ] as $value => $label)
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="challenge_types[]" value="{{ $value }}" id="challenge_{{ $value }}" @checked(in_array($value, $challengeTypes, true))>
                                <label class="form-check-label" for="challenge_{{ $value }}">{{ $label }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
                <textarea name="challenge_description" rows="3" class="form-control border p-2 mt-3" placeholder="Briefly explain the challenge, if any">{{ old('challenge_description', $reportData?->challenge_description) }}</textarea>
          </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-none mb-4 report-section">
    <div class="card-body">
        <h6 class="fw-semibold mb-3">Photo Evidence (Optional)</h6>

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
