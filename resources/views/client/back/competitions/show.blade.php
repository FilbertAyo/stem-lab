<x-app-layout>
    <div class="col-lg-9">

        <div class="dashboard-content-header mb-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('client.competitions.index') }}">Competitions</a></li>
                    <li class="breadcrumb-item active">{{ $competition->name }}</li>
                </ol>
            </nav>
            <h2 class="title mb-0">{{ $competition->name }}</h2>
        </div>

        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="background-color: #d4edda; border-color: #c3e6cb;">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="background-color: #f8d7da; border-color: #f5c6cb;" >
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Cover Image -->
                @if($competition->cover_image)
                    <div class="card border-0 shadow-none mb-4">
                        <img src="{{ Storage::url($competition->cover_image) }}" 
                             class="card-img-top rounded" 
                             alt="{{ $competition->name }}"
                             style="max-height: 350px; object-fit: cover;">
                    </div>
                @endif

                <!-- Description -->
                <div class="card border-0 shadow-none mb-4">
                    <div class="card-body">
                        <h5 class="card-title fw-semibold mb-3">About this Competition</h5>
                        <p class="card-text">{{ $competition->description ?: 'No description provided.' }}</p>
                    </div>
                </div>

                <!-- Terms & Conditions -->
                @if($competition->terms_and_conditions)
                    <div class="card border-0 shadow-none mb-4">
                        <div class="card-header bg-transparent">
                            <h6 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Terms and Conditions</h6>
                        </div>
                        <div class="card-body">
                            <div class="prose">{!! nl2br(e($competition->terms_and_conditions)) !!}</div>
                        </div>
                    </div>
                @endif

                <!-- Instructions -->
                @if($competition->instructions)
                    <div class="card border-0 shadow-none mb-4">
                        <div class="card-header bg-transparent">
                            <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>How to Participate</h6>
                        </div>
                        <div class="card-body">
                            <div class="prose">{!! nl2br(e($competition->instructions)) !!}</div>
                        </div>
                    </div>
                @endif

                <!-- Awards -->
                @if($competition->awards->count() > 0)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-transparent">
                            <h6 class="mb-0"><i class="bi bi-trophy me-2"></i>Awards</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                @foreach($competition->awards as $award)
                                    <div class="col-md-4">
                                        <div class="text-center p-3 border rounded {{ $award->position <= 3 ? 'border-warning' : '' }}">
                                            @if($award->prize_image)
                                                <img src="{{ Storage::url($award->prize_image) }}" alt="{{ $award->title }}" class="mb-2" style="max-height: 60px;">
                                            @else
                                                <i class="bi bi-award display-5 {{ $award->position == 1 ? 'text-warning' : ($award->position == 2 ? 'text-secondary' : 'text-danger') }}"></i>
                                            @endif
                                            <h6 class="fw-semibold mb-1">{{ $award->title }}</h6>
                                            <small class="text-muted">{{ $award->position_label }}</small>
                                            @if($award->prize_value)
                                                <div class="fw-bold text-success mt-1">{{ number_format($award->prize_value) }} TZS</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- My Registration Status -->
                @if($existingParticipant)
                    <div class="card border-0 shadow-none mb-4 border-start border-4 border-{{ $existingParticipant->status_badge }}">
                        <div class="card-header bg-transparent">
                            <h6 class="mb-0"><i class="bi bi-check-circle me-2"></i>Your Registration</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>Team:</strong> {{ $existingParticipant->team_name ?: 'Team #'.$existingParticipant->id }}</p>
                                    <p class="mb-2">
                                        <strong>Status:</strong> 
                                        <span class="badge bg-{{ $existingParticipant->status_badge }}">{{ ucfirst($existingParticipant->status) }}</span>
                                    </p>
                                    @if($existingParticipant->rejection_reason)
                                        <p class="text-danger mb-2"><strong>Reason:</strong> {{ $existingParticipant->rejection_reason }}</p>
                                    @endif
                                    @if($existingParticipant->award)
                                        <p class="mb-2"><strong>Award:</strong> <span class="badge bg-warning text-dark">{{ $existingParticipant->award->title }}</span></p>
                                    @endif
                                    @if($existingParticipant->final_score !== null)
                                        <p class="mb-2"><strong>Score:</strong> {{ number_format($existingParticipant->final_score, 1) }}/100</p>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>Students ({{ $existingParticipant->participantStudents->count() }}):</strong></p>
                                    <ul class="list-unstyled mb-0">
                                        @foreach($existingParticipant->participantStudents as $ps)
                                            <li class="mb-1">
                                                <i class="bi bi-person text-muted"></i> {{ $ps->student->name }}
                                                @if($ps->role) <small class="text-muted">({{ $ps->role }})</small> @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>

                            <!-- Submissions -->
                            @if($existingParticipant->submissions->count() > 0)
                                <hr>
                                <h6>Your Submissions</h6>
                                @foreach($existingParticipant->submissions as $submission)
                                    <div class="border rounded p-3 mb-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $submission->title }}</strong>
                                                <span class="badge bg-{{ $submission->status_badge }} ms-2">{{ ucfirst(str_replace('_', ' ', $submission->status)) }}</span>
                                                @if($submission->score !== null)
                                                    <span class="badge bg-primary ms-1">Score: {{ number_format($submission->score, 1) }}</span>
                                                @endif
                                            </div>
                                            <small class="text-muted">{{ $submission->submitted_at?->format('d M Y') ?? 'Draft' }}</small>
                                        </div>
                                        @if($submission->judge_feedback)
                                            <div class="alert alert-info mt-2 mb-0 small">
                                                <strong>Feedback:</strong> {{ $submission->judge_feedback }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @endif

                            @if($existingParticipant->status === 'approved' && $competition->canAcceptSubmissions())
                                <div class="mt-3">
                                    <a href="{{ route('client.competitions.submit', $competition) }}" class="btn btn-success">
                                        <i class="bi bi-upload me-1"></i> 
                                        {{ $existingParticipant->submissions->count() > 0 ? 'Update Submission' : 'Submit Your Work' }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Quick Info -->
                <div class="card border-0 shadow-none mb-4">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Quick Info</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Club</span>
                                <span class="badge bg-info">{{ $competition->club->name }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Status</span>
                                <span class="badge bg-{{ $competition->status_badge }}">{{ ucfirst($competition->status) }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Team Size</span>
                                <span>{{ $competition->min_members }}-{{ $competition->max_members ?? '∞' }} members</span>
                            </li>
                            @if($competition->min_age || $competition->max_age)
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">Age Range</span>
                                    <span>{{ $competition->min_age ?? 'Any' }}-{{ $competition->max_age ?? 'Any' }} years</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="card border-0 shadow-none mb-4">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Timeline</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            @if($competition->registration_start)
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">Registration Opens</span>
                                    <span>{{ $competition->registration_start->format('d M Y') }}</span>
                                </li>
                            @endif
                            @if($competition->registration_deadline)
                                <li class="d-flex justify-content-between py-2 border-bottom">
                                    <span class="text-muted">Registration Closes</span>
                                    <span>{{ $competition->registration_deadline->format('d M Y') }}</span>
                                </li>
                            @endif
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Competition Start</span>
                                <span class="text-success fw-semibold">{{ $competition->competition_start->format('d M Y') }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">Competition End</span>
                                <span class="text-danger fw-semibold">{{ $competition->competition_end->format('d M Y') }}</span>
                            </li>
                            @if($competition->results_announcement_date)
                                <li class="d-flex justify-content-between py-2">
                                    <span class="text-muted">Results</span>
                                    <span>{{ $competition->results_announcement_date->format('d M Y') }}</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- Registration Action -->
                <div class="card border-0 shadow-none">
                    <div class="card-body">
                        @if($existingParticipant)
                            <div class="text-center">
                                <i class="bi bi-check-circle-fill text-success display-6"></i>
                                <p class="mb-0 mt-2">You are registered for this competition</p>
                            </div>
                        @elseif($teacherClubs->isEmpty())
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                You need to join <strong>{{ $competition->club->name }}</strong> club first.
                                <a href="{{ route('client.clubs.index') }}" class="alert-link">Join Now</a>
                            </div>
                        @elseif(!$competition->isRegistrationOpen())
                            <div class="alert alert-secondary mb-0">
                                <i class="bi bi-clock me-2"></i>
                                Registration is currently closed.
                            </div>
                        @else
                            <a href="{{ route('client.competitions.register', $competition) }}" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-person-plus me-2"></i> Register Now
                            </a>
                            <small class="text-muted d-block text-center mt-2">
                                Registration deadline: {{ $competition->registration_deadline?->format('d M Y, H:i') ?? 'No deadline' }}
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

