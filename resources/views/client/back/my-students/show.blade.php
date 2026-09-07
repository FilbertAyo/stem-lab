@php
    $summary = $summary ?? null;
@endphp

<x-app-layout>
    <div class="col-lg-9">

        <div class="dashboard-content-header">
          

            <div>
            <h4 class="mb-1 mt-1">{{ $student->name }}</h4>
            <p class="text-muted mb-0 small">
                {{ $student->schoolClass->name ?? 'No class' }}
                · Admission <code>{{ $student->admission_number }}</code>
                @if($student->teacherClub?->club)
                    · {{ $student->teacherClub->club->name }}
                @endif
            </p>
              </div>
              <a href="{{ route('client.my-students.index') }}" class="btn btn-outline-secondary btn-sm float-end">
                <i class="bi bi-arrow-left me-1"></i>Back to My Students
            </a>
         
        </div>

        @if(! $student->isLinked())
            <div class="alert alert-info mt-3">
                This student is on your roster but does not have a platform login yet.
                You can create a login from the club roster.
            </div>
            <form method="POST" action="{{ route('client.students.credentials.store', $student) }}" class="mt-2">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm">Create login</button>
            </form>
        @else
            <section class="row g-3 mt-2 mb-4">
                <div class="col-6 col-lg-4 col-xl-2">
                    <div class="card border shadow-none h-100">
                        <div class="card-body">
                            <h6 class="text-muted small mb-1">Lessons completed</h6>
                            <h4 class="mb-0 fw-bold">{{ $summary->models_completed ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-4 col-xl-2">
                    <div class="card border shadow-none h-100">
                        <div class="card-body">
                            <h6 class="text-muted small mb-1">Quiz attempts</h6>
                            <h4 class="mb-0 fw-bold">{{ $summary->vr_attempts ?? 0 }}</h4>
                            @if(($summary->vr_passed ?? 0) > 0)
                                <small class="text-muted">{{ $summary->vr_passed }} passed</small>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-4 col-xl-2">
                    <div class="card border shadow-none h-100">
                        <div class="card-body">
                            <h6 class="text-muted small mb-1">Avg quiz score</h6>
                            <h4 class="mb-0 fw-bold">
                                @if($summary?->avg_vr_score !== null)
                                    {{ number_format($summary->avg_vr_score, 0) }}%
                                @else
                                    —
                                @endif
                            </h4>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-4 col-xl-2">
                    <div class="card border shadow-none h-100">
                        <div class="card-body">
                            <h6 class="text-muted small mb-1">Resources accessed</h6>
                            <h4 class="mb-0 fw-bold">{{ ($summary->resources_accessed ?? 0) + ($summary->projects_viewed ?? 0) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-4 col-xl-2">
                    <div class="card border shadow-none h-100">
                        <div class="card-body">
                            <h6 class="text-muted small mb-1">Linked on</h6>
                            <p class="mb-0 fw-semibold small">{{ $student->linked_at?->format('M d, Y') ?? '—' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-4 col-xl-2">
                    <div class="card border shadow-none h-100">
                        <div class="card-body">
                            <h6 class="text-muted small mb-1">Last activity</h6>
                            <p class="mb-0 fw-semibold small">
                                @if($summary?->last_activity_at)
                                    {{ $summary->last_activity_at->diffForHumans() }}
                                @else
                                    No activity yet
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            @if($summary && $summary->needsAttention())
                <div class="alert alert-warning">
                    <strong>Needs follow-up:</strong>
                    @if($summary->hasAttentionFlag('inactive'))
                        Inactive for more than 14 days.
                    @endif
                    @if($summary->hasAttentionFlag('low_quiz_score'))
                        Quiz scores are below 50% on average.
                    @endif
                </div>
            @endif

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card border shadow-none h-100">
                        <div class="card-header bg-light-soft">
                            <h5 class="mb-0">3D model progress</h5>
                        </div>
                        <div class="card-body">
                            @if($modelProgress->isEmpty())
                                <p class="text-muted mb-0">No 3D model activity yet.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead>
                                            <tr>
                                                <th>Model</th>
                                                <th>Score</th>
                                                <th>Last activity</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($modelProgress as $progress)
                                                <tr>
                                                    <td>{{ $progress->model->name ?? 'Model' }}</td>
                                                    <td>{{ $progress->percentage }}%</td>
                                                    <td>{{ \Illuminate\Support\Carbon::parse($progress->last_answered)->diffForHumans() }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card border shadow-none h-100">
                        <div class="card-header bg-light-soft">
                            <h5 class="mb-0">VR quiz results</h5>
                        </div>
                        <div class="card-body">
                            @if($vrAttempts->isEmpty())
                                <p class="text-muted mb-0">No VR quiz attempts yet.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead>
                                            <tr>
                                                <th>Quiz</th>
                                                <th>Score</th>
                                                <th>Result</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($vrAttempts as $attempt)
                                                <tr>
                                                    <td>{{ $attempt->questionBank->title ?? 'Quiz' }}</td>
                                                    <td>{{ number_format($attempt->score, 0) }}%</td>
                                                    <td>
                                                        @if($attempt->passed)
                                                            <span class="badge bg-success">Passed</span>
                                                        @else
                                                            <span class="badge bg-danger">Failed</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border shadow-none">
                        <div class="card-header bg-light-soft">
                            <h5 class="mb-0">Recent activity</h5>
                        </div>
                        <div class="card-body">
                            @if($recentEvents->isEmpty())
                                <p class="text-muted mb-0">No recorded learning events yet.</p>
                            @else
                                <ul class="list-group list-group-flush">
                                    @foreach($recentEvents as $event)
                                        <li class="list-group-item px-0 d-flex justify-content-between align-items-start">
                                            <div>
                                                <span class="fw-medium">{{ str_replace('.', ' ', ucfirst($event->event_type->value)) }}</span>
                                                @if(! empty($event->metadata['label']))
                                                    <span class="text-muted"> — {{ $event->metadata['label'] }}</span>
                                                @endif
                                            </div>
                                            <small class="text-muted">{{ $event->occurred_at->diffForHumans() }}</small>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
