<x-admin-layout>
    <div class="row">
        <div class="col-xl-8">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Payment order</h5>
                    <p class="text-muted mb-0"><code>{{ $order->ulid }}</code></p>
                </div>
                <a href="{{ route('admin.subscription-orders.index') }}" class="btn btn-outline-secondary">Back</a>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8"><span class="badge bg-secondary">{{ $order->status }}</span></dd>

                        <dt class="col-sm-4">User</dt>
                        <dd class="col-sm-8">
                            {{ $order->user?->name }}<br>
                            <span class="text-muted">{{ $order->user?->email }}</span>
                        </dd>

                        <dt class="col-sm-4">Package</dt>
                        <dd class="col-sm-8">{{ $order->package?->name ?? $order->package_slug }} ({{ $order->period_days }} days)</dd>

                        <dt class="col-sm-4">Amount</dt>
                        <dd class="col-sm-8">{{ number_format($order->amount_tzs) }} {{ $order->currency }}</dd>

                        <dt class="col-sm-4">Snippe session</dt>
                        <dd class="col-sm-8"><code>{{ $order->snippe_session_reference ?: '—' }}</code></dd>

                        <dt class="col-sm-4">Snippe payment</dt>
                        <dd class="col-sm-8"><code>{{ $order->snippe_payment_reference ?: '—' }}</code></dd>

                        <dt class="col-sm-4">Paid at</dt>
                        <dd class="col-sm-8">{{ $order->paid_at?->timezone(config('app.timezone'))->format('d M Y H:i') ?: '—' }}</dd>

                        <dt class="col-sm-4">Fulfilled at</dt>
                        <dd class="col-sm-8">{{ $order->fulfilled_at?->timezone(config('app.timezone'))->format('d M Y H:i') ?: '—' }}</dd>

                        <dt class="col-sm-4">User access until</dt>
                        <dd class="col-sm-8">{{ $order->user?->subscription_expires_at?->timezone(config('app.timezone'))->format('d M Y H:i') ?: '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
