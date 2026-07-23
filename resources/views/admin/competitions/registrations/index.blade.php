<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Registrations — {{ $competition->name }}</h5>
              </div>
                <div class="d-flex gap-2">
                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#enrollSchoolModal"
                    >
                        <i class="ti ti-school"></i> Enroll School
                    </button>
                    <a
                        href="{{ route('admin.competitions.registrations.export', [$competition] + request()->only(['search', 'status', 'region_id', 'district_id'])) }}"
                        class="btn btn-sm btn-danger"
                    >
                        <i class="ti ti-download"></i> Download PDF
                    </a>
                    <a href="{{ route('admin.competitions.show', $competition) }}" class="btn btn-sm btn-outline-secondary"><i class="ti ti-arrow-left"></i> Back</a>
                </div>
            </div>

            

            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.competitions.registrations.index', $competition) }}" class="row g-2 align-items-end">
                        <div class="col-md-4 col-lg-3">
                            <label class="form-label small mb-1" for="search">Search</label>
                            <input type="search" name="search" id="search" value="{{ request('search') }}" class="form-control" placeholder="School, teacher, email, phone…">
                        </div>
                        <div class="col-md-2 col-lg-2">
                            <label class="form-label small mb-1" for="status">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">All</option>
                                <option value="draft" @selected(request('status') === 'draft')>Incomplete</option>
                                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                                <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                                <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                                <option value="ineligible" @selected(request('status') === 'ineligible')>Ineligible</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-lg-2">
                            <label class="form-label small mb-1" for="filter_region_id">Region</label>
                            <select name="region_id" id="filter_region_id" class="form-select">
                                <option value="">All regions</option>
                                @foreach($regions as $region)
                                    <option value="{{ $region->id }}" @selected((string) request('region_id') === (string) $region->id)>
                                        {{ $region->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 col-lg-2">
                            <label class="form-label small mb-1" for="filter_district_id">District</label>
                            <select name="district_id" id="filter_district_id" class="form-select">
                                <option value="">All districts</option>
                                @foreach($districts as $district)
                                    <option value="{{ $district->id }}" @selected((string) request('district_id') === (string) $district->id)>
                                        {{ $district->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-lg-3 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary">Apply</button>
                            <a href="{{ route('admin.competitions.registrations.index', $competition) }}" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>School</th>
                                    <th>Teacher</th>
                                    <th>Club code</th>
                                    <th>Eligibility snapshot</th>
                                    <th>Representatives</th>
                                    <th>Club joined</th>
                                    <th>Reminders</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($registrations as $registration)
                                    <tr>
                                        <td>
                                            {{ $loop->iteration }}
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $registration->resolvedSchoolName() }}</div>
                                            <small class="text-muted">
                                                {{ $registration->region?->name ?? $registration->school?->regionModel?->name ?? 'No region' }}
                                                @if($registration->districtName())
                                                    · {{ $registration->districtName() }}
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            <div>{{ $registration->resolvedTeacherName() }}</div>
                                            <small class="text-muted">{{ $registration->resolvedTeacherEmail() }}</small>
                                        </td>
                                        <td>
                                            @if($registration->teacherClub?->club_code)
                                                <code>{{ $registration->teacherClub->club_code }}</code>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $poolCount = (int) ($registration->student_count_snapshot ?? 0);
                                                $poolRatio = $registration->girls_ratio_snapshot;
                                                $poolMet = (bool) $registration->eligibility_met;
                                            @endphp
                                            @if($poolCount > 0)
                                                <div>{{ $poolCount }} students</div>
                                                <small class="text-muted">{{ $poolRatio }}% girls</small>
                                                <div class="small {{ $poolMet ? 'text-success' : 'text-danger' }}">
                                                    {{ $poolMet ? 'Pool met' : 'Pool not met' }}
                                                </div>
                                            @elseif($registration->status === 'draft')
                                                <div class="text-muted">0 students</div>
                                                <small class="text-muted">Awaiting CSV upload</small>
                                            @else
                                                <div>0 students</div>
                                                <small class="text-muted">{{ $poolRatio ?? 0 }}% girls</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $registration->representativeStudents->count() }}/{{ max(1, (int) ($competition->representative_count ?? 10)) }}</span>
                                        </td>
                                        <td>
                                            @if($registration->teacherClub?->created_at)
                                                <small>{{ $registration->teacherClub->created_at->timezone(config('app.timezone'))->format('j M Y') }}</small>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $maxReminders = \App\Models\CompetitionRegistration::maxEligibilityReminders();
                                                $reminderCount = (int) $registration->eligibility_reminder_count;
                                            @endphp
                                            @if($registration->status === 'draft')
                                                <span class="badge {{ $reminderCount >= $maxReminders ? 'bg-secondary' : ($reminderCount > 0 ? 'bg-warning text-dark' : 'bg-light text-dark') }}">
                                                    {{ $reminderCount }}/{{ $maxReminders }}
                                                </span>
                                                @if($registration->last_eligibility_reminder_sent_at)
                                                    <div class="small text-muted">Last {{ $registration->last_eligibility_reminder_sent_at->timezone(config('app.timezone'))->format('j M') }}</div>
                                                @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $badge = match ($registration->status) {
                                                    'approved' => 'success',
                                                    'rejected' => 'danger',
                                                    'ineligible' => 'dark',
                                                    'draft' => 'secondary',
                                                    default => 'warning',
                                                };
                                                $statusLabel = $registration->status === 'draft' ? 'Incomplete' : ucfirst($registration->status);
                                            @endphp
                                            <span class="badge bg-{{ $badge }}">{{ $statusLabel }}</span>
                                        </td>
                                        <td class="text-end text-nowrap">
                                            <a href="{{ route('admin.competitions.registrations.show', [$competition, $registration]) }}" class="btn btn-sm btn-outline-primary">View</a>
                                         
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">No registrations match your filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @foreach($registrations as $registration)
                        @if($registration->status === 'pending')
                            <div class="modal fade" id="rejectModal{{ $registration->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Reject registration</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="POST" action="{{ route('admin.competitions.registrations.reject', [$competition, $registration]) }}">
                                            @csrf
                                            <div class="modal-body">
                                                <p class="small text-muted mb-2">{{ $registration->resolvedSchoolName() }}</p>
                                                <label class="form-label" for="rejection_reason_{{ $registration->id }}">Reason</label>
                                                <textarea class="form-control" name="rejection_reason" id="rejection_reason_{{ $registration->id }}" rows="4" required></textarea>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Reject</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach

                    <div class="mt-3">{{ $registrations->links('pagination::bootstrap-5') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="enrollSchoolModal" tabindex="-1" aria-labelledby="enrollSchoolModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="enrollSchoolModalLabel">Enroll school</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.competitions.registrations.enroll', $competition) }}">
                    @csrf
                    <div class="modal-body">
                        <p class="small text-muted mb-3">
                            Create an incomplete competition registration for a school that already has an approved teacher in this competition’s club.
                        </p>
                        <div class="mb-0">
                            <label class="form-label" for="enroll_school_id">School</label>
                            <select
                                name="school_id"
                                id="enroll_school_id"
                                class="form-select @error('school_id') is-invalid @enderror"
                                required
                                @disabled($enrollableSchools->isEmpty())
                            >
                                <option value="">Select a school…</option>
                                @foreach($enrollableSchools as $school)
                                    <option value="{{ $school->id }}" @selected((string) old('school_id') === (string) $school->id)>
                                        {{ $school->name }}
                                        @if($school->regionModel?->name || $school->districtName())
                                            — {{ collect([$school->regionModel?->name, $school->districtName()])->filter()->implode(' · ') }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('school_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @if($enrollableSchools->isEmpty())
                                <div class="form-text text-warning mt-2">
                                    No eligible schools left. Schools need an approved club teacher and must not already be registered.
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" @disabled($enrollableSchools->isEmpty())>
                            Enroll school
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($errors->has('school_id') || (session('error') && old('school_id') !== null))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var modalEl = document.getElementById('enrollSchoolModal');
                if (modalEl && window.bootstrap) {
                    window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
                }
            });
        </script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var districtsByRegion = @json($districtsByRegion ?? []);
            var regionSelect = document.getElementById('filter_region_id');
            var districtSelect = document.getElementById('filter_district_id');

            if (!regionSelect || !districtSelect) {
                return;
            }

            function populateDistricts(regionId, selectedDistrictId) {
                var districts = regionId
                    ? (districtsByRegion[regionId] || [])
                    : Object.values(districtsByRegion).flat();

                districtSelect.innerHTML = '';

                var allOption = document.createElement('option');
                allOption.value = '';
                allOption.textContent = 'All districts';
                districtSelect.appendChild(allOption);

                districts.forEach(function (district) {
                    var option = document.createElement('option');
                    option.value = district.id;
                    option.textContent = district.name;
                    if (selectedDistrictId && String(selectedDistrictId) === String(district.id)) {
                        option.selected = true;
                    }
                    districtSelect.appendChild(option);
                });
            }

            regionSelect.addEventListener('change', function () {
                populateDistricts(regionSelect.value, '');
            });
        });
    </script>
</x-admin-layout>
