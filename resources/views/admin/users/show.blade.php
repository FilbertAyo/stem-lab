<x-admin-layout>
    @php
        $timezone = config('app.timezone');
        $approvedClubs = $user->teacherClubs->where('status', 'approved');
        $pendingClubs = $user->teacherClubs->where('status', 'pending');
        $roster = $user->linkedRosterStudent;
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <p class="text-muted mb-0">{{ ucfirst($user->role) }} account details</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ $backRoute }}" class="btn btn-outline-secondary">Back to list</a>
                    @if ($user->role === 'teacher')
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editTeacherModal">
                            <i class="ti ti-edit"></i> Edit
                        </button>
                    @endif
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="row">
                <div class="col-xl-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0">Profile</h6>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Name</dt>
                                <dd class="col-sm-8">{{ $user->name }}</dd>

                                <dt class="col-sm-4">Email</dt>
                                <dd class="col-sm-8">{{ $user->email ?: '—' }}</dd>

                                <dt class="col-sm-4">Phone</dt>
                                <dd class="col-sm-8"><x-whatsapp-phone :phone="$user->phone" /></dd>

                                <dt class="col-sm-4">Login ID</dt>
                                <dd class="col-sm-8">{{ $user->login_id ?: '—' }}</dd>

                                <dt class="col-sm-4">Role</dt>
                                <dd class="col-sm-8">
                                    <span class="badge {{ $user->role === 'teacher' ? 'bg-info' : 'bg-success' }}">{{ ucfirst($user->role) }}</span>
                                </dd>

                                <dt class="col-sm-4">Status</dt>
                                <dd class="col-sm-8">
                                    @if ($user->is_active ?? true)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Disabled</span>
                                    @endif
                                </dd>

                                <dt class="col-sm-4">Email verified</dt>
                                <dd class="col-sm-8">
                                    @if ($user->email_verified_at)
                                        <span class="badge bg-success">Verified</span>
                                        <div class="small text-muted mt-1">{{ $user->email_verified_at->timezone($timezone)->format('d M Y H:i') }}</div>
                                    @else
                                        <span class="badge bg-warning">Not verified</span>
                                    @endif
                                </dd>

                                <dt class="col-sm-4">Created</dt>
                                <dd class="col-sm-8">{{ $user->created_at?->timezone($timezone)->format('d M Y H:i') ?: '—' }}</dd>

                                <dt class="col-sm-4">Updated</dt>
                                <dd class="col-sm-8">{{ $user->updated_at?->timezone($timezone)->format('d M Y H:i') ?: '—' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0">Subscription plan</h6>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Current plan</dt>
                                <dd class="col-sm-8">
                                    <span class="badge {{ $user->platformPlanBadgeClass() }}">{{ $user->adminPlanLabel() }}</span>
                                </dd>

                                <dt class="col-sm-4">Tier key</dt>
                                <dd class="col-sm-8"><code>{{ $user->subscription_tier ?: 'null' }}</code></dd>

                                <dt class="col-sm-4">Access ends</dt>
                                <dd class="col-sm-8">
                                    @if ($user->hasSubsidizedSchoolAccess())
                                        No expiry (public school / club access)
                                    @elseif ($user->subscription_expires_at)
                                        {{ $user->subscription_expires_at->timezone($timezone)->format('d M Y H:i') }}
                                        @if ($user->subscription_expires_at->isPast())
                                            <span class="badge bg-danger ms-1">Expired</span>
                                        @elseif ($user->hasActivePrepaidAccess())
                                            <span class="badge bg-success ms-1">Active</span>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </dd>

                                <dt class="col-sm-4">Trial used</dt>
                                <dd class="col-sm-8">
                                    {{ $user->trial_used_at?->timezone($timezone)->format('d M Y H:i') ?: 'Not used' }}
                                </dd>

                                <dt class="col-sm-4">Club linked</dt>
                                <dd class="col-sm-8">
                                    {{ $user->club_linked_at?->timezone($timezone)->format('d M Y H:i') ?: '—' }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">School & club links</h6>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        <strong>School:</strong> {{ $user->resolvedSchoolLabel() }}
                    </p>

                    @if ($user->role === 'teacher')
                        @if ($approvedClubs->isEmpty() && $pendingClubs->isEmpty())
                            <p class="text-muted mb-0">No STEM club memberships.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead>
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
                            <dl class="row mb-0">
                                <dt class="col-sm-3">Roster name</dt>
                                <dd class="col-sm-9">{{ $roster->name }}</dd>
                                <dt class="col-sm-3">Admission number</dt>
                                <dd class="col-sm-9"><code>{{ $roster->admission_number }}</code></dd>
                                <dt class="col-sm-3">Class</dt>
                                <dd class="col-sm-9">{{ $roster->schoolClass?->name ?: ($user->schoolClass?->name ?: '—') }}</dd>
                                <dt class="col-sm-3">Club</dt>
                                <dd class="col-sm-9">{{ $roster->teacherClub?->club?->name ?: '—' }}</dd>
                                <dt class="col-sm-3">School</dt>
                                <dd class="col-sm-9">{{ $roster->teacherClub?->school?->name ?: '—' }}</dd>
                                <dt class="col-sm-3">Linked at</dt>
                                <dd class="col-sm-9">{{ $roster->linked_at?->timezone($timezone)->format('d M Y H:i') ?: '—' }}</dd>
                            </dl>
                        @else
                            <p class="text-muted mb-0">Student is not linked to a STEM club roster.</p>
                        @endif
                    @endif
                </div>
            </div>

            @if ($user->subscriptionOrders->isNotEmpty())
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Recent payments</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead>
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
            @endif

            <div class="card">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                        onsubmit="return confirm('Are you sure you want to delete {{ addslashes($user->name) }}? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="ti ti-trash"></i> Delete {{ ucfirst($user->role) }}
                        </button>
                    </form>
                    <a href="{{ $backRoute }}" class="btn btn-outline-secondary">Back</a>
                </div>
            </div>
        </div>
    </div>

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
