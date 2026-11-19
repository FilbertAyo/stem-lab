<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Subscription Issues</h5>
                    <p class="text-muted mb-0">Open checkouts, failed/expired payments, and users whose prepaid access has ended.</p>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-none h-100">
                        <div class="card-body">
                            <div class="text-muted small">Open checkouts</div>
                            <div class="fs-4 fw-semibold">{{ $openCheckoutCount }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-none h-100">
                        <div class="card-body">
                            <div class="text-muted small">Failed / expired orders</div>
                            <div class="fs-4 fw-semibold">{{ $failedCount }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-none h-100">
                        <div class="card-body">
                            <div class="text-muted small">Expired prepaid users (sample)</div>
                            <div class="fs-4 fw-semibold">{{ $expiredUserCount }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card tbl-card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Payment / checkout issues</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>User</th>
                                    <th>Package</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($problemOrders as $order)
                                    <tr>
                                        <td>{{ $order->created_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $order->user?->name ?? '—' }}</div>
                                            <div class="small text-muted">{{ $order->user?->email }}</div>
                                        </td>
                                        <td>{{ $order->package?->name ?? $order->package_slug }}</td>
                                        <td>{{ number_format($order->amount_tzs) }} {{ $order->currency }}</td>
                                        <td><span class="badge bg-secondary">{{ $order->status }}</span></td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.subscription-orders.show', $order) }}" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No open or failed payment issues right now.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card tbl-card">
                <div class="card-header">
                    <h6 class="mb-0">Expired prepaid subscriptions</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Tier</th>
                                    <th>Expired at</th>
                                    <th class="text-center" style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($expiredUsers as $user)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $user->name }}</div>
                                            <div class="small text-muted">{{ $user->email }}</div>
                                        </td>
                                        <td>{{ $user->role }}</td>
                                        <td><span class="badge bg-secondary">{{ $user->subscription_tier }}</span></td>
                                        <td>{{ $user->subscription_expires_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No expired prepaid users in this sample.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
