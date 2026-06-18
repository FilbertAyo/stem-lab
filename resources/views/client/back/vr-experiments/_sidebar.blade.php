@php($subjects = $subjects ?? collect())
@php($selectedSubject = $selectedSubject ?? null)
@php($mathematicsSubject = $subjects->firstWhere('subject_name', 'Mathematics'))
@php($nonMathSubjects = $mathematicsSubject
    ? $subjects->where('id', '!=', $mathematicsSubject->id)->values()
    : $subjects)
@php($isMathPanelActive = request()->routeIs('client.vr-experiments.math*')
    || ($mathematicsSubject && (
        (string) $selectedSubject === (string) $mathematicsSubject->id
        || (string) ($vrExperiment->subject_id ?? '') === (string) $mathematicsSubject->id
    )))

<div class="col-lg-3 dashboard-sidebar mb-4 mb-lg-0">
    <ul class="category-list">
        <li>
            <a href="{{ route('client.vr-experiments.index', request()->only(['search', 'sort'])) }}"
               class="{{ !$selectedSubject && !$isMathPanelActive ? 'active' : '' }}">
                All Experiments <iconify-icon icon="lucide:chevron-right"></iconify-icon>
            </a>
        </li>
        @foreach($nonMathSubjects as $subject)
            <li>
                <a href="{{ route('client.vr-experiments.index', array_merge(request()->only(['search', 'sort']), ['subject' => $subject->id])) }}"
                   class="{{ (string) $subject->id === (string) $selectedSubject ? 'active' : '' }}">
                    {{ $subject->subject_name }} <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                </a>
            </li>
        @endforeach
        <li>
            <a href="{{ route('client.vr-experiments.math') }}"
               class="{{ $isMathPanelActive ? 'active' : '' }}">
                Mathematics <iconify-icon icon="lucide:chevron-right"></iconify-icon>
            </a>
        </li>
    </ul>
</div>
