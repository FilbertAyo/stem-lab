<x-app-layout>
    <div class="col-lg-9">

        <div class="dashboard-content-header">
            <h2 class="title mb-1">Join {{ $club->name }}</h2>
        </div>

        <section class="row">
            <div class="col-12 col-lg-12 col-xl-12">
                <div class="card border-0 shadow-none">
                    <div class="card-body p-4">

                        <div class="mb-4">
                            <h5 class="fw-semibold mb-2">{{ $club->name }}</h5>
                            @if($club->description)
                                <p class="text-muted mb-0">{{ $club->description }}</p>
                            @endif
                        </div>

                            <div class="alert alert-info mb-4">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-info-circle me-2 fs-5 mt-1"></i>
                                    <div>
                                        <strong>Join Process</strong>
                                        <p class="mb-0 small">If you're the first teacher from your school to join this club, you'll be automatically approved. Otherwise, your request will need approval from your school's leader.</p>
                                    </div>
                                </div>
                            </div>

                        <hr class="my-4">

                        <form method="POST" action="{{ route('client.clubs.join', $club) }}" id="joinClubForm">
                            @csrf

                            @if($existingSchool)
                                {{-- Teacher already has a school --}}
                                <div class="alert alert-info mb-4">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-info-circle me-2 fs-5"></i>
                                        <div>
                                            <strong>Your School:</strong> {{ $existingSchool->name }}
                                            @if($existingSchool->district || $existingSchool->region)
                                                <span class="text-muted">
                                                    ({{ trim(($existingSchool->district ?? '') . ($existingSchool->district && $existingSchool->region ? ', ' : '') . ($existingSchool->region ?? '')) }})
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="mb-0 mt-2 small">You will join this club using your existing school registration.</p>
                                </div>
                                <input type="hidden" name="use_existing_school" value="1">
                            @else
                                {{-- Teacher needs to select or register a school --}}
                                <div class="mb-4">
                                    <label for="school_id" class="form-label">
                                        Select School <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('school_id') is-invalid @enderror"
                                            id="school_id"
                                            name="school_id"
                                            required>
                                        <option value="">-- Select a school --</option>
                                        @foreach($schools as $school)
                                            <option value="{{ $school->id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>
                                                {{ $school->name }}
                                                @if($school->district || $school->region)
                                                    ({{ trim(($school->district ?? '') . ($school->district && $school->region ? ', ' : '') . ($school->region ?? '')) }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('school_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">If your school is not in the list, check "Add new school" below.</small>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_new_school" name="is_new_school" value="1" {{ old('is_new_school') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_new_school">
                                            Add new school
                                        </label>
                                    </div>
                                </div>
                            @endif

                            <div id="newSchoolFields" style="display: none;">
                                <hr class="my-4">
                                <h6 class="mb-3">New School Information</h6>

                                <div class="mb-3">
                                    <label for="school_name" class="form-label">
                                        School Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           class="form-control @error('school_name') is-invalid @enderror"
                                           id="school_name"
                                           name="school_name"
                                           value="{{ old('school_name') }}">
                                    @error('school_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="school_address" class="form-label">School Address</label>
                                    <textarea class="form-control @error('school_address') is-invalid @enderror"
                                              id="school_address"
                                              name="school_address"
                                              rows="3">{{ old('school_address') }}</textarea>
                                    @error('school_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="school_phone" class="form-label">School Phone</label>
                                        <input type="text"
                                               class="form-control @error('school_phone') is-invalid @enderror"
                                               id="school_phone"
                                               name="school_phone"
                                               value="{{ old('school_phone') }}">
                                        @error('school_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="school_email" class="form-label">School Email</label>
                                        <input type="email"
                                               class="form-control @error('school_email') is-invalid @enderror"
                                               id="school_email"
                                               name="school_email"
                                               value="{{ old('school_email') }}">
                                        @error('school_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="school_district" class="form-label">District</label>
                                        <input type="text"
                                               class="form-control @error('school_district') is-invalid @enderror"
                                               id="school_district"
                                               name="school_district"
                                               value="{{ old('school_district') }}">
                                        @error('school_district')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="school_region" class="form-label">Region</label>
                                        <input type="text"
                                               class="form-control @error('school_region') is-invalid @enderror"
                                               id="school_region"
                                               name="school_region"
                                               value="{{ old('school_region') }}">
                                        @error('school_region')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <a href="{{ route('client.clubs.index') }}" class="btn bg-light-soft text-dark">
                                    <i class="bi bi-arrow-left me-1"></i>
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary text-white">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Join Club
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @push('styles')
        <link rel="stylesheet" href="{{ asset('client/assets/vendors/choices.js/choices.min.css') }}">
    @endpush

    @push('scripts')
        <script src="{{ asset('client/assets/vendors/choices.js/choices.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const schoolSelect = document.getElementById('school_id');
                const isNewSchoolCheckbox = document.getElementById('is_new_school');
                const newSchoolFields = document.getElementById('newSchoolFields');
                const schoolNameInput = document.getElementById('school_name');
                const form = document.getElementById('joinClubForm');
                let choicesInstance = null;

                // Only initialize if school selection fields exist (teacher doesn't have existing school)
                if (schoolSelect && isNewSchoolCheckbox) {
                    // Initialize Choices.js for searchable dropdown
                    choicesInstance = new Choices(schoolSelect, {
                        searchEnabled: true,
                        searchChoices: true,
                        itemSelectText: '',
                        placeholder: true,
                        placeholderValue: '-- Select a school --',
                        searchPlaceholderValue: 'Search schools...',
                    });

                    function toggleNewSchoolFields() {
                        if (isNewSchoolCheckbox.checked) {
                            newSchoolFields.style.display = 'block';
                            if (choicesInstance) {
                                choicesInstance.disable();
                            }
                            schoolSelect.removeAttribute('required');
                            if (schoolNameInput) {
                                schoolNameInput.setAttribute('required', 'required');
                            }
                        } else {
                            newSchoolFields.style.display = 'none';
                            if (choicesInstance) {
                                choicesInstance.enable();
                            }
                            schoolSelect.setAttribute('required', 'required');
                            if (schoolNameInput) {
                                schoolNameInput.removeAttribute('required');
                            }
                        }
                    }

                    // Initialize on page load
                    toggleNewSchoolFields();

                    // Toggle when checkbox changes
                    isNewSchoolCheckbox.addEventListener('change', toggleNewSchoolFields);

                    // Form validation
                    form.addEventListener('submit', function (e) {
                        if (isNewSchoolCheckbox.checked) {
                            // If adding new school, validate new school fields
                            if (schoolNameInput && !schoolNameInput.value.trim()) {
                                e.preventDefault();
                                alert('Please enter a school name.');
                                schoolNameInput.focus();
                                return false;
                            }
                        } else {
                            // If selecting existing school, validate school selection
                            const selectedValue = choicesInstance ? choicesInstance.getValue(true) : schoolSelect.value;
                            if (!selectedValue || selectedValue === '') {
                                e.preventDefault();
                                alert('Please select a school or check "Add new school" to register a new one.');
                                if (choicesInstance && choicesInstance.containerOuter) {
                                    choicesInstance.containerOuter.element.focus();
                                } else {
                                    schoolSelect.focus();
                                }
                                return false;
                            }
                        }
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>

