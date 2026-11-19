<x-admin-layout>
    @php
        $timezone = config('app.timezone');
        $roster = $user->linkedRosterStudent;
        $selectedPlan = old('plan', match (true) {
            $user->subscription_tier === 'trial' => 'free-trial',
            in_array($user->subscription_tier, ['individual', 'pro'], true) => 'individual',
            $user->subscription_tier === 'public_school' => 'public-school',
            default => 'none',
        });
        $defaultExpires = old(
            'expires_at',
            $user->subscription_expires_at?->timezone($timezone)->format('Y-m-d')
        );
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center flex-shrink-0"
                        style="width: 3rem; height: 3rem; font-weight: 600;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                            <h4 class="mb-0">{{ $user->name }}</h4>
                            <span class="badge {{ $user->role === 'teacher' ? 'bg-info' : 'bg-success' }}">{{ ucfirst($user->role) }}</span>
                            @if ($user->is_active ?? true)
                                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">Disabled</span>
                            @endif
                            <span class="badge {{ $user->platformPlanBadgeClass() }}">{{ $user->adminPlanLabel() }}</span>
                        </div>
                        <p class="text-muted mb-0 small">
                            {{ $user->email ?: ($user->login_id ?: 'No email') }}
                            · School: {{ $user->resolvedSchoolLabel() }}
                        </p>
                    </div>
                </div>

                <div class="d-flex align-items-center flex-wrap gap-2">
                    <a href="{{ $backRoute }}" class="btn btn-sm btn-outline-secondary">
                        <i class="ti ti-arrow-left"></i> Back
                    </a>
                    @if ($user->role === 'teacher')
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editTeacherModal">
                            <i class="ti ti-edit"></i> Edit
                        </button>
                    @endif
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline"
                        onsubmit="return confirm('Delete {{ addslashes($user->name) }}? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete {{ ucfirst($user->role) }}">
                            <i class="ti ti-trash"></i>
                        </button>
                    </form>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success py-2">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger py-2">{{ session('error') }}</div>
            @endif
            @if ($errors->any() && old('_form') !== 'edit_teacher')
                <div class="alert alert-danger py-2">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row g-3">
                <div class="col-xl-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h6 class="text-muted text-uppercase small fw-semibold mb-3">Profile</h6>
                            <div class="d-flex flex-column gap-3">
                                <div>
                                    <div class="small text-muted">Email</div>
                                    <div>{{ $user->email ?: '—' }}</div>
                                </div>
                                <div>
                                    <div class="small text-muted">Phone</div>
                                    <div><x-whatsapp-phone :phone="$user->phone" /></div>
                                </div>
                                <div>
                                    <div class="small text-muted">Login ID</div>
                                    <div>{{ $user->login_id ?: '—' }}</div>
                                </div>
                                <div>
                                    <div class="small text-muted">Email verified</div>
                                    <div>
                                        @if ($user->email_verified_at)
                                            <span class="badge bg-success">Verified</span>
                                            <span class="small text-muted ms-1">{{ $user->email_verified_at->timezone($timezone)->format('d M Y') }}</span>
                                        @else
                                            <span class="badge bg-warning">Not verified</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="small text-muted">Created</div>
                                        <div class="small">{{ $user->created_at?->timezone($timezone)->format('d M Y H:i') ?: '—' }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="small text-muted">Updated</div>
                                        <div class="small">{{ $user->updated_at?->timezone($timezone)->format('d M Y H:i') ?: '—' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                                <div>
                                    <h6 class="text-muted text-uppercase small fw-semibold mb-1">Subscription plan</h6>
                                    <div class="d-flex align-items-center flex-wrap gap-2">
                                        <span class="badge {{ $user->platformPlanBadgeClass() }}">{{ $user->adminPlanLabel() }}</span>
                                        @if ($user->subscription_expires_at)
                                            <span class="small text-muted">
                                                Ends {{ $user->subscription_expires_at->timezone($timezone)->format('d M Y H:i') }}
                                                @if ($user->subscription_expires_at->isPast())
                                                    <span class="badge bg-danger ms-1">Expired</span>
                                                @endif
                                            </span>
                                        @elseif ($user->hasSubsidizedSchoolAccess())
                                            <span class="small text-muted">No expiry (club / public school access)</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <div class="rounded border bg-light p-3 h-100">
                                        <div class="small text-muted">Tier</div>
                                        <div class="fw-semibold"><code>{{ $user->subscription_tier ?: 'none' }}</code></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="rounded border bg-light p-3 h-100">
                                        <div class="small text-muted">Trial used</div>
                                        <div class="fw-semibold">{{ $user->trial_used_at?->timezone($timezone)->format('d M Y') ?: 'Not used' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="rounded border bg-light p-3 h-100">
                                        <div class="small text-muted">Club linked</div>
                                        <div class="fw-semibold">{{ $user->club_linked_at?->timezone($timezone)->format('d M Y') ?: '—' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="border-top pt-3">
                                <h6 class="mb-3">Manage plan</h6>
                                <form method="POST" action="{{ route('admin.users.subscription.update', $user) }}" class="row g-3 align-items-end">
                                    @csrf
                                    @method('PUT')

                                    <div class="col-md-4">
                                        <label for="plan" class="form-label">Plan</label>
                                        <select name="plan" id="plan" class="form-select @error('plan') is-invalid @enderror" required>
                                            <option value="none" @selected($selectedPlan === 'none')>No plan</option>
                                            <option value="free-trial" @selected($selectedPlan === 'free-trial')>
                                                Free trial @if(isset($packages['free-trial'])) ({{ $packages['free-trial']->billing_period_days }} days) @endif
                                            </option>
                                            <option value="individual" @selected($selectedPlan === 'individual')>
                                                Individual @if(isset($packages['individual'])) ({{ $packages['individual']->billing_period_days }} days) @endif
                                            </option>
                                            <option value="public-school" @selected($selectedPlan === 'public-school')>
                                                Public school (free)
                                            </option>
                                        </select>
                                        @error('plan')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4" id="expires-at-field">
                                        <label for="expires_at" class="form-label">Access ends</label>
                                        <input type="date" name="expires_at" id="expires_at"
                                            class="form-control @error('expires_at') is-invalid @enderror"
                                            value="{{ $defaultExpires }}">
                                        <div class="form-text">Leave blank to use the package default period.</div>
                                        @error('expires_at')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-check mb-2" id="mark-trial-used-field">
                                            <input class="form-check-input" type="checkbox" value="1" id="mark_trial_used"
                                                name="mark_trial_used" @checked(old('mark_trial_used', true))>
                                            <label class="form-check-label" for="mark_trial_used">
                                                Mark free trial as used
                                            </label>
                                        </div>
                                        <button type="submit" class="btn btn-dark w-100">
                                            Save plan
                                        </button>
                                    </div>
                                </form>
                                <p class="small text-muted mb-0 mt-3">
                                    Choose <strong>No plan</strong> to remove access from a paid/trial assignment.
                                    Club-linked public-school access can still apply if the user remains linked to a STEM club.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted text-uppercase small fw-semibold mb-3">School & club</h6>
                            <p class="mb-3">
                                <span class="text-muted">School:</span>
                                <strong>{{ $user->resolvedSchoolLabel() }}</strong>
                            </p>

                            @if ($user->role === 'teacher')
                                @if ($user->teacherClubs->isEmpty())
                                    <p class="text-muted mb-0">No STEM club memberships.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Club</th>
                                                    <th>School</th>
                                                    <th>Status</th>
                                                    <th>Leader</th>
                                                    <th>Club code</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($user->teacherClubs as $membership)
                                                    <tr>
                                                        <td>{{ $membership->club?->name ?: '—' }}</td>
                                                        <td>{{ $membership->school?->name ?: '—' }}</td>
                                                        <td><span class="badge bg-secondary">{{ $membership->status }}</span></td>
                                                        <td>{{ $membership->is_leader ? 'Yes' : 'No' }}</td>
                                                        <td><code>{{ $membership->club_code ?: '—' }}</code></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            @else
                                @if ($roster)
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="small text-muted">Roster name</div>
                                            <div>{{ $roster->name }}</div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="small text-muted">Admission number</div>
                                            <div><code>{{ $roster->admission_number }}</code></div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="small text-muted">Class</div>
                                            <div>{{ $roster->schoolClass?->name ?: ($user->schoolClass?->name ?: '—') }}</div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="small text-muted">Club</div>
                                            <div>{{ $roster->teacherClub?->club?->name ?: '—' }}</div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="small text-muted">School</div>
                                            <div>{{ $roster->teacherClub?->school?->name ?: '—' }}</div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="small text-muted">Linked at</div>
                                            <div>{{ $roster->linked_at?->timezone($timezone)->format('d M Y H:i') ?: '—' }}</div>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-muted mb-0">Student is not linked to a STEM club roster.</p>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                @if ($user->subscriptionOrders->isNotEmpty())
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted text-uppercase small fw-semibold mb-3">Recent payments</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Package</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Payment ref</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($user->subscriptionOrders as $order)
                                                <tr>
                                                    <td>{{ $order->created_at?->timezone($timezone)->format('d M Y H:i') }}</td>
                                                    <td>{{ $order->package?->name ?? $order->package_slug }}</td>
                                                    <td>{{ number_format($order->amount_tzs) }} {{ $order->currency }}</td>
                                                    <td><span class="badge bg-secondary">{{ $order->status }}</span></td>
                                                    <td><code>{{ $order->snippe_payment_reference ?: '—' }}</code></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        (function () {
            const planSelect = document.getElementById('plan');
            const expiresField = document.getElementById('expires-at-field');
            const trialField = document.getElementById('mark-trial-used-field');

            function syncPlanFields() {
                if (!planSelect) return;
                const plan = planSelect.value;
                const needsExpiry = plan === 'free-trial' || plan === 'individual';
                if (expiresField) {
                    expiresField.style.display = needsExpiry ? '' : 'none';
                }
                if (trialField) {
                    trialField.style.display = plan === 'free-trial' ? '' : 'none';
                }
            }

            if (planSelect) {
                planSelect.addEventListener('change', syncPlanFields);
                syncPlanFields();
            }
        })();
    </script>
    @endpush

    @if ($user->role === 'teacher')
        <div class="modal fade" id="editTeacherModal" tabindex="-1" aria-labelledby="editTeacherModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editTeacherModalLabel">Edit Teacher</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_form" value="edit_teacher">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="edit_teacher_name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="edit_teacher_name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="edit_teacher_email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="edit_teacher_email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="edit_teacher_phone" class="form-label">Phone</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                    id="edit_teacher_phone" name="phone" value="{{ old('phone', $user->phone) }}"
                                    placeholder="+255XX or 07XX">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="edit_teacher_password" class="form-label">
                                    Password <small class="text-muted">(Leave blank to keep current password)</small>
                                </label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="edit_teacher_password" name="password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Teacher</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if ($errors->any() && old('_form') === 'edit_teacher')
            @push('scripts')
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const modal = document.getElementById('editTeacherModal');
                        if (modal && typeof bootstrap !== 'undefined') {
                            bootstrap.Modal.getOrCreateInstance(modal).show();
                        }
                    });
                </script>
            @endpush
        @endif
    @endif
</x-admin-layout>
