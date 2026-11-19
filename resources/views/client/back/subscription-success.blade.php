<x-app-layout>
    <div class="row justify-content-center">
        <div class="col-lg-7 col-xl-6">
            <div class="card border shadow-none">
                <div class="card-body p-7 p-xxl-5">
                    @if ($order->isFulfilled() || auth()->user()?->hasActivePrepaidAccess())
                        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle mb-3">Payment confirmed</span>
                        <h4 class="mb-3">Your plan is active</h4>
                        <p class="text-muted mb-4">
                            Thanks for paying with mobile money. Your
                            <strong>{{ $order->package?->name ?? 'Individual' }}</strong>
                            prepaid access
                            @if (auth()->user()?->subscription_expires_at)
                                runs until
                                <strong>{{ auth()->user()->subscription_expires_at->timezone(config('app.timezone'))->format('d M Y H:i') }}</strong>.
                            @else
                                is being activated.
                            @endif
                        </p>
                    @else
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle mb-3">Confirming payment</span>
                        <h4 class="mb-3">We’re confirming your payment</h4>
                        <p class="text-muted mb-4">
                            If you completed the mobile-money prompt, access usually activates within a few moments.
                            You can refresh this page or return to the dashboard.
                        </p>
                    @endif

                    <div class="d-flex flex-wrap gap-3">
                        <a href="{{ route('dashboard') }}#platform-subscription" class="btn btn-dark">Back to dashboard</a>
                        @if (! $order->isFulfilled())
                            <a href="{{ route('client.subscription-packages.success', $order) }}" class="btn btn-outline-dark">Refresh status</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
