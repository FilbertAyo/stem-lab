<x-app-layout>
    <div class="col-lg-9">

        <div class="dashboard-content-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h2 class="title mb-1">My Competition Submissions</h2>
            </div>
            <div>
                <a href="{{ route('client.competitions.index') }}" class="btn btn-primary text-white btn-sm">
                    Browse Competitions
                </a>
            </div>  
        </div>

        <section class="row">
            <div class="col-12">
                @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($participants->isEmpty())
                    <div class="card border shadow-none">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-file-earmark-text display-4 text-muted mb-3"></i>
                            <h5 class="fw-semibold mb-2">No registrations yet</h5>
                            <p class="text-muted mb-3">
                                You haven't registered for any competitions yet.
                            </p>
                            <a href="{{ route('client.competitions.index') }}" class="btn btn-primary text-white btn-sm">
                                <i class="bi bi-trophy me-1"></i> Browse Competitions
                            </a>
                        </div>
                    </div>
                @else
                    @foreach($participants as $participant)
                        <div class="card border shadow-none mb-4">
                            <div class="card-header bg-light-soft d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">{{ $participant->competition->name }}</h5>
                                    <small class="text-muted">{{ $participant->competition->club->name }}</small>
                                </div>
                                <div>
                                    <span class="badge bg-{{ $participant->status_badge }} me-2">{{ ucfirst($participant->status) }}</span>
                                    @if($participant->award)
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-trophy me-1"></i>{{ $participant->award->title }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <h6 class="small text-muted mb-2">Team Information</h6>
                                        <p class="mb-1"><strong>Team:</strong> {{ $participant->team_name ?: 'Team #'.$participant->id }}</p>
                                        <p class="mb-1"><strong>Registered:</strong> {{ $participant->registered_at->format('d M Y') }}</p>
                                        @if($participant->final_score !== null)
                                            <p class="mb-1"><strong>Score:</strong> {{ number_format($participant->final_score, 1) }}/100</p>
                                        @endif
                                        @if($participant->rank)
                                            <p class="mb-1"><strong>Rank:</strong> #{{ $participant->rank }}</p>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="small text-muted mb-2">Students ({{ $participant->participantStudents->count() }})</h6>
                                        <ul class="list-unstyled small mb-0">
                                            @foreach($participant->participantStudents->take(5) as $ps)
                                                <li>
                                                    <i class="bi bi-person text-muted me-1"></i>{{ $ps->student->name }}
                                                    @if($ps->role) <span class="text-muted">({{ $ps->role }})</span> @endif
                                                </li>
                                            @endforeach
                                            @if($participant->participantStudents->count() > 5)
                                                <li class="text-muted">... and {{ $participant->participantStudents->count() - 5 }} more</li>
                                            @endif
                                        </ul>
                                    </div>
                                    <div class="col-md-4">
                                        <h6 class="small text-muted mb-2">Submissions ({{ $participant->submissions->count() }})</h6>
                                        @if($participant->submissions->isEmpty())
                                            <p class="text-muted small mb-2">No submissions yet.</p>
                                            @if($participant->status === 'approved' && $participant->competition->canAcceptSubmissions())
                                                <a href="{{ route('client.competitions.submit', $participant->competition) }}" class="btn btn-sm btn-success text-white">
                                                    <i class="bi bi-upload me-1"></i> Submit Now
                                                </a>
                                            @endif
                                        @else
                                            @foreach($participant->submissions as $submission)
                                                <div class="border rounded p-2 mb-2 small">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <strong>{{ Str::limit($submission->title, 25) }}</strong>
                                                        <span class="badge bg-{{ $submission->status_badge }}">{{ ucfirst(str_replace('_', ' ', $submission->status)) }}</span>
                                                    </div>
                                                    @if($submission->score !== null)
                                                        <div class="mt-1">
                                                            <span class="badge bg-primary">Score: {{ number_format($submission->score, 1) }}</span>
                                                        </div>
                                                    @endif
                                                    @if($submission->judge_feedback)
                                                        <div class="mt-1 text-muted" style="font-size: 0.75rem;">
                                                            <strong>Feedback:</strong> {{ Str::limit($submission->judge_feedback, 50) }}
                                                        </div>
                                                    @endif
                                                    <div class="mt-1 text-muted">
                                                        {{ $submission->attachments->count() }} attachment(s)
                                                    </div>
                                                </div>
                                            @endforeach
                                            @if($participant->status === 'approved' && $participant->competition->canAcceptSubmissions())
                                                <a href="{{ route('client.competitions.submit', $participant->competition) }}" class="btn btn-sm btn-primary text-white">
                                                    <i class="bi bi-pencil me-1"></i> Edit Submission
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                @if($participant->rejection_reason)
                                    <div class="alert alert-danger mt-3 mb-0 small">
                                        <strong>Reason:</strong> {{ $participant->rejection_reason }}
                                    </div>
                                @endif
                            </div>
                            <div class="card-footer bg-transparent">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar me-1"></i>
                                        Competition: {{ $participant->competition->competition_start->format('d M') }} - {{ $participant->competition->competition_end->format('d M Y') }}
                                    </small>
                                    <a href="{{ route('client.competitions.show', $participant->competition) }}" class="btn btn-sm btn-outline-primary">
                                        View Competition <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </section>
    </div>
</x-app-layout>

