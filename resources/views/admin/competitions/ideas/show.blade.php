@php
    $registration = $idea->registration;
    $school = $registration?->school;
    $statusLabel = ucfirst(str_replace('_', ' ', (string) $idea->status));
    $statusBadge = match ($idea->status) {
        'shortlisted' => 'success',
        'not_shortlisted' => 'danger',
        'submitted' => 'primary',
        'draft' => 'secondary',
        default => 'secondary',
    };
    $smsCanSend = filled($smsPhoneNormalized ?? null);
    $smsPhoneDisplay = $smsPhoneNormalized ?? ($registration?->resolvedTeacherPhone());
    $smsSenderOptions = $smsAllowedSenders ?? ['ADILISHA', 'ADILISHAXR', 'STEMLABS'];
    $smsSenderDefault = $smsDefaultSender ?? 'ADILISHA';
    $alreadyNotified = (bool) ($shortlistAlreadyNotified ?? false);
    $canRemind = (bool) ($canSendShortlistReminder ?? false);
    $shortlistDecision = old('status', $idea->shortlist?->status ?? 'shortlisted');
    $showShortlistNotify = ! $alreadyNotified && $shortlistDecision === 'shortlisted';
    $teacherEmail = $registration?->resolvedTeacherEmail();
    $maxShortlistReminders = \App\Models\CompetitionShortlist::maxReminders();
    $pendingIdeaCount = (int) ($pendingIdeaCount ?? 0);
@endphp

<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('admin.competitions.index') }}">Competitions</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.competitions.show', $competition) }}">{{ $competition->name }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.competitions.ideas.index', $competition) }}">Ideas</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ \Illuminate\Support\Str::limit($idea->problem_title ?: 'Untitled', 40) }}</li>
                        </ol>
                    </nav>
                    <h4 class="mb-2">{{ $idea->problem_title ?: 'Untitled idea' }}</h4>
                    <p class="mb-0">
                        <span class="badge bg-{{ $statusBadge }}">{{ $statusLabel }}</span>
                        @if($idea->submitted_at)
                            <span class="ms-2 text-muted small">Submitted {{ $idea->submitted_at->timezone(config('app.timezone'))->format('j M Y, H:i') }}</span>
                        @endif
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.competitions.ideas.index', $competition) }}" class="btn btn-outline-secondary">Back to list</a>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scoreFromShowModal">Score</button>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#shortlistFromShowModal">Shortlist</button>
                    @if($canRemind)
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#shortlistReminderModal">
                            Shortlist reminder
                            @if(($idea->shortlist?->reminder_count ?? 0) > 0)
                                ({{ $idea->shortlist->reminder_count }}/{{ $maxShortlistReminders }})
                            @endif
                        </button>
                    @elseif($alreadyNotified && $idea->shortlist?->status === 'shortlisted')
                        <button type="button" class="btn btn-outline-secondary" disabled>Reminder limit reached</button>
                    @endif
                </div>
            </div>

            @if($pendingIdeaCount > 0)
                <div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4" role="status">
                    <div>
                        <strong>{{ $pendingIdeaCount }}</strong>
                        {{ $pendingIdeaCount === 1 ? 'school has' : 'schools have' }}
                        not submitted an idea yet. You can view them and send email/SMS reminders.
                    </div>
                    <a href="{{ route('admin.competitions.ideas.index', [$competition, 'tab' => 'pending']) }}" class="btn btn-sm btn-warning">
                        View &amp; remind
                    </a>
                </div>
            @endif

            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <strong>School</strong>
                            @if($registration)
                                <a href="{{ route('admin.competitions.registrations.show', [$competition, $registration]) }}" class="small">Registration</a>
                            @endif
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-4">School</dt>
                                <dd class="col-sm-8">{{ $registration?->resolvedSchoolName() ?: ($school?->name ?? '—') }}</dd>
                                <dt class="col-sm-4">Teacher</dt>
                                <dd class="col-sm-8">{{ $registration?->teacher_name ?? '—' }}</dd>
                                <dt class="col-sm-4">Teacher email</dt>
                                <dd class="col-sm-8 text-break">{{ $teacherEmail ?: '—' }}</dd>
                                <dt class="col-sm-4">Teacher phone</dt>
                                <dd class="col-sm-8"><x-whatsapp-phone :phone="$registration?->teacher_phone" /></dd>
                                @if($registration?->teacherClub?->club_code)
                                    <dt class="col-sm-4">Club code</dt>
                                    <dd class="col-sm-8"><code>{{ $registration->teacherClub->club_code }}</code></dd>
                                @endif
                                <dt class="col-sm-4">Registration region</dt>
                                <dd class="col-sm-8">{{ $registration?->region?->name ?? '—' }}</dd>
                                <dt class="col-sm-4">Registration status</dt>
                                <dd class="col-sm-8 mb-0">{{ $registration ? ucfirst($registration->status) : '—' }}</dd>
                                @if($school)
                                    <dt class="col-sm-4 mt-2">School location</dt>
                                    <dd class="col-sm-8 mt-2 text-break">
                                        @if($school->latitude !== null && $school->longitude !== null)
                                            <a href="https://www.google.com/maps?q={{ $school->latitude }},{{ $school->longitude }}" target="_blank" rel="noopener noreferrer">
                                                {{ $school->latitude }}, {{ $school->longitude }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </dd>
                                    <dt class="col-sm-4">District / ward</dt>
                                    <dd class="col-sm-8">{{ $school->districtName() ?? '—' }} / {{ $school->ward ?? '—' }}</dd>
                                    <dt class="col-sm-4">School region</dt>
                                    <dd class="col-sm-8 mb-0">{{ $school->regionModel?->name ?? $school->region ?? '—' }}</dd>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header"><strong>Idea meta</strong></div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-5">STEM discipline</dt>
                                <dd class="col-sm-7">{{ $idea->stem_category ?: '—' }}</dd>
                                <dt class="col-sm-5">Community region</dt>
                                <dd class="col-sm-7">{{ $idea->communityRegion?->name ?? '—' }}</dd>
                                <dt class="col-sm-5">Community district</dt>
                                <dd class="col-sm-7">{{ $idea->communityDistrict?->name ?? ($idea->community_location ?: '—') }}</dd>
                                <dt class="col-sm-5">Submitted</dt>
                                <dd class="col-sm-7">{{ $idea->submitted_at?->timezone(config('app.timezone'))->format('j M Y, H:i') ?? '—' }}</dd>
                                <dt class="col-sm-5">Shortlist</dt>
                                <dd class="col-sm-7">
                                    @if($idea->shortlist)
                                        <span class="badge bg-{{ $idea->shortlist->status === 'shortlisted' ? 'success' : ($idea->shortlist->status === 'waitlist' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($idea->shortlist->status) }}
                                        </span>
                                        @if($idea->shortlist->rank)
                                            <span class="ms-1">Rank {{ $idea->shortlist->rank }}</span>
                                        @endif
                                        @if($idea->shortlist->notified_at)
                                            <div class="mt-1">Notified {{ $idea->shortlist->notified_at->timezone(config('app.timezone'))->format('j M Y, H:i') }}</div>
                                        @endif
                                    @else
                                        Pending
                                    @endif
                                </dd>
                                @if($idea->reopened_at)
                                    <dt class="col-sm-5">Reopened</dt>
                                    <dd class="col-sm-7 mb-0">
                                        {{ $idea->reopened_at->timezone(config('app.timezone'))->format('j M Y, H:i') }}
                                        @if($idea->reopenedBy)
                                            by {{ $idea->reopenedBy->name }}
                                        @endif
                                    </dd>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <strong>Students</strong>
                            <span class="badge bg-light text-dark">{{ $registration?->representativeStudents->count() ?? 0 }}</span>
                        </div>
                        <div class="card-body p-0">
                            @if($registration && $registration->representativeStudents->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0 align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>No</th>
                                                <th>Admission No.</th>
                                                <th>Name</th>
                                                <th>Gender</th>
                                                <th>Class</th>
                                                <th>Role</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($registration->representativeStudents as $rs)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td><code>{{ $rs->student?->admission_number ?? '—' }}</code></td>
                                                    <td>{{ $rs->student?->name ?? '—' }}</td>
                                                    <td>{{ $rs->student?->gender ? ucfirst((string) $rs->student->gender) : '—' }}</td>
                                                    <td>{{ $rs->student?->schoolClass?->name ?? '—' }}</td>
                                                    <td>{{ $rs->role ?: '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted mb-0 p-3">No representatives linked.</p>
                            @endif
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header"><strong>Judge scores</strong></div>
                        <div class="card-body p-0">
                            @if($idea->scores->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0 align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Judge</th>
                                                <th>STEM</th>
                                                <th>Potential</th>
                                                <th>Relevance</th>
                                                <th>Creativity</th>
                                                <th>Total</th>
                                                <th>Feedback</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($idea->scores as $score)
                                                <tr>
                                                    <td>{{ $score->judge?->name ?? '—' }}</td>
                                                    <td>{{ $score->stem_score }}</td>
                                                    <td>{{ $score->product_potential_score }}</td>
                                                    <td>{{ $score->community_relevance_score }}</td>
                                                    <td>{{ $score->creativity_score }}</td>
                                                    <td class="fw-semibold">{{ $score->total_score }}</td>
                                                    <td class="text-break">{{ $score->feedback ?: '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted mb-0 p-3">No scores yet.</p>
                            @endif
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header"><strong>Feedback for the school</strong></div>
                        <div class="card-body">
                            <p class="mb-3">Shown at the top of this school’s prototype page. Leave it blank to show nothing there.</p>
                            <form method="POST" action="{{ route('admin.competitions.ideas.feedback', [$competition, $idea]) }}">
                                @csrf
                                <label class="form-label" for="admin_feedback">Comment</label>
                                <textarea
                                    class="form-control @error('admin_feedback') is-invalid @enderror"
                                    name="admin_feedback"
                                    id="admin_feedback"
                                    rows="5"
                                    maxlength="5000"
                                >{{ old('admin_feedback', $idea->admin_feedback) }}</textarea>
                                @error('admin_feedback')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <button class="btn btn-primary mt-3" type="submit">Save feedback</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card mb-4">
                        <div class="card-header"><strong>Problem statement</strong></div>
                        <div class="card-body">
                            <div class="text-break">{!! nl2br(e($idea->problem_statement ?? '')) ?: '—' !!}</div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header"><strong>Proposed solution</strong></div>
                        <div class="card-body">
                            <div class="text-break">{!! nl2br(e($idea->proposed_solution ?? '')) ?: '—' !!}</div>
                        </div>
                    </div>

                    @if(filled($idea->supporting_notes))
                        <div class="card mb-4">
                            <div class="card-header"><strong>Supporting notes</strong></div>
                            <div class="card-body">
                                <div class="text-break">{!! nl2br(e($idea->supporting_notes)) !!}</div>
                            </div>
                        </div>
                    @endif

                    @if($idea->attachments_meta && count((array) $idea->attachments_meta) > 0)
                        <div class="card mb-4">
                            <div class="card-header"><strong>Attachments</strong></div>
                            <div class="card-body">
                                <pre class="mb-0 bg-light p-3 rounded border">{{ json_encode($idea->attachments_meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="modal fade" id="scoreFromShowModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Score idea</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="{{ route('admin.competitions.ideas.score', [$competition, $idea]) }}">
                            @csrf
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">STEM</label>
                                        <select name="stem_score" class="form-select" required>
                                            @foreach([1,2,3,4,5] as $v)
                                                <option value="{{ $v }}">{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Potential</label>
                                        <select name="product_potential_score" class="form-select" required>
                                            @foreach([1,2,3,4,5] as $v)
                                                <option value="{{ $v }}">{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Relevance</label>
                                        <select name="community_relevance_score" class="form-select" required>
                                            @foreach([1,2,3,4,5] as $v)
                                                <option value="{{ $v }}">{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Creativity</label>
                                        <select name="creativity_score" class="form-select" required>
                                            @foreach([1,2,3,4,5] as $v)
                                                <option value="{{ $v }}">{{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Feedback</label>
                                        <textarea class="form-control" name="feedback" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-primary" type="submit">Save score</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="shortlistFromShowModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Shortlist decision</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="{{ route('admin.competitions.ideas.shortlist', [$competition, $idea]) }}" class="js-prevent-double-submit">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label" for="shortlist-status">Decision</label>
                                    <select name="status" id="shortlist-status" class="form-select" required>
                                        <option value="shortlisted" @selected(old('status', $idea->shortlist?->status) === 'shortlisted')>Shortlisted</option>
                                        <option value="waitlist" @selected(old('status', $idea->shortlist?->status) === 'waitlist')>Waitlist</option>
                                        <option value="rejected" @selected(old('status', $idea->shortlist?->status) === 'rejected')>Rejected</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Regional rank (optional)</label>
                                    <input class="form-control" type="number" name="rank" min="1" value="{{ old('rank', $idea->shortlist?->rank) }}">
                                </div>

                                <div id="shortlist-already-notified" class="alert alert-info {{ ($alreadyNotified && $shortlistDecision === 'shortlisted') ? '' : 'd-none' }}">
                                    A shortlist notification was already sent. Saving again will not send another email or SMS. Use Reminder when you want to send it again.
                                </div>

                                <div id="shortlist-notify-panel" class="{{ $showShortlistNotify ? '' : 'd-none' }}" data-already-notified="{{ $alreadyNotified ? '1' : '0' }}">
                                    <p class="mb-3">
                                        The first time you save Shortlisted, the teacher receives the standard shortlist email and the SMS below.
                                        Email:
                                        @if(filled($teacherEmail))
                                            <strong>{{ $teacherEmail }}</strong>
                                        @else
                                            <strong class="text-danger">No teacher email — email will be skipped</strong>
                                        @endif
                                    </p>
                                    @include('admin.competitions.registrations.partials.sms-preview-fields', [
                                        'prefix' => 'shortlist',
                                        'phoneDisplay' => $smsPhoneDisplay,
                                        'canSendSms' => $smsCanSend,
                                        'message' => old('sms_message', $smsShortlistPreview ?? ''),
                                        'senderDefault' => $smsSenderDefault,
                                        'senderOptions' => $smsSenderOptions,
                                    ])
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-success" type="submit" data-loading-text="Saving…">Save decision</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            @if($canRemind)
                <div class="modal fade" id="shortlistReminderModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Send shortlist reminder</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="{{ route('admin.competitions.ideas.shortlist-reminder', [$competition, $idea]) }}" class="js-prevent-double-submit">
                                @csrf
                                <div class="modal-body">
                                    <p class="mb-3">
                                        This sends another email and SMS. It does not run when you only save the shortlist decision.
                                        Email:
                                        @if(filled($teacherEmail))
                                            <strong>{{ $teacherEmail }}</strong>
                                        @else
                                            <strong class="text-danger">No teacher email — email will be skipped</strong>
                                        @endif
                                    </p>
                                    @include('admin.competitions.registrations.partials.sms-preview-fields', [
                                        'prefix' => 'shortlist-reminder',
                                        'phoneDisplay' => $smsPhoneDisplay,
                                        'canSendSms' => $smsCanSend,
                                        'message' => old('sms_message', $smsReminderPreview ?? ''),
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
            document.addEventListener('DOMContentLoaded', function () {
                const select = document.getElementById('shortlist-status');
                const panel = document.getElementById('shortlist-notify-panel');
                const alreadyNote = document.getElementById('shortlist-already-notified');
                if (select && panel) {
                    const already = panel.dataset.alreadyNotified === '1';
                    const sync = function () {
                        const shortlisting = select.value === 'shortlisted';
                        panel.classList.toggle('d-none', already || !shortlisting);
                        if (alreadyNote) {
                            alreadyNote.classList.toggle('d-none', !(already && shortlisting));
                        }
                    };
                    select.addEventListener('change', sync);
                    sync();
                }

                document.querySelectorAll('form.js-prevent-double-submit').forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (form.dataset.submitting === '1') {
                            event.preventDefault();
                            return;
                        }
                        form.dataset.submitting = '1';
                        const submitBtn = form.querySelector('button[type="submit"]');
                        const loadingText = submitBtn?.getAttribute('data-loading-text') || 'Saving…';
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
            });
        </script>
    @endpush
</x-admin-layout>
