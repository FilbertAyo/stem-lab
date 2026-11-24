@php
  $paymentsEnabled = (bool) config('subscriptions.payments_enabled', false);
  $hasLockedPlan = $user->hasLockedSubscriptionPlan();
  $hasAccess = $user->hasPlatformAccess();
  $onTrial = $user->hasActivePrepaidAccess() && $user->subscription_tier === 'trial';
  $onIndividual = $user->hasActivePrepaidAccess() && in_array($user->subscription_tier, ['individual', 'pro'], true);
  $expiresAt = $user->subscription_expires_at;
  $secondsLeft = ($expiresAt && ! $expiresAt->isPast()) ? ($expiresAt->getTimestamp() - time()) : 0;
  $daysLeft = $secondsLeft > 0 ? (int) ceil($secondsLeft / 86400) : 0;
  $endsToday = $secondsLeft > 0 && $daysLeft <= 1;
  $trialUsed = $user->trial_used_at !== null;
  $paywallTitle = $trialUsed && ! $hasAccess ? 'Your trial has ended' : 'Access paused';
  $heroClass = match (true) {
      $hasLockedPlan => 'is-free',
      $onTrial => 'is-trial',
      $hasAccess => '',
      default => 'is-paused',
  };
  $statusHeadline = match (true) {
      $hasLockedPlan => 'You’re on free public-school access',
      $onTrial => $endsToday ? 'Your free trial ends today' : ($daysLeft.' '.($daysLeft === 1 ? 'day' : 'days').' left on your trial'),
      $onIndividual => 'Individual prepaid access is active',
      $trialUsed => 'Choose how to continue',
      default => 'Choose your access',
  };
  $statusCopy = match (true) {
      $hasLockedPlan => 'Your STEM club covers the platform at no cost while membership stays approved. You do not need to pay.',
      $onTrial => $expiresAt
          ? 'Access continues until '.$expiresAt->timezone(config('app.timezone'))->format('d M Y').'. Pay anytime to keep STEM Labs after the trial.'
          : 'Pay anytime to keep STEM Labs after the trial.',
      $onIndividual => $expiresAt
          ? 'Access runs until '.$expiresAt->timezone(config('app.timezone'))->format('d M Y').'. Prepaid — no auto-renewal.'
          : 'Prepaid access — no auto-renewal.',
      $trialUsed => 'Pick a package below: pay with mobile money, or join a public-school STEM club for free access.',
      default => 'Pick a package below for Individual access, or join a public-school STEM club if your school is eligible.',
  };
@endphp

@push('styles')
  <link rel="stylesheet" href="{{ asset('client/assets/css/client-billing.css') }}">
@endpush

<x-app-layout>
    <div class="col-lg-12">
        <div class="billing-page">
            <div class="dashboard-content-header d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                <div>
                    <h4 class="mb-1">My plan</h4>
                    <p class="text-muted mb-0">Current access, packages, and payment history.</p>
                </div>
                <a href="{{ route('client.billing.transactions') }}" class="btn btn-outline-dark btn-sm">
                    <i class="bi bi-receipt me-1"></i> Billing history
                </a>
            </div>

            @if (session('warning'))
                <div class="alert alert-warning">{{ session('warning') }}</div>
            @endif
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if ($showPaywall)
                <div class="alert alert-warning d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2" role="alert">
                    <div>
                        <strong class="d-block">{{ $paywallTitle }}</strong>
                        <span class="small">Learning is paused until you subscribe or join a public-school STEM club. Choose a package below.</span>
                    </div>
                    <a href="#platform-subscription" class="btn btn-dark btn-sm flex-shrink-0">View packages</a>
                </div>
            @endif

            <section class="billing-hero {{ $heroClass }} mb-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-4">
                    <div class="flex-grow-1">
                        <span class="badge {{ $user->platformPlanBadgeClass() }} mb-3">{{ $user->platformPlanLabel() }}</span>
                        <h5 class="fw-semibold mb-2">{{ $statusHeadline }}</h5>
                        <p class="text-muted mb-0" style="max-width: 40rem;">{{ $statusCopy }}</p>
                    </div>
                    <div class="billing-hero-meta align-self-lg-center">
                        <div class="billing-hero-meta-item">
                            <span class="label">Access</span>
                            <span class="value">{{ $hasAccess ? 'Active' : 'Paused' }}</span>
                        </div>
                        <div class="billing-hero-meta-item">
                            <span class="label">Ends</span>
                            <span class="value">
                                @if ($hasLockedPlan)
                                    Club-linked
                                @elseif ($expiresAt)
                                    {{ $expiresAt->timezone(config('app.timezone'))->format('d M Y') }}
                                @else
                                    —
                                @endif
                            </span>
                        </div>
                        <div class="billing-hero-meta-item">
                            <span class="label">Trial</span>
                            <span class="value">
                                @if ($onTrial)
                                    Active
                                @elseif ($trialUsed)
                                    Used
                                @else
                                    Available
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Same package cards as dashboard --}}
            <div class="mb-4">
                @include('client.back.partials.package')
            </div>

            <section class="card border shadow-none mb-4">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-receipt"></i>
                        <h5 class="mb-0 fs-6">Recent payments</h5>
                    </div>
                    <a href="{{ route('client.billing.transactions') }}" class="small text-decoration-none">View all</a>
                </div>
                <div class="card-body p-0">
                    @if ($recentOrders->isEmpty())
                        <div class="billing-history-empty">
                            <div class="empty-icon"><i class="bi bi-wallet2"></i></div>
                            <h6 class="mb-1">No payments yet</h6>
                            <p class="text-muted small mb-0">Mobile-money receipts will appear here after you pay.</p>
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
                                    @foreach ($recentOrders as $order)
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
            </section>

            <a href="{{ route('dashboard') }}#platform-subscription" class="btn btn-link text-decoration-none px-0 mb-2">
                <i class="bi bi-arrow-left me-1"></i> Back to dashboard
            </a>
        </div>
    </div>

    @if ($showPaywall)
        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var target = document.getElementById('platform-subscription');
                if (target && typeof target.scrollIntoView === 'function') {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        </script>
        @endpush
    @endif
</x-app-layout>
