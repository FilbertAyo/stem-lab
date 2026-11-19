<x-admin-layout>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Subscription Payments</h5>
                    <p class="text-muted mb-0">Snippe prepaid checkout orders and fulfillment status.</p>
                </div>
            </div>

            <form method="GET" class="card border-0 shadow-none mb-3">
                <div class="card-body d-flex flex-wrap gap-3 align-items-end">
                    <div>
                        <label class="form-label" for="status">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All</option>
                            @foreach (['pending', 'checkout_created', 'paid', 'fulfilled', 'failed', 'expired', 'cancelled'] as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-dark">Filter</button>
                </div>
            </form>

            <div class="card tbl-card">
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
                                @forelse ($orders as $order)
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
                                        <td colspan="6" class="text-center text-muted py-4">No subscription payments yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
