<x-admin-layout>
        <div class="row">
            <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Schools</h5>
                    <p class="text-muted mb-0">Manage Schools</p>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSchoolModal">
                    <i class="ti ti-plus"></i> Add School
                </button>
            </div>

            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Please fix the errors below and try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card tbl-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>School Name</th>
                                    <th>Address</th>
                                    <th>Contact</th>
                                    <th>District</th>
                                    <th>Region</th>
                                    <th>Status</th>
                                    <th>Last Updated</th>
                                    <th class="text-center" style="width: 180px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($schools as $school)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="fw-semibold">{{ $school->name }}</td>
                                        <td>{{ $school->address ? \Illuminate\Support\Str::limit($school->address, 50) : '—' }}</td>
                                        <td>
                                            @if($school->phone || $school->email)
                                                <div class="small">
                                                    @if($school->phone)
                                                        <div><i class="ti ti-phone"></i> {{ $school->phone }}</div>
                                                    @endif
                                                    @if($school->email)
                                                        <div><i class="ti ti-mail"></i> {{ $school->email }}</div>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>{{ $school->district ?? '—' }}</td>
                                        <td>{{ $school->region ?? '—' }}</td>
                                        <td>
                                            <span class="badge {{ $school->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $school->is_active ? 'Active' : 'Disabled' }}
                                            </span>
                                        </td>
                                        <td>{{ $school->updated_at?->format('d M Y, H:i') }}</td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a
                                                    href="{{ route('admin.schools.show', $school) }}"
                                                    class="btn btn-sm btn-outline-info"
                                                    title="View Details"
                                                >
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                <button
                                                    class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editSchoolModal"
                                                    data-action="{{ route('admin.schools.update', $school) }}"
                                                    data-name="{{ $school->name }}"
                                                    data-address="{{ $school->address }}"
                                                    data-phone="{{ $school->phone }}"
                                                    data-email="{{ $school->email }}"
                                                    data-district="{{ $school->district }}"
                                                    data-region="{{ $school->region }}"
                                                    data-active="{{ $school->is_active ? '1' : '0' }}"
                                                    title="Edit"
                                                >
                                                    <i class="ti ti-edit"></i>
                                                </button>
                                                <button
                                                    class="btn btn-sm {{ $school->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#schoolStatusModal"
                                                    data-action="{{ route('admin.schools.toggle-status', $school) }}"
                                                    data-name="{{ $school->name }}"
                                                    data-status="{{ $school->is_active ? 'disable' : 'enable' }}"
                                                    title="{{ $school->is_active ? 'Disable' : 'Enable' }}"
                                                >
                                                    <i class="ti {{ $school->is_active ? 'ti-ban' : 'ti-check' }}"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            No schools found. Use the "Add School" button to create the first one.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createSchoolModal" tabindex="-1" aria-labelledby="createSchoolModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createSchoolModalLabel">Add School</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.schools.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="schoolName" class="form-label">School Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="schoolName" class="form-control" value="{{ old('name') }}" placeholder="e.g. ABC High School" required>
                                @error('name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="schoolEmail" class="form-label">Email</label>
                                <input type="email" name="email" id="schoolEmail" class="form-control" value="{{ old('email') }}" placeholder="school@example.com">
                                @error('email')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="schoolPhone" class="form-label">Phone</label>
                                <input type="text" name="phone" id="schoolPhone" class="form-control" value="{{ old('phone') }}" placeholder="+255 XXX XXX XXX">
                                @error('phone')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="schoolDistrict" class="form-label">District</label>
                                <input type="text" name="district" id="schoolDistrict" class="form-control" value="{{ old('district') }}" placeholder="District name">
                                @error('district')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="schoolRegion" class="form-label">Region</label>
                                <input type="text" name="region" id="schoolRegion" class="form-control" value="{{ old('region') }}" placeholder="Region name">
                                @error('region')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="schoolActiveToggle" class="form-label d-block">Status</label>
                                <div class="form-check form-switch mt-2">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="schoolActiveToggle" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="schoolActiveToggle">Active</label>
                                </div>
                                @error('is_active')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="schoolAddress" class="form-label">Address</label>
                            <textarea name="address" id="schoolAddress" rows="2" class="form-control" placeholder="Full address">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save School</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editSchoolModal" tabindex="-1" aria-labelledby="editSchoolModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSchoolModalLabel">Edit School</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="editSchoolForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editSchoolName" class="form-label">School Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="editSchoolName" class="form-control" required>
                                @error('name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editSchoolEmail" class="form-label">Email</label>
                                <input type="email" name="email" id="editSchoolEmail" class="form-control">
                                @error('email')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editSchoolPhone" class="form-label">Phone</label>
                                <input type="text" name="phone" id="editSchoolPhone" class="form-control">
                                @error('phone')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editSchoolDistrict" class="form-label">District</label>
                                <input type="text" name="district" id="editSchoolDistrict" class="form-control">
                                @error('district')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editSchoolRegion" class="form-label">Region</label>
                                <input type="text" name="region" id="editSchoolRegion" class="form-control">
                                @error('region')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editSchoolActiveToggle" class="form-label d-block">Status</label>
                                <div class="form-check form-switch mt-2">
                                    <input type="hidden" name="is_active" id="editSchoolIsActive" value="1">
                                    <input class="form-check-input" type="checkbox" role="switch" id="editSchoolActiveToggle">
                                    <label class="form-check-label" for="editSchoolActiveToggle">Active</label>
                                </div>
                                @error('is_active')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editSchoolAddress" class="form-label">Address</label>
                            <textarea name="address" id="editSchoolAddress" rows="2" class="form-control"></textarea>
                            @error('address')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update School</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Disable/Enable Modal -->
    <div class="modal fade" id="schoolStatusModal" tabindex="-1" aria-labelledby="schoolStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="schoolStatusModalLabel">Update Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="schoolStatusForm">
                    @csrf
                    <div class="modal-body">
                        <p class="mb-0" id="schoolStatusModalMessage"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning" id="schoolStatusModalSubmit">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const editModal = document.getElementById('editSchoolModal');
                const editForm = document.getElementById('editSchoolForm');
                const editName = document.getElementById('editSchoolName');
                const editAddress = document.getElementById('editSchoolAddress');
                const editPhone = document.getElementById('editSchoolPhone');
                const editEmail = document.getElementById('editSchoolEmail');
                const editDistrict = document.getElementById('editSchoolDistrict');
                const editRegion = document.getElementById('editSchoolRegion');
                const editToggle = document.getElementById('editSchoolActiveToggle');
                const editStatusInput = document.getElementById('editSchoolIsActive');

                editModal?.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const action = button.getAttribute('data-action');
                    const name = button.getAttribute('data-name');
                    const address = button.getAttribute('data-address') || '';
                    const phone = button.getAttribute('data-phone') || '';
                    const email = button.getAttribute('data-email') || '';
                    const district = button.getAttribute('data-district') || '';
                    const region = button.getAttribute('data-region') || '';
                    const isActive = button.getAttribute('data-active') === '1';

                    editForm.setAttribute('action', action);
                    editName.value = name;
                    editAddress.value = address;
                    editPhone.value = phone;
                    editEmail.value = email;
                    editDistrict.value = district;
                    editRegion.value = region;
                    editToggle.checked = isActive;
                    editStatusInput.value = isActive ? '1' : '0';
                });

                editToggle?.addEventListener('change', function () {
                    editStatusInput.value = this.checked ? '1' : '0';
                });

                const statusModal = document.getElementById('schoolStatusModal');
                const statusForm = document.getElementById('schoolStatusForm');
                const statusMessage = document.getElementById('schoolStatusModalMessage');
                const statusSubmit = document.getElementById('schoolStatusModalSubmit');

                statusModal?.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const action = button.getAttribute('data-action');
                    const name = button.getAttribute('data-name');
                    const status = button.getAttribute('data-status');

                    statusForm.setAttribute('action', action);
                    if (status === 'disable') {
                        statusMessage.textContent = `Disable ${name}? This school will no longer be available until re-enabled.`;
                        statusSubmit.classList.remove('btn-success');
                        statusSubmit.classList.add('btn-warning');
                        statusSubmit.textContent = 'Disable';
                    } else {
                        statusMessage.textContent = `Enable ${name}? It will become available immediately.`;
                        statusSubmit.classList.remove('btn-warning');
                        statusSubmit.classList.add('btn-success');
                        statusSubmit.textContent = 'Enable';
                    }
                });
            });
        </script>
    @endpush
</x-admin-layout>
