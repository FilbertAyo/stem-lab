@php
    /** @var \App\DataTransferObjects\StudentDashboardAnalytics $studentAnalytics */
    $firstName = explode(' ', trim(Auth::user()->name ?? ''))[0] ?: 'there';
    $weekMax = max(1, $studentAnalytics->weeklyMax);
    $resourcesTotal = $studentAnalytics->resourcesAccessed + $studentAnalytics->projectsViewed;
@endphp

<section class="student-analytics mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-2 mb-3">
        <div>
            <h5 class="mb-1 fw-semibold">Your progress</h5>
            <p class="text-muted small mb-0">
                Hello {{ $firstName }} — here’s what you’ve done on the platform
                @if($studentAnalytics->lastActivityAt)
                    <span>· last activity {{ $studentAnalytics->lastActivityAt->diffForHumans() }}</span>
                @endif
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('client.models.3d.index') }}" class="btn btn-sm btn-outline-primary">
                3D lessons
            </a>
            <a href="{{ route('client.vr-assessments.index') }}" class="btn btn-sm btn-outline-primary">
                VR quizzes
            </a>
        </div>
    </div>

    <div class="row g-2 g-md-3 mb-3">
        <div class="col-6 col-lg-3">
            <div class="dashboard-stat-card h-100">
                <span class="dashboard-stat-label">Lessons completed</span>
                <span class="dashboard-stat-value">{{ $studentAnalytics->modelsCompleted }}</span>
                <span class="dashboard-stat-meta">
                    {{ $studentAnalytics->modelsAttempted }} started
                </span>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="dashboard-stat-card h-100">
                <span class="dashboard-stat-label">Quiz accuracy</span>
                <span class="dashboard-stat-value">{{ $studentAnalytics->accuracy }}%</span>
                <span class="dashboard-stat-meta">
                    {{ $studentAnalytics->correctAnswers }}/{{ $studentAnalytics->totalAnswers }} correct
                </span>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="dashboard-stat-card h-100">
                <span class="dashboard-stat-label">VR average</span>
                <span class="dashboard-stat-value">
                    @if($studentAnalytics->avgVrScore !== null)
                        {{ number_format($studentAnalytics->avgVrScore, 0) }}%
                    @else
                        —
                    @endif
                </span>
                <span class="dashboard-stat-meta">
                    {{ $studentAnalytics->vrPassed }} of {{ $studentAnalytics->vrAttemptsCount }} passed
                </span>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="dashboard-stat-card h-100">
                <span class="dashboard-stat-label">Resources opened</span>
                <span class="dashboard-stat-value">{{ $resourcesTotal }}</span>
                <span class="dashboard-stat-meta">
                    {{ $studentAnalytics->projectsViewed }} projects
                </span>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="student-analytics-card h-100">
                <h6 class="student-analytics-card__title">This week</h6>
                <div class="student-week" role="img" aria-label="Activity over the last 7 days">
                    @foreach($studentAnalytics->weeklyActivity as $day)
                        @php
                            $height = $day->count > 0
                                ? max(12, (int) round(($day->count / $weekMax) * 100))
                                : 6;
                        @endphp
                        <div class="student-week__col">
                            <span class="student-week__count">{{ $day->count > 0 ? $day->count : '' }}</span>
                            <span
                                class="student-week__bar {{ $day->count === 0 ? 'is-empty' : '' }}"
                                style="height: {{ $height }}%"
                                title="{{ $day->label }}: {{ $day->count }}"
                            ></span>
                            <span class="student-week__label">{{ $day->label }}</span>
                        </div>
                    @endforeach
                </div>
                @unless($studentAnalytics->hasActivity)
                    <p class="text-muted small mb-0 mt-2">No activity this week yet.</p>
                @endunless
            </div>
        </div>

        <div class="col-lg-8">
            <div class="student-analytics-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="student-analytics-card__title mb-0">3D lesson scores</h6>
                    <a href="{{ route('client.models.3d.index') }}" class="small fw-semibold text-decoration-none">View all</a>
                </div>
                @if($studentAnalytics->modelProgress->isEmpty())
                    <p class="text-muted small mb-0">You haven’t completed any 3D lesson questions yet.</p>
                @else
                    <ul class="student-score-list">
                        @foreach($studentAnalytics->modelProgress as $progress)
                            <li>
                                <div class="d-flex justify-content-between gap-2 mb-1">
                                    <a href="{{ route('client.models.3d.show', $progress->model) }}" class="student-score-list__name">
                                        {{ $progress->model->name }}
                                    </a>
                                    <span class="student-score-list__pct">{{ $progress->percentage }}%</span>
                                </div>
                                <div class="student-score-bar" aria-hidden="true">
                                    <span style="width: {{ min(100, $progress->percentage) }}%"></span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="col-lg-6">
            <div class="student-analytics-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="student-analytics-card__title mb-0">VR quiz results</h6>
                    <a href="{{ route('client.vr-assessments.index') }}" class="small fw-semibold text-decoration-none">View all</a>
                </div>
                @if($studentAnalytics->vrAttempts->isEmpty())
                    <p class="text-muted small mb-0">No VR quizzes completed yet.</p>
                @else
                    <ul class="student-result-list">
                        @foreach($studentAnalytics->vrAttempts as $attempt)
                            <li>
                                <div>
                                    <a href="{{ route('client.vr-assessments.results', $attempt) }}" class="student-score-list__name">
                                        {{ $attempt->questionBank->title ?? 'Quiz' }}
                                    </a>
                                    <div class="text-muted small">{{ $attempt->completed_at?->diffForHumans() }}</div>
                                </div>
                                <div class="text-end">
                                    <span class="fw-semibold">{{ number_format($attempt->score, 0) }}%</span>
                                    @if($attempt->passed)
                                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">Passed</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">Try again</span>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="col-lg-6">
            <div class="student-analytics-card h-100">
                <h6 class="student-analytics-card__title">Recent activity</h6>
                @if($studentAnalytics->recentEvents->isEmpty())
                    <p class="text-muted small mb-0">Nothing recorded yet. Open a lesson or quiz to get started.</p>
                @else
                    <ul class="student-activity-list">
                        @foreach($studentAnalytics->recentEvents as $event)
                            <li>
                                <span class="student-activity-list__dot" aria-hidden="true"></span>
                                <div>
                                    <div class="fw-medium">{{ $event->event_type->label() }}</div>
                                    @if(! empty($event->metadata['label']))
                                        <div class="text-muted small">{{ $event->metadata['label'] }}</div>
                                    @endif
                                </div>
                                <small class="text-muted text-nowrap">{{ $event->occurred_at->diffForHumans() }}</small>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</section>
