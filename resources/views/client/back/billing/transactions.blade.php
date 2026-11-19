@push('styles')
  <link rel="stylesheet" href="{{ asset('client/assets/css/client-billing.css') }}">
@endpush

<x-app-layout>
    <div class="col-lg-12">
        <div class="dashboard-content-header d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
            <div>
                <h4 class="mb-1">Billing history</h4>
                <p class="text-muted mb-0">Mobile-money payments for your Individual plan.</p>
            </div>
            <a href="{{ route('client.billing.index') }}" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to My plan
            </a>
        </div>

        <div class="card border shadow-none">
            <div class="card-body p-0">
                @if ($orders->isEmpty())
                    <div class="billing-history-empty">
                        <div class="empty-icon"><i class="bi bi-wallet2"></i></div>
                        <h6 class="mb-1">No payments yet</h6>
                        <p class="text-muted small mb-3">When you pay with mobile money, receipts and payment references will appear here.</p>
                        <a href="{{ route('client.billing.index') }}" class="btn btn-dark btn-sm">Go to billing</a>
                    </div>
                @else
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
                                @foreach ($orders as $order)
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
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            @if ($orders->hasPages())
                <div class="card-footer bg-white">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
