@php
    $maxIdeaReminders = \App\Models\CompetitionRegistration::maxIdeaSubmissionReminders();
    $canRemind = $registration->canSendIdeaSubmissionReminder();
    $smsCanSend = filled($smsPhoneNormalized ?? null);
    $smsPhoneDisplay = $smsPhoneNormalized ?? $registration->resolvedTeacherPhone();
    $smsSenderOptions = $smsAllowedSenders ?? ['ADILISHA', 'ADILISHAXR', 'STEMLABS'];
    $smsSenderDefault = $smsDefaultSender ?? 'ADILISHA';
    $teacherEmail = $registration->resolvedTeacherEmail();
    $school = $registration->school;
@endphp

<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('admin.competitions.index') }}">Competitions</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.competitions.show', $competition) }}">{{ $competition->name }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.competitions.ideas.index', [$competition, 'tab' => 'pending']) }}">Ideas</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Not submitted</li>
                        </ol>
                    </nav>
                    <h4 class="mb-2">{{ $registration->resolvedSchoolName() }}</h4>
                    <p class="mb-0">
                        <span class="badge bg-warning text-dark">Idea not submitted</span>
                        @if($registration->idea)
                            <span class="badge bg-secondary ms-1">Draft saved</span>
                        @endif
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.competitions.ideas.index', [$competition, 'tab' => 'pending']) }}" class="btn btn-outline-secondary">Back to list</a>
                    @if($canRemind)
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#ideaSubmissionReminderModal">
                            Send reminder
                            @if($registration->idea_submission_reminder_count > 0)
                                ({{ $registration->idea_submission_reminder_count }}/{{ $maxIdeaReminders }})
                            @endif
                        </button>
                    @elseif(! filled($teacherEmail) && ! filled($registration->resolvedTeacherPhone()))
                        <button type="button" class="btn btn-outline-secondary" disabled>No teacher contact</button>
                    @else
                        <button type="button" class="btn btn-outline-secondary" disabled>
                            Reminder limit reached ({{ $registration->idea_submission_reminder_count }}/{{ $maxIdeaReminders }})
                        </button>
                    @endif
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card mb-4">
                        <div class="card-header"><strong>School</strong></div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-4">School</dt>
                                <dd class="col-sm-8">{{ $registration->resolvedSchoolName() ?: '—' }}</dd>
                                <dt class="col-sm-4">Teacher</dt>
                                <dd class="col-sm-8">{{ $registration->resolvedTeacherName() ?: '—' }}</dd>
                                <dt class="col-sm-4">Teacher email</dt>
                                <dd class="col-sm-8 text-break">{{ $teacherEmail ?: '—' }}</dd>
                                <dt class="col-sm-4">Teacher phone</dt>
                                <dd class="col-sm-8"><x-whatsapp-phone :phone="$registration->resolvedTeacherPhone()" /></dd>
                                @if($registration->teacherClub?->club_code)
                                    <dt class="col-sm-4">Club code</dt>
                                    <dd class="col-sm-8"><code>{{ $registration->teacherClub->club_code }}</code></dd>
                                @endif
                                <dt class="col-sm-4">Region</dt>
                                <dd class="col-sm-8">{{ $registration->region?->name ?? $school?->regionModel?->name ?? '—' }}</dd>
                                <dt class="col-sm-4">Approved</dt>
                                <dd class="col-sm-8 mb-0">
                                    {{ $registration->approved_at?->timezone(config('app.timezone'))->format('j M Y, H:i') ?? '—' }}
                                </dd>
                            </dl>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header"><strong>Reminder status</strong></div>
                        <div class="card-body">
                            <p class="mb-1">
                                <strong>{{ $registration->idea_submission_reminder_count }}</strong> / {{ $maxIdeaReminders }} reminders sent
                            </p>
                            @if($registration->last_idea_submission_reminder_sent_at)
                                <p class="text-muted mb-0 small">
                                    Last sent {{ $registration->last_idea_submission_reminder_sent_at->timezone(config('app.timezone'))->format('j M Y, H:i') }}
                                </p>
                            @else
                                <p class="text-muted mb-0 small">No idea submission reminder sent yet.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card mb-4">
                        <div class="card-header"><strong>Idea draft</strong></div>
                        <div class="card-body">
                            @if($registration->idea)
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Title</dt>
                                    <dd class="col-sm-8">{{ $registration->idea->problem_title ?: 'Untitled draft' }}</dd>
                                    <dt class="col-sm-4">STEM discipline</dt>
                                    <dd class="col-sm-8">{{ $registration->idea->stem_category ?: '—' }}</dd>
                                    <dt class="col-sm-4">Status</dt>
                                    <dd class="col-sm-8 mb-0"><span class="badge bg-secondary">Draft</span></dd>
                                </dl>
                                @if(filled($registration->idea->problem_statement))
                                    <hr>
                                    <h6 class="text-muted text-uppercase small">Problem statement</h6>
                                    <div class="text-break">{!! nl2br(e($registration->idea->problem_statement)) !!}</div>
                                @endif
                            @else
                                <p class="text-muted mb-0">This school has not started an idea draft yet.</p>
                            @endif
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <strong>Students</strong>
                            <span class="badge bg-light text-dark">{{ $registration->representativeStudents->count() }}</span>
                        </div>
                        <div class="card-body p-0">
                            @if($registration->representativeStudents->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0 align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>No</th>
                                                <th>Name</th>
                                                <th>Gender</th>
                                                <th>Class</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($registration->representativeStudents as $rs)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $rs->student?->name ?? '—' }}</td>
                                                    <td>{{ $rs->student?->gender ? ucfirst((string) $rs->student->gender) : '—' }}</td>
                                                    <td>{{ $rs->student?->schoolClass?->name ?? '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted mb-0 p-3">No representatives linked yet.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if($canRemind)
                <div class="modal fade" id="ideaSubmissionReminderModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Send idea submission reminder</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="{{ route('admin.competitions.ideas.submission-reminder', [$competition, $registration]) }}" class="js-prevent-double-submit">
                                @csrf
                                <div class="modal-body">
                                    <p class="mb-3">
                                        Email:
                                        @if(filled($teacherEmail))
                                            <strong>{{ $teacherEmail }}</strong>
                                        @else
                                            <strong class="text-danger">No teacher email — email will be skipped</strong>
                                        @endif
                                    </p>
                                    @include('admin.competitions.registrations.partials.sms-preview-fields', [
                                        'prefix' => 'idea-submission-reminder',
                                        'phoneDisplay' => $smsPhoneDisplay,
                                        'canSendSms' => $smsCanSend,
                                        'message' => old('sms_message', $smsIdeaSubmissionPreview ?? ''),
                                        'senderDefault' => $smsSenderDefault,
                                        'senderOptions' => $smsSenderOptions,
                                    ])
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                                    <button class="btn btn-warning" type="submit" data-loading-text="Sending reminder…">Send reminder</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('form.js-prevent-double-submit').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (form.dataset.submitting === '1') {
                        event.preventDefault();
                        return;
                    }
                    form.dataset.submitting = '1';
                    const submitBtn = form.querySelector('button[type="submit"]');
                    const loadingText = submitBtn?.getAttribute('data-loading-text') || 'Sending…';
                    if (submitBtn) {
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' + loadingText;
                    }
                    window.setTimeout(function () {
                        if (submitBtn) {
                            submitBtn.disabled = true;
                        }
                    }, 0);
                });
            });
        </script>
    @endpush
</x-admin-layout>
