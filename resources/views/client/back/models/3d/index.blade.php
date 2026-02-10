<x-app-layout>
    @php($model3ds = $model3ds ?? collect())
    @php($subjects = $subjects ?? collect())
    @php($selectedSubject = $selectedSubject ?? null)
    @php($search = $search ?? '')
    @php($sort = $sort ?? 'by_subject')
    @php($totalModels = $totalModels ?? 0)
    @php($showGroupedView = $showGroupedView ?? false)
    @php($modelsBySubject = $modelsBySubject ?? collect())
    @php($subjectsWithModels = $subjectsWithModels ?? collect())

 

    <div class="col-lg-12">

        <div class="dashboard-content-header">
            <h3 class="mb-1">3D Models Library</h3>
        </div>

        {{-- Search & Filter Bar --}}
        <div class="card border-0 shadow-none mb-4">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('client.models.3d.index') }}" id="filterForm">
                    <div class="row g-3 align-items-end">
                        {{-- Search Input --}}
                        <div class="col-12 col-md-4">
                         
                            <input type="text"
                                   id="search"
                                   name="search"
                                   class="form-control"
                                   placeholder="Search by name or description..."
                                   value="{{ $search }}">
                        </div>

                        {{-- Subject Filter --}}
                        <div class="col-6 col-md-3">
                          
                            <select id="subject-filter"
                                    name="subject"
                                    class="form-select">
                                <option value="">All subjects</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" @selected((string) $subject->id === (string) $selectedSubject)>
                                        {{ $subject->subject_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Sort Options --}}
                        <div class="col-6 col-md-3">
                           
                            <select id="sort" name="sort" class="form-select">
                                <option value="by_subject" @selected($sort === 'by_subject')>By Subject</option>
                                <option value="newest" @selected($sort === 'newest')>Newest first</option>
                                <option value="oldest" @selected($sort === 'oldest')>Oldest first</option>
                                <option value="name_asc" @selected($sort === 'name_asc')>Name (A-Z)</option>
                                <option value="name_desc" @selected($sort === 'name_desc')>Name (Z-A)</option>
                            </select>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="col-12 col-md-2">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary text-white flex-grow-1">
                                   Filter
                                </button>
                                @if($search || $selectedSubject || ($sort !== 'by_subject'))
                                    <a href="{{ route('client.models.3d.index') }}" class="badge bg-light-soft text-dark border d-inline-flex align-items-center gap-1 text-decoration-none" title="Clear all filters">
                                       Clear all filters
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Active Filters Display --}}
        @if($search || $selectedSubject)
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="text-muted small">Active filters:</span>
                @if($search)
                    <a href="{{ route('client.models.3d.index', array_merge(request()->except('search'), ['page' => 1])) }}"
                       class="badge bg-light-soft text-dark border d-inline-flex align-items-center gap-1 text-decoration-none">
                        <i class="bi bi-search"></i>
                        "{{ Str::limit($search, 20) }}"
                        <i class="bi bi-x"></i>
                    </a>
                @endif
                @if($selectedSubject)
                    @php($selectedSubjectName = $subjects->firstWhere('id', $selectedSubject)?->subject_name ?? 'Unknown')
                    <a href="{{ route('client.models.3d.index', array_merge(request()->except('subject'), ['page' => 1])) }}"
                       class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 text-decoration-none">
                        <i class="bi bi-book"></i>
                        {{ $selectedSubjectName }}
                        <i class="bi bi-x"></i>
                    </a>
                @endif
            </div>
        @endif

        <section class="row">
            <div class="col-12">
                @if($showGroupedView)
                    {{-- Grouped by Subject View --}}
                    @if($subjectsWithModels->isEmpty())
                        <div class="card border-0 shadow-none">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-box display-4 text-muted mb-3"></i>
                                <h5 class="fw-semibold mb-2">No 3D models published yet</h5>
                                <p class="text-muted mb-3">
                                    Once your instructor uploads 3D models they will appear here automatically.
                                </p>
                                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm">
                                    Explore dashboard
                                </a>
                            </div>
                        </div>
                    @else
                        @foreach($subjectsWithModels as $subject)
                            @php($subjectModels = $modelsBySubject->get($subject->id, collect()))
                            @if($subjectModels->isNotEmpty())
                                <div id="subject-{{ $subject->id }}" class="mb-5 scroll-mt-4">
                                    {{-- Subject Header --}}
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <h4 class="fw-semibold mb-0">{{ $subject->subject_name }}</h4>
                                            <span class="badge bg-secondary">{{ $subjectModels->count() }} models</span>
                                        </div>
                                        <a href="{{ route('client.models.3d.index', ['subject' => $subject->id, 'sort' => 'newest']) }}"
                                           class="text-decoration-none">
                                            View all 
                                        </a>
                                    </div>

                                    {{-- Horizontal Scrollable Cards --}}
                                    <div class="subject-models-scroll position-relative">
                                        <div class="d-flex gap-4 pb-3 overflow-auto subject-scroll-container">
                                            @foreach($subjectModels->take(8) as $model)
                                                <div class="flex-shrink-0" style="width: 320px;">
                                                    @include('client.back.models.3d._model-card', ['model' => $model])
                                                </div>
                                            @endforeach

                                            {{-- Show more card if there are more models --}}
                                            @if($subjectModels->count() > 8)
                                                <div class="flex-shrink-0 d-flex align-items-stretch" style="width: 200px;">
                                                    <a href="{{ route('client.models.3d.index', ['subject' => $subject->id, 'sort' => 'newest']) }}"
                                                       class="card border-0 shadow-none h-100 w-100 text-decoration-none hover-shadow-lg transition-all">
                                                        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                                                            <div class="bg-primary bg-opacity-10 rounded-circle p-3 mb-3">
                                                                <i class="bi bi-plus-lg fs-3 text-primary"></i>
                                                            </div>
                                                            <p class="fw-semibold mb-1">{{ $subjectModels->count() - 8 }} more</p>
                                                            <span class="text-muted small">View all {{ $subject->subject_name }} models</span>
                                                        </div>
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                @else
                    {{-- Standard Paginated View --}}
                    @if($model3ds->total() > 0)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="text-muted mb-0 small">
                                Showing {{ $model3ds->firstItem() }}-{{ $model3ds->lastItem() }} of {{ $model3ds->total() }} models
                            </p>
                            @if($model3ds->hasPages())
                                <span class="text-muted small">
                                    Page {{ $model3ds->currentPage() }} of {{ $model3ds->lastPage() }}
                                </span>
                            @endif
                        </div>
                    @endif

                    @if($model3ds->isEmpty())
                        <div class="card border-0 shadow-none">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-box display-4 text-muted mb-3"></i>
                                <h5 class="fw-semibold mb-2">
                                    @if($search)
                                        No models found for "{{ $search }}"
                                    @elseif($selectedSubject)
                                        No models found for this subject
                                    @else
                                        No 3D models published yet
                                    @endif
                                </h5>
                                <p class="text-muted mb-3">
                                    @if($search || $selectedSubject)
                                        Try adjusting your search or filters to find what you're looking for.
                                    @else
                                        Once your instructor uploads 3D models they will appear here automatically.
                                    @endif
                                </p>
                                @if($search || $selectedSubject)
                                    <a href="{{ route('client.models.3d.index') }}" class="badge bg-light-soft text-dark border d-inline-flex align-items-center gap-1 text-decoration-none">
                                        Clear all filters
                                    </a>
                                @else
                                    <a href="{{ route('dashboard') }}" class="badge bg-light-soft text-dark border d-inline-flex align-items-center gap-1 text-decoration-none">
                                        Explore dashboard
                                    </a>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="row g-4">
                            @foreach($model3ds as $model)
                                <div class="col-12 col-md-3 col-xl-3">
                                    @include('client.back.models.3d._model-card', ['model' => $model])
                                </div>
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        @if($model3ds->hasPages())
                            <nav class="mt-4" aria-label="Model navigation">
                                <div class="d-flex justify-content-center">
                                    {{ $model3ds->links('pagination::bootstrap-5') }}
                                </div>
                            </nav>
                        @endif
                    @endif
                @endif
            </div>
        </section>
    </div>

    
</x-app-layout>
