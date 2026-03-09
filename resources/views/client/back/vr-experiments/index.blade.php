<x-app-layout>
    @php($vrExperiments = $vrExperiments ?? collect())
    @php($subjects = $subjects ?? collect())
    @php($selectedSubject = $selectedSubject ?? null)
    @php($search = $search ?? '')
    @php($sort = $sort ?? 'by_subject')
    @php($totalExperiments = $totalExperiments ?? 0)
    @php($showGroupedView = $showGroupedView ?? false)
    @php($experimentsBySubject = $experimentsBySubject ?? collect())
    @php($subjectsWithExperiments = $subjectsWithExperiments ?? collect())

    {{-- Labs/Experiments own sidebar: All Experiments + subject filter links (no global aside on this page) --}}
    <div class="col-12">
        <div class="row">
            <div class="col-lg-3 dashboard-sidebar mb-4 mb-lg-0">
                <ul class="category-list">
                    <li>
                        <a href="{{ route('client.vr-experiments.index', request()->only(['search', 'sort'])) }}"
                           class="{{ !$selectedSubject ? 'active' : '' }}">
                            All Experiments <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                        </a>
                    </li>
                    @foreach($subjects as $subject)
                        <li>
                            <a href="{{ route('client.vr-experiments.index', array_merge(request()->only(['search', 'sort']), ['subject' => $subject->id])) }}"
                               class="{{ (string) $subject->id === (string) $selectedSubject ? 'active' : '' }}">
                                {{ $subject->subject_name }} <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                            </a>
                        </li>
                    @endforeach
                    <li>
                        <a href="{{ route('client.vr-experiments.math') }}"
                           class="{{ request()->routeIs('client.vr-experiments.math') ? 'active' : '' }}">
                            Mathematics <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="col-lg-9">

        <div class="dashboard-content-header">
            <h3 class="mb-0">VR Experiments Library</h3>
        </div>

        {{-- Search & Filter Bar --}}
        <div class="card border-0 shadow-none mb-4">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('client.vr-experiments.index') }}" id="filterForm">
                    @if($selectedSubject)
                        <input type="hidden" name="subject" value="{{ $selectedSubject }}">
                    @endif
                    <div class="row g-3 align-items-end">
                        {{-- Search Input --}}
                        <div class="col-12 col-md-5">
                           
                            <input type="text"
                                   id="search"
                                   name="search"
                                   class="form-control"
                                   placeholder="Search by name or description..."
                                   value="{{ $search }}">
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
                                    <a href="{{ route('client.vr-experiments.index') }}" class="badge bg-light-soft text-dark border d-inline-flex align-items-center gap-1 text-decoration-none" title="Clear all filters">
                                        <i class="bi bi-x-lg"></i> Clear all filters
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
                    <a href="{{ route('client.vr-experiments.index', array_merge(request()->except('search'), ['page' => 1])) }}"
                       class="badge bg-light-soft d-inline-flex align-items-center gap-1 text-dark">
                        <i class="bi bi-search"></i>
                        "{{ Str::limit($search, 20) }}"
                        <i class="bi bi-x"></i>
                    </a>
                @endif
                @if($selectedSubject)
                    @php($selectedSubjectName = $subjects->firstWhere('id', $selectedSubject)?->subject_name ?? 'Unknown')
                    <a href="{{ route('client.vr-experiments.index', array_merge(request()->except('subject'), ['page' => 1])) }}"
                       class="badge bg-light-soft  d-inline-flex align-items-center gap-1 text-dark">
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
                    @if($subjectsWithExperiments->isEmpty())
                        <div class="card border-0 shadow-none">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-vr display-4 text-muted mb-3"></i>
                                <h5 class="fw-semibold mb-2">No VR experiments published yet</h5>
                                <p class="text-muted mb-3">
                                    Once your instructor uploads VR experiments they will appear here automatically.
                                </p>
                                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm">
                                    Explore dashboard
                                </a>
                            </div>
                        </div>
                    @else
                        @foreach($subjectsWithExperiments as $subject)
                            @php($subjectExperiments = $experimentsBySubject->get($subject->id, collect()))
                            @if($subjectExperiments->isNotEmpty())
                                <div id="subject-{{ $subject->id }}" class="mb-5 scroll-mt-4">
                                    {{-- Subject Header --}}
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <h4 class="fw-semibold mb-0">{{ $subject->subject_name }}</h4>
                                            <span class="badge bg-secondary">{{ $subjectExperiments->count() }} experiments</span>
                                        </div>
                                        <a href="{{ route('client.vr-experiments.index', ['subject' => $subject->id, 'sort' => 'newest']) }}">
                                            View all 
                                        </a>
                                    </div>

                                    {{-- Horizontal Scrollable Cards --}}
                                    <div class="subject-experiments-scroll position-relative">
                                        <div class="d-flex gap-4 pb-3 overflow-auto subject-scroll-container">
                                            @foreach($subjectExperiments->take(8) as $experiment)
                                                <div class="flex-shrink-0" style="width: 320px;">
                                                    @include('client.back.vr-experiments._experiment-card', ['experiment' => $experiment])
                                                </div>
                                            @endforeach

                                            {{-- Show more card if there are more experiments --}}
                                            @if($subjectExperiments->count() > 8)
                                                <div class="flex-shrink-0 d-flex align-items-stretch" style="width: 200px;">
                                                    <a href="{{ route('client.vr-experiments.index', ['subject' => $subject->id, 'sort' => 'newest']) }}"
                                                       class="card border-0 shadow-none h-100 w-100 text-decoration-none hover-shadow-lg transition-all">
                                                        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                                                            <div class="bg-primary bg-opacity-10 rounded-circle p-3 mb-3">
                                                                <i class="bi bi-plus-lg fs-3 text-primary"></i>
                                                            </div>
                                                            <p class="fw-semibold mb-1">{{ $subjectExperiments->count() - 8 }} more</p>
                                                            <span class="text-muted small">View all {{ $subject->subject_name }} experiments</span>
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
                    @if($vrExperiments->total() > 0)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="text-muted mb-0 small">
                                Showing {{ $vrExperiments->firstItem() }}-{{ $vrExperiments->lastItem() }} of {{ $vrExperiments->total() }} experiments
                            </p>
                            @if($vrExperiments->hasPages())
                                <span class="text-muted small">
                                    Page {{ $vrExperiments->currentPage() }} of {{ $vrExperiments->lastPage() }}
                                </span>
                            @endif
                        </div>
                    @endif

                    @if($vrExperiments->isEmpty())
                        <div class="card border-0 shadow-none">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-vr display-4 text-muted mb-3"></i>
                                <h5 class="fw-semibold mb-2">
                                    @if($search)
                                        No experiments found for "{{ $search }}"
                                    @elseif($selectedSubject)
                                        No experiments found for this subject
                                    @else
                                        No VR experiments published yet
                                    @endif
                                </h5>
                                <p class="text-muted mb-3">
                                    @if($search || $selectedSubject)
                                        Try adjusting your search or filters to find what you're looking for.
                                    @else
                                        Once your instructor uploads VR experiments they will appear here automatically.
                                    @endif
                                </p>
                                @if($search || $selectedSubject)
                                    <a href="{{ route('client.vr-experiments.index') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>Clear all filters
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
                            @foreach($vrExperiments as $experiment)
                                <div class="col-12 col-md-6 col-xl-4">
                                    @include('client.back.vr-experiments._experiment-card', ['experiment' => $experiment])
                                </div>
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        @if($vrExperiments->hasPages())
                            <nav class="mt-4" aria-label="Experiment navigation">
                                <div class="d-flex justify-content-center">
                                    {{ $vrExperiments->links('pagination::bootstrap-5') }}
                                </div>
                            </nav>
                        @endif
                    @endif
                @endif
            </div>
        </section>
            </div>
        </div>
    </div>

    
</x-app-layout>
