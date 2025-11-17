<x-app-layout>
    @php($model3ds = $model3ds ?? collect())
    @php($subjects = $subjects ?? collect())
    @php($selectedSubject = $selectedSubject ?? null)

    <div class="page-heading d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="mb-1">3D Models Library</h3>
            <p class="text-muted mb-0">Browse all interactive STEM models available to your account.</p>
        </div>
        <form method="GET" action="{{ route('client.models.3d.index') }}" class="d-flex align-items-center gap-2">
            <label for="subject-filter" class="text-muted small mb-0">Filter by subject</label>
            <div class="d-flex gap-2">
                <select id="subject-filter"
                        name="subject"
                        class="form-select form-select-sm"
                        onchange="this.form.submit()">
                    <option value="">All subjects</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected((string) $subject->id === (string) $selectedSubject)>
                            {{ $subject->subject_name }}
                        </option>
                    @endforeach
                </select>
                @if($selectedSubject)
                    <a href="{{ route('client.models.3d.index') }}" class="btn btn-outline-secondary btn-sm">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="page-content">
        <section class="row">
            <div class="col-12">
                @if($model3ds->isEmpty())
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-box display-4 text-muted mb-3"></i>
                            <h5 class="fw-semibold mb-2">
                                {{ $selectedSubject ? 'No models found for this subject' : 'No 3D models published yet' }}
                            </h5>
                            <p class="text-muted mb-3">
                                @if($selectedSubject)
                                    Try selecting another subject or clear the filter to view all models.
                                @else
                                    Once your instructor uploads 3D models they will appear here automatically.
                                @endif
                            </p>
                            @if($selectedSubject)
                                <a href="{{ route('client.models.3d.index') }}" class="btn btn-primary btn-sm">
                                    Clear filter
                                </a>
                            @else
                                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm">
                                    Explore dashboard
                                </a>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="row g-4">
                        @foreach($model3ds as $model)
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="rounded-top" style="overflow: hidden; height: 220px;">
                                        @if($model->thumbnail)
                                            <img src="{{ asset('storage/' . $model->thumbnail) }}"
                                                 alt="{{ $model->name }} thumbnail"
                                                 class="w-100 h-100"
                                                 style="object-fit: cover;">
                                        @else
                                            <div class="d-flex flex-column justify-content-center align-items-center bg-light h-100 w-100">
                                                <i class="bi bi-cube text-secondary fs-1"></i>
                                                <span class="text-muted small mt-2">Thumbnail coming soon</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-light text-dark">
                                                <i class="bi bi-book me-1"></i>
                                                {{ $model->subject->subject_name ?? 'General' }}
                                            </span>
                                            <small class="text-muted">
                                                {{ $model->created_at?->format('M d, Y') }}
                                            </small>
                                        </div>

                                        <h5 class="fw-semibold">{{ $model->name }}</h5>
                                        <p class="text-muted small mb-3">
                                            {{ Str::limit($model->description ?? 'No description provided yet.', 110) }}
                                        </p>
                                        <div class="mt-auto d-flex justify-content-between align-items-center">
                                            <span class="text-muted small">
                                                <i class="bi bi-box-arrow-up-right me-1"></i>Interactive model
                                            </span>
                                            <a href="{{ route('client.models.3d.show', $model) }}" class="btn btn-primary btn-sm">
                                                View model
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    </div>
</x-app-layout>
