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

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Please fix the errors below and try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

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
                                        <label class="form-label">Or create new category (optional)</label>
                                        <input type="text" name="new_category" class="form-control" value="{{ old('new_category') }}" placeholder="e.g. Environment Monitoring">
                                        @error('new_category') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                @if($categories->count() > 0)
                                    <div class="mb-3">
                                        <label class="form-label d-block">Delete category</label>
                                        <div class="d-flex flex-wrap gap-2" id="deleteCategoryButtons">
                                            @foreach($categories as $category)
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-danger delete-category-btn"
                                                    data-url="{{ route('admin.project-categories.destroy', $category) }}"
                                                    data-name="{{ $category->name }}">
                                                    <i class="ti ti-trash"></i> {{ $category->name }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif


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
                                    <label class="form-label">Step-by-step guide (optional)</label>
                                    <textarea name="guide" rows="10" class="form-control" placeholder="Write the full guide here (Markdown or plain text).">{{ old('guide') }}</textarea>
                                    @error('guide') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Embedded code (optional)</label>
                                    <textarea name="embedded_code" rows="6" class="form-control font-monospace small" placeholder="Quick single code block if you don’t use “Code blocks” below.">{{ old('embedded_code') }}</textarea>
                                    @error('embedded_code') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Step-by-step instructions (optional) --}}
                        <div class="card mb-3">
                            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="ti ti-list-numbers me-1"></i> Steps (optional)</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addStepRow">
                                    <i class="ti ti-plus"></i> Add step
                                </button>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">Add numbered steps with title, description, optional image and code snippet. Leave empty if you only use the guide text above.</p>
                                <div id="stepsContainer">
                                    <div class="step-row border rounded p-3 mb-2 position-relative">
                                        <span class="badge bg-secondary position-absolute top-0 end-0">1</span>
                                        <div class="row g-2">
                                            <div class="col-12">
                                                <input type="text" name="step_title[]" class="form-control" placeholder="Step title (e.g. Connect the sensor)">
                                            </div>
                                            <div class="col-12">
                                                <textarea name="step_description[]" class="form-control small" rows="2" placeholder="What to do in this step"></textarea>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small mb-0">Image (optional)</label>
                                                <input type="file" name="step_image[]" class="form-control form-control-sm" accept="image/*">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label small mb-0">Code snippet (optional)</label>
                                                <textarea name="step_code_snippet[]" class="form-control font-monospace small" rows="2" placeholder="Short code for this step"></textarea>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger mt-2 removeStepRow"><i class="ti ti-trash"></i> Remove</button>
                                    </div>
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

                        {{-- Code blocks (full programs) --}}
                        <div class="card mb-3">
                            <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="ti ti-code me-1"></i> Code blocks (optional)</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addCodeRow">
                                    <i class="ti ti-plus"></i> Add code block
                                </button>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">Full programs (Arduino, Python, etc.) with language label. Students see them with syntax-style display.</p>
                                <div id="codesContainer">
                                    <div class="code-row border rounded p-3 mb-2">
                                        <div class="row g-2">
                                            <div class="col-md-6"><input type="text" name="code_title[]" class="form-control" placeholder="e.g. LED Blink"></div>
                                            <div class="col-md-6"><input type="text" name="code_language[]" class="form-control" placeholder="e.g. Arduino C, Python"></div>
                                            <div class="col-12"><textarea name="code_body[]" class="form-control font-monospace small" rows="6" placeholder="Paste code here"></textarea></div>
                                            <div class="col-12"><input type="text" name="code_description[]" class="form-control form-control-sm" placeholder="Short description (optional)"></div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger mt-2 removeCodeRow"><i class="ti ti-trash"></i> Remove</button>
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

                                <div class="form-check form-switch mb-3">
                                    <input type="hidden" name="is_published" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="isPublished" name="is_published" value="1" {{ old('is_published', false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isPublished">Published</label>
                                </div>

                                <div>
                                    <label class="form-label">Publish date (optional)</label>
                                    <input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at') }}">
                                    @error('published_at') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    <small class="text-muted">If Published is ON and you leave this empty, we’ll set it to now.</small>
                                </div>
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

                // Steps
                let stepCount = 1;
                document.getElementById('addStepRow')?.addEventListener('click', function () {
                    stepCount++;
                    const div = document.createElement('div');
                    div.className = 'step-row border rounded p-3 mb-2 position-relative';
                    div.innerHTML = `
                        <span class="badge bg-secondary position-absolute top-0 end-0">${stepCount}</span>
                        <div class="row g-2">
                            <div class="col-12"><input type="text" name="step_title[]" class="form-control" placeholder="Step title"></div>
                            <div class="col-12"><textarea name="step_description[]" class="form-control small" rows="2" placeholder="What to do"></textarea></div>
                            <div class="col-md-6"><label class="form-label small mb-0">Image (optional)</label><input type="file" name="step_image[]" class="form-control form-control-sm" accept="image/*"></div>
                            <div class="col-12"><label class="form-label small mb-0">Code snippet (optional)</label><textarea name="step_code_snippet[]" class="form-control font-monospace small" rows="2"></textarea></div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger mt-2 removeStepRow"><i class="ti ti-trash"></i> Remove</button>
                    `;
                    document.getElementById('stepsContainer').appendChild(div);
                    div.querySelector('.removeStepRow').onclick = function () { div.remove(); updateStepBadges(); stepCount--; };
                    updateStepBadges();
                });
                document.querySelectorAll('.removeStepRow').forEach(btn => {
                    btn.onclick = function () {
                        const row = this.closest('.step-row');
                        if (document.querySelectorAll('.step-row').length <= 1) return;
                        row?.remove();
                        updateStepBadges();
                        stepCount--;
                    };
                });
                function updateStepBadges() {
                    document.querySelectorAll('.step-row').forEach((row, i) => {
                        const badge = row.querySelector('.badge');
                        if (badge) badge.textContent = i + 1;
                    });
                }

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

                // Code blocks
                document.getElementById('addCodeRow')?.addEventListener('click', function () {
                    const div = document.createElement('div');
                    div.className = 'code-row border rounded p-3 mb-2';
                    div.innerHTML = `
                        <div class="row g-2">
                            <div class="col-md-6"><input type="text" name="code_title[]" class="form-control" placeholder="e.g. Main sketch"></div>
                            <div class="col-md-6"><input type="text" name="code_language[]" class="form-control" placeholder="e.g. Arduino C"></div>
                            <div class="col-12"><textarea name="code_body[]" class="form-control font-monospace small" rows="6" placeholder="Paste code"></textarea></div>
                            <div class="col-12"><input type="text" name="code_description[]" class="form-control form-control-sm" placeholder="Description (optional)"></div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger mt-2 removeCodeRow"><i class="ti ti-trash"></i> Remove</button>
                    `;
                    document.getElementById('codesContainer').appendChild(div);
                    div.querySelector('.removeCodeRow').onclick = function () { if (document.querySelectorAll('.code-row').length > 1) div.remove(); };
                });
                document.querySelectorAll('.removeCodeRow').forEach(btn => {
                    btn.onclick = function () {
                        if (document.querySelectorAll('.code-row').length <= 1) return;
                        this.closest('.code-row')?.remove();
                    };
                });

                // Delete-category buttons — submit their own separate forms (outside the main form)
                document.querySelectorAll('.delete-category-btn').forEach(btn => {
                    btn.addEventListener('click', function () {
                        const name = this.dataset.name;
                        if (!confirm('Delete category "' + name + '"? Projects using it will become uncategorized.')) return;
                        const url = this.dataset.url;
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = url;
                        form.innerHTML = `
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="_method" value="DELETE">
                        `;
                        document.body.appendChild(form);
                        form.submit();
                    });
                });
            });
        </script>
    @endpush
</x-admin-layout>

