<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Create Project</h5>
                    <p class="text-muted mb-0">Upload a new project guide for clients</p>
                </div>
                <a href="{{ route('admin.projects.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left"></i> Back
                </a>
            </div>

           

            <form method="POST" action="{{ route('admin.projects.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card mb-3">
                            <div class="card-header bg-transparent border-bottom">
                                <h6 class="mb-0">Project details</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Project name</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                    @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Difficulty</label>
                                        <select name="difficulty" class="form-select" required>
                                            <option value="beginner" {{ old('difficulty', 'beginner') === 'beginner' ? 'selected' : '' }}>Beginner</option>
                                            <option value="intermediate" {{ old('difficulty') === 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                            <option value="advanced" {{ old('difficulty') === 'advanced' ? 'selected' : '' }}>Advanced</option>
                                        </select>
                                        @error('difficulty') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Time needed (minutes)</label>
                                        <input type="number" name="estimated_time" class="form-control" value="{{ old('estimated_time') }}" min="0" placeholder="e.g. 120">
                                        <small class="text-muted">e.g. 60 = 1 hour</small>
                                        @error('estimated_time') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Platforms</label>
                                        <div class="border rounded p-2" style="max-height: 120px; overflow-y: auto;">
                                            @forelse($platforms as $platform)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="platform_ids[]" value="{{ $platform->id }}" id="platform{{ $platform->id }}" {{ in_array($platform->id, old('platform_ids', [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label small" for="platform{{ $platform->id }}">{{ $platform->name }}</label>
                                                </div>
                                            @empty
                                                <p class="small text-muted mb-0">No platforms yet. <a href="{{ route('admin.platforms.create') }}" target="_blank">Add platforms</a> first.</p>
                                            @endforelse
                                        </div>
                                        @error('platform_ids') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">YouTube video URL (optional)</label>
                                        <input type="url" name="youtube_url" class="form-control" value="{{ old('youtube_url') }}" placeholder="https://youtube.com/watch?v=...">
                                        @error('youtube_url') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">External link (optional)</label>
                                        <input type="url" name="external_link" class="form-control" value="{{ old('external_link') }}" placeholder="https://...">
                                        <small class="text-muted">Tutorial, datasheet, etc.</small>
                                        @error('external_link') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Category</label>
                                        <select name="project_category_id" class="form-select">
                                            <option value="">— None —</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ (string)old('project_category_id') === (string)$category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('project_category_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Class <span class="text-danger">*</span></label>
                                        <select name="school_class_id" class="form-select" required>
                                            <option value="">Select class...</option>
                                            @foreach($classes as $class)
                                                <option value="{{ $class->id }}" {{ (string) old('school_class_id') === (string) $class->id ? 'selected' : '' }}>
                                                    {{ $class->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('school_class_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </div>


                                <div class="mb-3">
                                    <label class="form-label">Tags (comma-separated)</label>
                                    <input type="text" name="tags" class="form-control" value="{{ old('tags') }}" placeholder="e.g. iot, sensors, arduino">
                                    @error('tags') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Short description (optional)</label>
                                    <textarea name="description" rows="3" class="form-control" placeholder="What will the client build?">{{ old('description') }}</textarea>
                                    @error('description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Guide notes (optional)</label>
                                    <textarea id="guideNotesEditor" name="guide" rows="10" class="form-control" placeholder="Write guiding notes for clients.">{{ old('guide') }}</textarea>
                                    @error('guide') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Resources (PDFs, links, files) --}}
                        <div class="card mb-3">
                            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="ti ti-file me-1"></i> Resources (optional)</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addResourceRow">
                                    <i class="ti ti-plus"></i> Add resource
                                </button>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">Links, PDFs or files for students (circuit diagram, code download, etc.).</p>
                                <div id="resourcesContainer">
                                    <div class="resource-row border rounded p-3 mb-2">
                                        <div class="row g-2">
                                            <div class="col-12"><input type="text" name="resource_title[]" class="form-control" placeholder="Resource title"></div>
                                            <div class="col-md-4">
                                                <select name="resource_type[]" class="form-select form-select-sm">
                                                    <option value="link">Link</option>
                                                    <option value="pdf">PDF</option>
                                                    <option value="image">Image</option>
                                                    <option value="file">File</option>
                                                </select>
                                            </div>
                                            <div class="col-md-8">
                                                <input type="url" name="resource_url[]" class="form-control form-control-sm" placeholder="URL (or upload file below)">
                                            </div>
                                            <div class="col-12">
                                                <input type="file" name="resource_file[]" class="form-control form-control-sm" placeholder="Or upload file">
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger mt-2 removeResourceRow"><i class="ti ti-trash"></i> Remove</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Equipment used</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addEquipmentRow">
                                    <i class="ti ti-plus"></i> Add row
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle mb-0" id="equipmentTable">
                                        <thead>
                                            <tr>
                                                <th style="width: 60%;">Item name</th>
                                                <th style="width: 25%;">Quantity</th>
                                                <th style="width: 60px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><input type="text" name="equipment_name[]" class="form-control" placeholder="e.g. Arduino UNO"></td>
                                                <td><input type="text" name="equipment_quantity[]" class="form-control" placeholder="e.g. 1"></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-outline-danger removeEquipmentRow" title="Remove">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <small class="text-muted d-block mt-2">Tip: add one item per row. Quantity can be “1 pack”, “2”, etc.</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card mb-3">
                            <div class="card-header bg-transparent border-bottom">
                                <h6 class="mb-0">Images</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Cover image (optional)</label>
                                    <input type="file" name="cover_image" class="form-control" accept="image/*">
                                    <small class="text-muted">Recommended: landscape image. Max 5MB.</small>
                                    @error('cover_image') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Circuit image (optional)</label>
                                    <input type="file" name="circuit_image" class="form-control" accept="image/*">
                                    <small class="text-muted">Upload wiring / schematic image. Max 5MB.</small>
                                    @error('circuit_image') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header bg-transparent border-bottom">
                                <h6 class="mb-0">Visibility</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-check form-switch mb-2">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="isActive" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isActive">Active</label>
                                </div>
                                <small class="text-muted">Active projects are visible to clients. Inactive projects are hidden.</small>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" type="submit">
                                <i class="ti ti-device-floppy"></i> Create project
                            </button>
                            <a class="btn btn-light" href="{{ route('admin.projects.index') }}">Cancel</a>
                        </div>
                    </div>
                </div>
            </form>


        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('admin/js/plugins/tinymce/tinymce.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const tableBody = document.querySelector('#equipmentTable tbody');
                const addBtn = document.getElementById('addEquipmentRow');

                function bindRemoveButtons() {
                    document.querySelectorAll('.removeEquipmentRow').forEach(btn => {
                        btn.onclick = function () {
                            const row = this.closest('tr');
                            if (!row) return;
                            if (tableBody.querySelectorAll('tr').length <= 1) {
                                // keep one empty row
                                row.querySelectorAll('input').forEach(i => i.value = '');
                                return;
                            }
                            row.remove();
                        };
                    });
                }

                addBtn?.addEventListener('click', function () {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td><input type="text" name="equipment_name[]" class="form-control" placeholder="e.g. Breadboard"></td>
                        <td><input type="text" name="equipment_quantity[]" class="form-control" placeholder="e.g. 1"></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger removeEquipmentRow" title="Remove">
                                <i class="ti ti-trash"></i>
                            </button>
                        </td>
                    `;
                    tableBody.appendChild(tr);
                    bindRemoveButtons();
                });

                bindRemoveButtons();

                // Resources
                document.getElementById('addResourceRow')?.addEventListener('click', function () {
                    const div = document.createElement('div');
                    div.className = 'resource-row border rounded p-3 mb-2';
                    div.innerHTML = `
                        <div class="row g-2">
                            <div class="col-12"><input type="text" name="resource_title[]" class="form-control" placeholder="Resource title"></div>
                            <div class="col-md-4"><select name="resource_type[]" class="form-select form-select-sm"><option value="link">Link</option><option value="pdf">PDF</option><option value="image">Image</option><option value="file">File</option></select></div>
                            <div class="col-md-8"><input type="url" name="resource_url[]" class="form-control form-control-sm" placeholder="URL"></div>
                            <div class="col-12"><input type="file" name="resource_file[]" class="form-control form-control-sm"></div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger mt-2 removeResourceRow"><i class="ti ti-trash"></i> Remove</button>
                    `;
                    document.getElementById('resourcesContainer').appendChild(div);
                    div.querySelector('.removeResourceRow').onclick = function () { if (document.querySelectorAll('.resource-row').length > 1) div.remove(); };
                });
                document.querySelectorAll('.removeResourceRow').forEach(btn => {
                    btn.onclick = function () {
                        if (document.querySelectorAll('.resource-row').length <= 1) return;
                        this.closest('.resource-row')?.remove();
                    };
                });

                if (window.tinymce && document.getElementById('guideNotesEditor')) {
                    tinymce.init({
                        selector: '#guideNotesEditor',
                        height: 320,
                        menubar: false,
                        plugins: 'lists link table code fullscreen autoresize',
                        toolbar: 'undo redo | styles | bold italic underline | bullist numlist | link table | code fullscreen',
                    });
                }

            });
        </script>
    @endpush
</x-admin-layout>

