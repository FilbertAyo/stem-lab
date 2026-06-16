<x-app-layout>
    <div class="col-12">
        @php($repCount = max(1, (int) ($competition->representative_count ?? 10)))
        @php($girlsInPool = $students->where('gender', 'female')->count())
        @php($poolCount = $students->count())
        @php($poolRatio = $poolCount > 0 ? round(($girlsInPool / $poolCount) * 100, 2) : 0)

        <div class="dashboard-content-header mb-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('client.competitions.index') }}">Competitions</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('client.competitions.show', $competition) }}">{{ $competition->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('client.competitions.register', $competition) }}">Participants</a></li>
                    <li class="breadcrumb-item active">Representatives</li>
                </ol>
            </nav>
            <h4 class="mb-1">Step 2: Select {{ $repCount }} representatives</h4>
            <p class="text-muted small mb-0">Choose exactly {{ $repCount }} students from the pool. At least 70% must be girls. Submit when ready for admin review.</p>
        </div>

        @include('elements.client-validation')

        <div class="row">
            <div class="col-lg-8">
                <div class="card border shadow-none">
                    <div class="card-header bg-light-soft">
                        <h5 class="mb-0">{{ $competition->name }}</h5>
                        <small class="text-muted">{{ $competition->club->name }}</small>
                    </div>
                    <div class="card-body">
                        <form id="representative-picker" action="{{ route('client.competitions.register.representatives.store', $competition) }}" method="POST" data-max-reps="{{ $repCount }}">
                            @csrf

                            <div class="table-responsive">
                                <table class="table table-hover table-light-soft">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;">Select</th>
                                            <th>Name</th>
                                            <th>Gender</th>
                                            <th>Class</th>
                                            <th>Role</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($students as $student)
                                            <tr>
                                                <td>
                                                    <input class="form-check-input student-checkbox" type="checkbox"
                                                           name="representative_student_ids[]" value="{{ $student->id }}"
                                                           data-gender="{{ strtolower($student->gender) }}"
                                                           @checked(in_array($student->id, old('representative_student_ids', [])))>
                                                </td>
                                                <td>{{ $student->name }}</td>
                                                <td>{{ ucfirst($student->gender) }}</td>
                                                <td>{{ $student->schoolClass->name ?? '—' }}</td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm" name="student_roles[{{ $student->id }}]" value="{{ old("student_roles.{$student->id}") }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <button type="submit" class="btn btn-sm btn-primary text-white">Submit registration for approval</button>
                                <a href="{{ route('client.competitions.register', $competition) }}" class="btn btn-sm btn-outline-secondary">Back to participants</a>
                                <a href="{{ route('client.competitions.show', $competition) }}" class="btn btn-sm btn-secondary text-white">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border shadow-none mb-3">
                    <div class="card-header bg-light-soft"><h6 class="mb-0">Eligibility &amp; selection</h6></div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Pool:</strong> {{ $poolCount }} students, {{ $poolRatio }}% girls</p>
                        <hr class="my-2">
                        <p class="mb-1"><strong>Selected reps:</strong> <span id="selectedCount">0</span> / {{ $repCount }}</p>
                        <p class="mb-0"><strong>Girls in reps:</strong> <span id="selectedGirlsRatio">0</span>% / 70%</p>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const form = document.getElementById('representative-picker');
                    const maxReps = form ? parseInt(form.dataset.maxReps, 10) || 10 : 10;
                    const checkboxes = Array.from(document.querySelectorAll('.student-checkbox'));
                    const selectedCountEl = document.getElementById('selectedCount');
                    const selectedGirlsRatioEl = document.getElementById('selectedGirlsRatio');

                    function updateSelection() {
                        const checked = checkboxes.filter((el) => el.checked);
                        const girls = checked.filter((el) => el.dataset.gender === 'female');
                        const count = checked.length;
                        const ratio = count > 0 ? ((girls.length / count) * 100).toFixed(2) : '0';
                        selectedCountEl.textContent = String(count);
                        selectedGirlsRatioEl.textContent = ratio;
                    }

                    checkboxes.forEach((checkbox) => {
                        checkbox.addEventListener('change', function () {
                            const selected = checkboxes.filter((el) => el.checked).length;
                            if (selected > maxReps) {
                                this.checked = false;
                                window.alert('Only ' + maxReps + ' representatives can be selected.');
                            }
                            updateSelection();
                        });
                    });

                    updateSelection();
                });
            </script>
        @endpush
    </div>
</x-app-layout>
