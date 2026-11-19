<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Notification Settings</h5>
                    <p class="text-muted mb-0">SMS credits, sender IDs, and competition message templates.</p>
                </div>
            </div>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <p class="text-muted small mb-1">SMS credits remaining</p>
                            @if (! is_null($balance['sms_balance'] ?? null))
                                <h3 class="mb-0">{{ number_format((int) $balance['sms_balance']) }}</h3>
                            @else
                                <h3 class="mb-0 text-muted">—</h3>
                                @if (! empty($balance['error']))
                                    <p class="small text-danger mb-0 mt-2">{{ $balance['error'] }}</p>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card h-100">
                        <div class="card-body">
                            <p class="text-muted small mb-2">Sender IDs (allowed for this app)</p>
                            <div class="d-flex flex-wrap gap-2">
                                @forelse ($senders['sender_ids'] ?? [] as $sender)
                                    @php $name = is_array($sender) ? ($sender['name'] ?? '') : (string) $sender; @endphp
                                    <span class="badge bg-{{ strcasecmp($name, $defaultSender) === 0 ? 'primary' : 'secondary' }}">
                                        {{ $name }}
                                        @if (strcasecmp($name, $defaultSender) === 0)
                                            · default
                                        @endif
                                    </span>
                                @empty
                                    @foreach ($allowedSenders as $name)
                                        <span class="badge bg-secondary">{{ $name }}</span>
                                    @endforeach
                                @endforelse
                            </div>
                            @if (! empty($senders['error']))
                                <p class="small text-warning mb-0 mt-2">Could not refresh from Soscom: {{ $senders['error'] }}. Showing configured IDs.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.notification-settings.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label" for="sms_default_sender_id">Default sender ID</label>
                            <select class="form-select @error('sms_default_sender_id') is-invalid @enderror" name="sms_default_sender_id" id="sms_default_sender_id" required>
                                @foreach ($allowedSenders as $sender)
                                    <option value="{{ $sender }}" @selected(old('sms_default_sender_id', $defaultSender) === $sender)>{{ $sender }}</option>
                                @endforeach
                            </select>
                            @error('sms_default_sender_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <p class="small text-muted mb-3">Placeholders: {{ $placeholders }}</p>

                        <div class="mb-3">
                            <label class="form-label" for="sms_template_competition_registration_approved">Registration approved SMS</label>
                            <textarea
                                class="form-control @error('sms_template_competition_registration_approved') is-invalid @enderror"
                                name="sms_template_competition_registration_approved"
                                id="sms_template_competition_registration_approved"
                                rows="3"
                                maxlength="480"
                                required
                            >{{ old('sms_template_competition_registration_approved', $templates['registration_approved']) }}</textarea>
                            @error('sms_template_competition_registration_approved')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="sms_template_competition_eligibility_reminder">Eligibility reminder SMS</label>
                            <textarea
                                class="form-control @error('sms_template_competition_eligibility_reminder') is-invalid @enderror"
                                name="sms_template_competition_eligibility_reminder"
                                id="sms_template_competition_eligibility_reminder"
                                rows="3"
                                maxlength="480"
                                required
                            >{{ old('sms_template_competition_eligibility_reminder', $templates['eligibility_reminder']) }}</textarea>
                            @error('sms_template_competition_eligibility_reminder')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="sms_template_competition_idea_submitted">Idea submitted confirmation SMS</label>
                            <textarea
                                class="form-control @error('sms_template_competition_idea_submitted') is-invalid @enderror"
                                name="sms_template_competition_idea_submitted"
                                id="sms_template_competition_idea_submitted"
                                rows="3"
                                maxlength="480"
                                required
                            >{{ old('sms_template_competition_idea_submitted', $templates['idea_submitted']) }}</textarea>
                            @error('sms_template_competition_idea_submitted')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Sent automatically when a teacher final-submits an idea (not edited at send time).</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="sms_template_competition_idea_shortlisted">Idea shortlisted SMS</label>
                            <textarea
                                class="form-control @error('sms_template_competition_idea_shortlisted') is-invalid @enderror"
                                name="sms_template_competition_idea_shortlisted"
                                id="sms_template_competition_idea_shortlisted"
                                rows="3"
                                maxlength="480"
                                required
                            >{{ old('sms_template_competition_idea_shortlisted', $templates['idea_shortlisted']) }}</textarea>
                            @error('sms_template_competition_idea_shortlisted')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Default text when an admin shortlists an idea. They can still edit it before sending.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="sms_template_competition_idea_shortlist_reminder">Idea shortlist reminder SMS</label>
                            <textarea
                                class="form-control @error('sms_template_competition_idea_shortlist_reminder') is-invalid @enderror"
                                name="sms_template_competition_idea_shortlist_reminder"
                                id="sms_template_competition_idea_shortlist_reminder"
                                rows="3"
                                maxlength="480"
                                required
                            >{{ old('sms_template_competition_idea_shortlist_reminder', $templates['idea_shortlist_reminder']) }}</textarea>
                            @error('sms_template_competition_idea_shortlist_reminder')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Default text for a later reminder. Admins edit it in the reminder dialog before it is sent.</div>
                        </div>

                        <button type="submit" class="btn btn-primary">Save notification settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
