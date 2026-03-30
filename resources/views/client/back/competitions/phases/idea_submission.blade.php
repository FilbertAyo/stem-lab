@php
    $repCount = max(1, (int) ($competition->representative_count ?? 10));
    $selectedRepIds = old(
        'representative_student_ids',
        $registration?->representativeStudents->pluck('student_id')->all() ?? []);
@endphp

<div id="idea-submission" class="row">
    <div class="col-12">
        <div class="card border-0 shadow-none mb-4">
            <div class="card-header bg-transparent d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h6 class="mb-0">Idea submission</h6>
                    <p class="text-muted small mb-0 mt-1">
                        Submit one idea for your school. Select exactly <strong>{{ $repCount }}</strong> student representatives from your pool;
                        at least <strong>70%</strong> must be girls.
                    </p>
                </div>
            </div>
            <div class="card-body">
                @if(!$teacherClub)
                    <div class="alert alert-warning mb-0">You must be an approved teacher in this club to submit an idea.</div>
                @elseif(!$registration || $registration->status === 'draft')
                    <div class="alert alert-info mb-0">
                        Complete and submit your participant registration for admin approval before submitting an idea.
                        @if($registration && $registration->status === 'draft')<a href="{{ route('client.competitions.register', $competition) }}" class="alert-link">Continue registration</a>@endif
                    </div>
                @elseif($registration->status === 'pending')
                    <div class="alert alert-warning mb-0">Your registration is pending approval. You can submit an idea once it is approved.</div>
                @elseif($registration->status === 'rejected')
                    <div class="alert alert-danger mb-0">Your registration was not approved. Contact the organisers if you need help.</div>
                @elseif($poolStudents->isEmpty())
                    <div class="alert alert-warning mb-0">Add students to your class pool (registration) before you can select representatives.</div>
                @else
                    @if($idea && $idea->status === 'submitted')
                        <div class="alert alert-info mb-3">Your idea is submitted. You can update it while this phase stays open unless the organisers lock submissions.</div>
                    @endif

                    <form method="POST" action="{{ route('client.competitions.idea.store', $competition) }}" id="idea-submission-form" data-max-reps="{{ $repCount }}">
                        @csrf
                        <input type="hidden" name="sync_representatives" value="1">

                        <h6 class="text-uppercase text-muted small mb-3">Idea details</h6>

                        <div class="mb-3">
                            <label class="form-label">Problem title <span class="text-danger">*</span></label>
                            <input type="text" name="problem_title" class="form-control @error('problem_title') is-invalid @enderror border p-2" value="{{ old('problem_title', $idea?->problem_title) }}" required maxlength="255">
                            @error('problem_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Problem statement <span class="text-danger">*</span></label>
                            <textarea name="problem_statement" class="form-control @error('problem_statement') is-invalid @enderror border p-2" rows="4" required>{{ old('problem_statement', $idea?->problem_statement) }}</textarea>
                            @error('problem_statement')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Proposed solution <span class="text-danger">*</span></label>
                            <textarea name="proposed_solution" class="form-control @error('proposed_solution') is-invalid @enderror border p-2" rows="4" required>{{ old('proposed_solution', $idea?->proposed_solution) }}</textarea>
                            @error('proposed_solution')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">STEM discipline <span class="text-danger">*</span></label>
                                <input type="text" name="stem_category" class="form-control @error('stem_category') is-invalid @enderror border p-2" value="{{ old('stem_category', $idea?->stem_category) }}" required maxlength="120" list="stem-category-suggestions">
                                <datalist id="stem-category-suggestions">
                                    <option value="Science"></option>
                                    <option value="Technology"></option>
                                    <option value="Engineering"></option>
                                    <option value="Mathematics"></option>
                                    <option value="Integrated STEM"></option>
                                    <option value="Environmental Science"></option>
                                </datalist>
                                @error('stem_category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Community region</label>
                                <select name="community_region_id" class="form-select @error('community_region_id') is-invalid @enderror border p-2">
                                    <option value="">Select region</option>
                                    @foreach($regions as $region)
                                        <option value="{{ $region->id }}" @selected((string) old('community_region_id', $idea?->community_region_id) === (string) $region->id)>{{ $region->name }}</option>
                                    @endforeach
                                </select>
                                @error('community_region_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Community / location</label>
                            <input type="text" name="community_location" class="form-control @error('community_location') is-invalid @enderror border p-2" value="{{ old('community_location', $idea?->community_location) }}" maxlength="255">
                            @error('community_location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Supporting notes</label>
                            <textarea name="supporting_notes" class="form-control @error('supporting_notes') is-invalid @enderror border p-2" rows="3">{{ old('supporting_notes', $idea?->supporting_notes) }}</textarea>
                            @error('supporting_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <hr class="my-4 opacity-25">

                        <h6 class="text-uppercase text-muted small mb-2">Student representatives</h6>
                        <p class="small text-muted mb-3">
                            Choose <strong>{{ $repCount }}</strong> students who will represent your school for this idea. At least <strong>70%</strong> must be girls.
                            Optional role labels help judges (e.g. &ldquo;Team lead&rdquo;).
                        </p>

                        <div class="row">
                            <div class="col-lg-8">
                                <div class="table-responsive border mb-3">
                                    <table class="table table-hover table-bordered align-middle mb-0 small">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 48px;">Pick</th>
                                                <th>Name</th>
                                                <th>Gender</th>
                                                <th>Class</th>
                                                <th>Role (optional)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($poolStudents as $student)
                                                <tr>
                                                    <td>
                                                        <input class="form-check-input idea-rep-checkbox" type="checkbox"
                                                               name="representative_student_ids[]" value="{{ $student->id }}"
                                                               data-gender="{{ strtolower((string) $student->gender) }}"
                                                               @checked(in_array($student->id, $selectedRepIds, true))>
                                                    </td>
                                                    <td>{{ $student->name }}</td>
                                                    <td>{{ ucfirst((string) $student->gender) }}</td>
                                                    <td>{{ $student->schoolClass->name ?? '—' }}</td>
                                                    <td>
                                                        <input type="text" class="border p-2" name="student_roles[{{ $student->id }}]" value="{{ old("student_roles.{$student->id}", $registration->representativeStudents->firstWhere('student_id', $student->id)?->role) }}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card border bg-light-soft mb-3">
                                    <div class="card-body py-3">
                                        <p class="mb-1 small"><strong>Selected</strong> <span id="ideaSelectedCount">0</span> / {{ $repCount }}</p>
                                        <p class="mb-0 small"><strong>Girls share</strong> <span id="ideaSelectedGirlsRatio">0</span>% (min 70%)</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <button type="submit" class="btn btn-dark btn-sm" name="submit_final" value="0">Save draft</button>
                            <button type="submit" class="btn btn-primary text-white btn-sm" name="submit_final" value="1">Submit idea &amp; representatives</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
    @if($teacherClub && $registration && $registration->status === 'approved' && $poolStudents->isNotEmpty())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('idea-submission-form');
                if (!form) return;
                const maxReps = parseInt(form.dataset.maxReps, 10) || 1;
                const checkboxes = Array.from(form.querySelectorAll('.idea-rep-checkbox'));
                const selectedCountEl = document.getElementById('ideaSelectedCount');
                const selectedGirlsRatioEl = document.getElementById('ideaSelectedGirlsRatio');

                function updateIdeaRepSelection() {
                    const checked = checkboxes.filter(function (el) { return el.checked; });
                    const girls = checked.filter(function (el) { return el.dataset.gender === 'female'; });
                    const count = checked.length;
                    const ratio = count > 0 ? ((girls.length / count) * 100).toFixed(2) : '0';
                    selectedCountEl.textContent = String(count);
                    selectedGirlsRatioEl.textContent = ratio;
                }

                checkboxes.forEach(function (checkbox) {
                    checkbox.addEventListener('change', function () {
                        const selected = checkboxes.filter(function (el) { return el.checked; }).length;
                        if (selected > maxReps) {
                            checkbox.checked = false;
                            window.alert('You can select at most ' + maxReps + ' representatives.');
                        }
                        updateIdeaRepSelection();
                    });
                });

                updateIdeaRepSelection();
            });
        </script>
    @endif
@endpush
