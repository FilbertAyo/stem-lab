<x-app-layout>
    @php($subjects = $subjects ?? collect())
    @php($smileyTutorCredential = $smileyTutorCredential ?? null)

    {{-- Same sidebar as math page --}}
    <div class="col-12">
        <div class="row">
            <div class="col-lg-3 dashboard-sidebar mb-4 mb-lg-0">
                <ul class="category-list">
                    <li>
                        <a href="{{ route('client.vr-experiments.index', request()->only(['search', 'sort'])) }}"
                           class="">
                            All Experiments <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                        </a>
                    </li>
                    @foreach($subjects as $subject)
                        <li>
                            <a href="{{ route('client.vr-experiments.index', array_merge(request()->only(['search', 'sort']), ['subject' => $subject->id])) }}"
                               class="">
                                {{ $subject->subject_name }} <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                            </a>
                        </li>
                    @endforeach
                    <li>
                        <a href="{{ route('client.vr-experiments.math') }}"
                           class="{{ request()->routeIs('client.vr-experiments.math*') ? 'active' : '' }}">
                            Mathematics <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="col-lg-9">
                <div class="dashboard-content-header d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                    <div>
                        <h3 class="mb-1">SmileyTutor Math Practice</h3>
                  </div>
                    <a href="{{ route('client.vr-experiments.math') }}" class="badge bg-light-soft text-dark border d-flex align-items-center" style="font-size: 0.95rem;">
                        <i class="bi bi-arrow-left me-1"></i>Back to Mathematics
                    </a>
                </div>

                <div class="position-relative rounded overflow-hidden border mb-3" style="box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);">
                    <iframe
                        src="https://tutor-web.net/math"
                        title="SmileyTutor Math Department"
                        class="w-100 border-0"
                        style="height: 75vh; min-height: 500px; display: block;"
                        allowfullscreen>
                    </iframe>
                </div>
                <p class="text-muted small mb-0">
                    If the practice area above does not load, <a href="https://tutor-web.net/math" target="_blank" rel="noopener noreferrer">open SmileyTutor in a new tab</a>.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
