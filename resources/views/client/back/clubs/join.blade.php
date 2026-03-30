<x-app-layout>
    <div class="col-lg-9">

        <div class="dashboard-content-header">
            <h3 class="mb-1">Join {{ $club->name }}</h3>
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

                        <form method="POST" action="{{ route('client.clubs.join', $club) }}" id="joinClubForm" class="d-flex flex-column gap-3">
                            @csrf

                            @include('partials.teacher-club-join-school-fields', [
                                'schools' => $schools,
                                'regions' => $regions,
                                'existingSchool' => $existingSchool,
                            ])

                            <div class="d-flex mt-3">
                                <button type="submit" class="btn w-100 btn-primary btn-sm text-white justify-content-center">
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
                const schoolSearchInput = document.getElementById('school_search');
                const isNewSchoolCheckbox = document.getElementById('is_new_school');
                const newSchoolFields = document.getElementById('newSchoolFields');
                const schoolNameInput = document.getElementById('school_name');
                const form = document.getElementById('joinClubForm');
                let choicesInstance = null;

                // Only initialize if school selection fields exist (teacher doesn't have existing school)
                if (schoolSelect && isNewSchoolCheckbox) {
                    const schoolOptions = Array.from(schoolSelect.options)
                        .filter(option => option.value !== '')
                        .map(option => ({
                            value: option.value,
                            label: option.text,
                        }));

                    // Initialize Choices.js only when library is available.
                    if (typeof Choices !== 'undefined') {
                        choicesInstance = new Choices(schoolSelect, {
                            searchEnabled: true,
                            searchChoices: true,
                            itemSelectText: '',
                            placeholder: true,
                            placeholderValue: '-- Select a school --',
                            searchPlaceholderValue: 'Search schools...',
                        });
                    }

                    function applySchoolFilter(query) {
                        const searchText = (query || '').trim().toLowerCase();
                        const filteredOptions = schoolOptions.filter(option =>
                            option.label.toLowerCase().includes(searchText)
                        );

                        if (choicesInstance) {
                            choicesInstance.clearChoices();
                            choicesInstance.setChoices(filteredOptions, 'value', 'label', true);
                        } else {
                            const currentValue = schoolSelect.value;
                            schoolSelect.innerHTML = '<option value="">-- Select a school --</option>';

                            filteredOptions.forEach(option => {
                                const optionElement = document.createElement('option');
                                optionElement.value = option.value;
                                optionElement.textContent = option.label;
                                if (option.value === currentValue) {
                                    optionElement.selected = true;
                                }
                                schoolSelect.appendChild(optionElement);
                            });
                        }
                    }

                    function toggleNewSchoolFields() {
                        if (isNewSchoolCheckbox.checked) {
                            newSchoolFields.style.display = 'block';
                            if (choicesInstance) {
                                choicesInstance.disable();
                            }
                            if (schoolSearchInput) {
                                schoolSearchInput.setAttribute('disabled', 'disabled');
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
                            if (schoolSearchInput) {
                                schoolSearchInput.removeAttribute('disabled');
                            }
                            schoolSelect.setAttribute('required', 'required');
                            if (schoolNameInput) {
                                schoolNameInput.removeAttribute('required');
                            }
                        }
                    }

                    // Initialize on page load
                    toggleNewSchoolFields();

                    if (schoolSearchInput) {
                        schoolSearchInput.addEventListener('input', function () {
                            applySchoolFilter(this.value);
                        });
                    }

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

