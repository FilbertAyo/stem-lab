<x-app-layout>
    <div class="col-lg-12">
        <div class="dashboard-content-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h4 class="mb-1">Billing history</h4>
                <p class="text-muted mb-0">Mobile-money payments for your Individual plan.</p>
            </div>
            <a href="{{ route('client.billing.index') }}" class="btn btn-outline-dark btn-sm">Back to my plan</a>
        </div>

        <div class="card border shadow-none">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
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
                            @forelse ($orders as $order)
                                <tr>
                                    <td>{{ $order->created_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}</td>
                                    <td>{{ $order->package?->name ?? $order->package_slug }}</td>
                                    <td>{{ number_format($order->amount_tzs) }} {{ $order->currency }}</td>
                                    <td>
                                        <span class="badge {{ $order->isFulfilled() ? 'bg-success-subtle text-success-emphasis border border-success-subtle' : 'bg-secondary-subtle text-secondary-emphasis border' }}">
                                            {{ str_replace('_', ' ', $order->status) }}
                                        </span>
                                    </td>
                                    <td><code>{{ $order->snippe_payment_reference ?: '—' }}</code></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">No payments yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($orders->hasPages())
                <div class="card-footer bg-white">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
