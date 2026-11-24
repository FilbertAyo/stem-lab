@php
    $tab = $tab ?? 'submitted';
    $maxIdeaReminders = \App\Models\CompetitionRegistration::maxIdeaSubmissionReminders();
@endphp

<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h5 class="mb-0">Ideas — {{ $competition->name }}</h5>
                    <p class="text-muted mb-0">Submitted ideas for scoring, plus schools that still need to submit.</p>
                </div>
                <a href="{{ route('admin.competitions.show', $competition) }}" class="btn btn-outline-secondary">Back</a>
            </div>

            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <a
                        class="nav-link {{ $tab === 'submitted' ? 'active' : '' }}"
                        href="{{ route('admin.competitions.ideas.index', [$competition, 'tab' => 'submitted']) }}"
                        role="tab"
                        aria-selected="{{ $tab === 'submitted' ? 'true' : 'false' }}"
                    >
                        Submitted ideas
                        <span class="badge {{ $tab === 'submitted' ? 'bg-primary' : 'bg-light text-dark' }} ms-1">{{ $submittedCount }}</span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a
                        class="nav-link {{ $tab === 'pending' ? 'active' : '' }}"
                        href="{{ route('admin.competitions.ideas.index', [$competition, 'tab' => 'pending']) }}"
                        role="tab"
                        aria-selected="{{ $tab === 'pending' ? 'true' : 'false' }}"
                    >
                        Not submitted
                        <span class="badge {{ $tab === 'pending' ? 'bg-warning text-dark' : 'bg-light text-dark' }} ms-1">{{ $pendingCount }}</span>
                    </a>
                </li>
            </ul>

            @if($tab === 'pending')
                <div class="card">
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            Approved schools with no final idea submission. Open a school to view details, or send an email and SMS reminder.
                        </p>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>School</th>
                                        <th>Teacher</th>
                                        <th>Region</th>
                                        <th>Draft</th>
                                        <th>Reminders</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pendingRegistrations as $registration)
                                        @php
                                            $reminderCount = (int) $registration->idea_submission_reminder_count;
                                            $canRemind = $registration->canSendIdeaSubmissionReminder();
                                            $smsMeta = $ideaSubmissionSmsPreviews[$registration->id] ?? null;
                                            $smsCanSend = filled($smsMeta['normalized'] ?? null);
                                            $smsPhoneDisplay = $smsMeta['normalized'] ?? ($smsMeta['phone'] ?? null);
                                        @endphp
                                        <tr>
                                            <td>{{ $pendingRegistrations->firstItem() + $loop->index }}</td>
                                            <td>
                                                <div class="fw-semibold">{{ $registration->resolvedSchoolName() }}</div>
                                                @if($registration->teacherClub?->club_code)
                                                    <small class="text-muted">Club <code>{{ $registration->teacherClub->club_code }}</code></small>
                                                @endif
                                            </td>
                                            <td>
                                                <div>{{ $registration->resolvedTeacherName() ?: '—' }}</div>
                                                <small class="text-muted text-break">{{ $registration->resolvedTeacherEmail() ?: 'No email' }}</small>
                                            </td>
                                            <td>{{ $registration->region?->name ?? $registration->school?->regionModel?->name ?? 'N/A' }}</td>
                                            <td>
                                                @if($registration->idea)
                                                    <span class="badge bg-secondary">Draft saved</span>
                                                @else
                                                    <span class="badge bg-light text-dark">No draft</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $reminderCount >= $maxIdeaReminders ? 'bg-secondary' : ($reminderCount > 0 ? 'bg-warning text-dark' : 'bg-light text-dark') }}">
                                                    {{ $reminderCount }}/{{ $maxIdeaReminders }}
                                                </span>
                                                @if($registration->last_idea_submission_reminder_sent_at)
                                                    <div class="small text-muted">Last {{ $registration->last_idea_submission_reminder_sent_at->timezone(config('app.timezone'))->format('j M') }}</div>
                                                @endif
                                            </td>
                                            <td class="text-nowrap">
                                                <x-admin.table-actions :actions="array_values(array_filter([
                                                    [
                                                        'type' => 'link',
                                                        'label' => 'View',
                                                        'icon' => 'ti ti-eye',
                                                        'url' => route('admin.competitions.ideas.pending', [$competition, $registration]),
                                                    ],
                                                    $canRemind ? [
                                                        'type' => 'button',
                                                        'label' => 'Remind',
                                                        'icon' => 'ti ti-bell',
                                                        'attributes' => [
                                                            'data-bs-toggle' => 'modal',
                                                            'data-bs-target' => '#ideaSubmissionReminderModal'.$registration->id,
                                                        ],
                                                    ] : null,
                                                ]))" />
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">All approved schools have submitted ideas.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $pendingRegistrations->links('pagination::bootstrap-5') }}</div>
                    </div>
                </div>

                @foreach($pendingRegistrations as $registration)
                    @if($registration->canSendIdeaSubmissionReminder())
                        @php
                            $smsMeta = $ideaSubmissionSmsPreviews[$registration->id] ?? null;
                            $smsCanSend = filled($smsMeta['normalized'] ?? null);
                            $smsPhoneDisplay = $smsMeta['normalized'] ?? ($smsMeta['phone'] ?? null);
                            $teacherEmail = $registration->resolvedTeacherEmail();
                        @endphp
                        <div class="modal fade" id="ideaSubmissionReminderModal{{ $registration->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Remind: {{ $registration->resolvedSchoolName() }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" action="{{ route('admin.competitions.ideas.submission-reminder', [$competition, $registration]) }}" class="js-prevent-double-submit">
                                        @csrf
                                        <div class="modal-body">
                                            <p class="mb-3">
                                                Sends an email and optional SMS asking them to submit their idea.
                                                Email:
                                                @if(filled($teacherEmail))
                                                    <strong>{{ $teacherEmail }}</strong>
                                                @else
                                                    <strong class="text-danger">No teacher email — email will be skipped</strong>
                                                @endif
                                            </p>
                                            @include('admin.competitions.registrations.partials.sms-preview-fields', [
                                                'prefix' => 'idea-submission-reminder-'.$registration->id,
                                                'phoneDisplay' => $smsPhoneDisplay,
                                                'canSendSms' => $smsCanSend,
                                                'message' => old('sms_message', $smsMeta['preview'] ?? ''),
                                                'senderDefault' => $smsDefaultSender,
                                                'senderOptions' => $smsAllowedSenders,
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
                @endforeach
            @else
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>School</th>
                                        <th>Idea</th>
                                        <th>Region</th>
                                        <th>Avg Score</th>
                                        <th>Shortlist</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($ideas as $idea)
                                        @php($avgScore = round((float) $idea->scores->avg('total_score'), 2))
                                        <tr>
                                            <td>{{ $ideas->firstItem() + $loop->index }}</td>
                                            <td>
                                                <div class="fw-semibold">{{ $idea->registration?->resolvedSchoolName() ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ $idea->registration?->teacher_name }}</small>
                                            </td>
                                            <td>
                                                <div>{{ $idea->problem_title ?: 'Untitled' }}</div>
                                                <small class="text-muted">{{ ucfirst(str_replace('_', ' ', (string) $idea->status)) }}</small>
                                            </td>
                                            <td>{{ $idea->communityRegion?->name ?? $idea->registration?->region?->name ?? 'N/A' }}</td>
                                            <td>{{ $avgScore ?: '—' }}</td>
                                            <td>
                                                @if($idea->shortlist)
                                                    <span class="badge bg-{{ $idea->shortlist->status === 'shortlisted' ? 'success' : ($idea->shortlist->status === 'waitlist' ? 'warning' : 'danger') }}">
                                                        {{ ucfirst($idea->shortlist->status) }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-light text-dark">Pending</span>
                                                @endif
                                            </td>
                                            <td class="text-nowrap">
                                                <x-admin.table-actions :actions="[
                                                    [
                                                        'type' => 'link',
                                                        'label' => 'View',
                                                        'icon' => 'ti ti-eye',
                                                        'url' => route('admin.competitions.ideas.show', [$competition, $idea]),
                                                    ],
                                                ]" />
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">No submitted ideas yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $ideas->links('pagination::bootstrap-5') }}</div>
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
