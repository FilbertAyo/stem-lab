@php
  $paymentsEnabled = (bool) config('subscriptions.payments_enabled', false);
  $hasLockedPlan = $user->hasLockedSubscriptionPlan();
  $hasAccess = $user->hasPlatformAccess();
  $onTrial = $user->hasActivePrepaidAccess() && $user->subscription_tier === 'trial';
  $onIndividual = $user->hasActivePrepaidAccess() && in_array($user->subscription_tier, ['individual', 'pro'], true);
  $expiresAt = $user->subscription_expires_at;
  $secondsLeft = ($expiresAt && $expiresAt->isFuture()) ? ($expiresAt->getTimestamp() - time()) : 0;
  $daysLeft = $secondsLeft > 0 ? (int) ceil($secondsLeft / 86400) : 0;
  $endsToday = $secondsLeft > 0 && $daysLeft <= 1;
  $trialUsed = $user->trial_used_at !== null;
  $twelveMonthCost = $monthlyPackage ? ((int) $monthlyPackage->price_amount_tzs * 12) : 0;
  $yearlyCharge = $yearlyPackage ? (int) $yearlyPackage->price_amount_tzs : 0;
  $yearlySavings = max(0, $twelveMonthCost - $yearlyCharge);
  $paywallTitle = $trialUsed && ! $hasAccess ? 'Your trial has ended' : 'Access paused';
  $canShowSchoolPath = ! $hasLockedPlan && in_array($user->role, ['teacher', 'student'], true);
  $canPay = $paymentsEnabled && ! $hasLockedPlan;
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
      $trialUsed => 'Pay with mobile money, or join a public-school STEM club for free access.',
      default => 'Pay with mobile money for Individual access, or join a public-school STEM club if your school is eligible.',
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
                    <p class="text-muted mb-0">Current access, mobile-money payments, and school free path.</p>
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

            @if ($hasLockedPlan)
                <section class="billing-covered mb-4">
                    <div class="check"><i class="bi bi-check-lg"></i></div>
                    <h5 class="billing-section-title">Public secondary school</h5>
                    <p class="billing-section-copy mb-4" style="max-width: 36rem;">
                        Full platform access is included through your approved STEM club. Manage club membership from Activities if you need to change schools.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        @if ($user->role === 'teacher')
                            <a href="{{ route('client.clubs.index') }}" class="btn btn-dark">My clubs</a>
                        @elseif ($user->role === 'student')
                            <a href="{{ route('client.student-club.index') }}" class="btn btn-dark">My club</a>
                        @endif
                        <a href="{{ route('dashboard') }}#platform-subscription" class="btn btn-outline-dark">View packages</a>
                    </div>
                </section>
            @else
                <div class="row g-4 mb-4">
                    <div class="{{ $canShowSchoolPath ? 'col-lg-7' : 'col-12' }}">
                        <div class="billing-path-card">
                            <div class="billing-path-icon">
                                <i class="bi bi-phone"></i>
                            </div>
                            <h5 class="billing-section-title">Pay with mobile money</h5>
                            <p class="billing-section-copy mb-4">
                                Prepaid Individual access. Choose 30 days or a discounted year. No auto-renewal.
                            </p>

                            @if (! $paymentsEnabled)
                                <div class="alert alert-warning mb-4">
                                    <strong>Checkout is not live yet.</strong>
                                    <span class="small d-block">Prices are ready; pay buttons unlock when payments are enabled.</span>
                                </div>
                            @endif

                            <div class="row g-3">
                                @if ($monthlyPackage)
                                    <div class="col-md-6">
                                        <div class="billing-plan-option">
                                            <div>
                                                <div class="period-label">Monthly</div>
                                                <div class="price-row mt-2">
                                                    @if ($monthlyPackage->price_compare)
                                                        <span class="price-compare">{{ $monthlyPackage->price_compare }}</span>
                                                    @endif
                                                    <span class="price-amount">{{ $monthlyPackage->price_heading }}</span>
                                                    <span class="price-unit">{{ $monthlyPackage->price_subheading }}</span>
                                                </div>
                                                <p class="small text-muted mb-0 mt-2">{{ (int) $monthlyPackage->billing_period_days }} days of full access.</p>
                                            </div>
                                            <div class="option-foot">
                                                @if ($canPay)
                                                    <form method="POST" action="{{ route('client.subscription-packages.select') }}">
                                                        @csrf
                                                        <input type="hidden" name="package" value="individual">
                                                        <button type="submit" class="btn btn-dark w-100">
                                                            {{ $onIndividual ? 'Extend 30 days' : 'Pay monthly' }}
                                                        </button>
                                                    </form>
                                                @else
                                                    <button type="button" class="btn btn-outline-secondary w-100" disabled>Coming soon</button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if ($yearlyPackage)
                                    <div class="col-md-6">
                                        <div class="billing-plan-option is-featured">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-center gap-2">
                                                    <div class="period-label mb-0">Yearly</div>
                                                    @if ($yearlySavings > 0)
                                                        <span class="badge text-bg-dark">Best value</span>
                                                    @endif
                                                </div>
                                                <div class="price-row mt-2">
                                                    @if ($twelveMonthCost > 0)
                                                        <span class="price-compare">{{ number_format($twelveMonthCost) }}</span>
                                                    @endif
                                                    <span class="price-amount">{{ $yearlyPackage->price_heading }}</span>
                                                    <span class="price-unit">{{ $yearlyPackage->price_subheading }}</span>
                                                </div>
                                                @if ($yearlySavings > 0)
                                                    <p class="save-note mb-0 mt-2">Save {{ number_format($yearlySavings) }} TZS vs 12 months</p>
                                                @endif
                                                <p class="small text-muted mb-0 mt-2">{{ (int) $yearlyPackage->billing_period_days }} days — same Individual features.</p>
                                            </div>
                                            <div class="option-foot">
                                                @if ($canPay)
                                                    <form method="POST" action="{{ route('client.subscription-packages.select') }}">
                                                        @csrf
                                                        <input type="hidden" name="package" value="individual-yearly">
                                                        <button type="submit" class="btn btn-dark w-100">
                                                            {{ $onIndividual ? 'Extend 365 days' : 'Pay yearly' }}
                                                        </button>
                                                    </form>
                                                @else
                                                    <button type="button" class="btn btn-outline-secondary w-100" disabled>Coming soon</button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($canShowSchoolPath)
                        <div class="col-lg-5">
                            <div class="billing-path-card">
                                <div class="billing-path-icon is-school">
                                    <i class="bi bi-mortarboard"></i>
                                </div>
                                <h5 class="billing-section-title">Get free school access</h5>
                                <p class="billing-section-copy mb-4">
                                    Public secondary schools stay free through an approved STEM club.
                                </p>

                                @if ($user->role === 'student')
                                    <ul class="billing-steps">
                                        <li><span class="step-num">1</span><span>Get your admission number and club code from your teacher.</span></li>
                                        <li><span class="step-num">2</span><span>Open My Club and link your account.</span></li>
                                        <li><span class="step-num">3</span><span>Keep free access while you stay linked.</span></li>
                                    </ul>
                                    <a href="{{ route('client.student-club.index') }}" class="btn btn-outline-dark w-100">Link with your club</a>
                                @else
                                    <ul class="billing-steps">
                                        <li><span class="step-num">1</span><span>Join your school’s STEM club.</span></li>
                                        <li><span class="step-num">2</span><span>Wait for approval if required.</span></li>
                                        <li><span class="step-num">3</span><span>Approved teachers keep free school access.</span></li>
                                    </ul>
                                    <div class="d-flex flex-column gap-2">
                                        @if ($publicSchoolClub)
                                            <a href="{{ route('client.clubs.join.form', $publicSchoolClub) }}" class="btn btn-dark w-100">
                                                Join {{ $publicSchoolClub->name }}
                                            </a>
                                        @endif
                                        <a href="{{ route('client.clubs.index') }}" class="btn btn-outline-dark w-100">Browse STEM clubs</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endif

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
                <i class="bi bi-arrow-left me-1"></i> Back to dashboard packages
            </a>
        </div>
    </div>

    @if ($showPaywall)
        <div class="modal fade" id="billing-paywall-modal" tabindex="-1" aria-labelledby="billing-paywall-modal-label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-semibold" id="billing-paywall-modal-label">{{ $paywallTitle }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-2">
                        <p class="text-muted mb-4">Learning is paused until you subscribe or join a public-school STEM club.</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="billing-plan-option h-100">
                                    <div>
                                        <div class="period-label">Pay with mobile money</div>
                                        <p class="small text-muted mb-0 mt-2">Prepaid monthly or yearly Individual access.</p>
                                    </div>
                                    @if ($paymentsEnabled)
                                        <div class="d-flex flex-column gap-2 option-foot">
                                            <form method="POST" action="{{ route('client.subscription-packages.select') }}">
                                                @csrf
                                                <input type="hidden" name="package" value="individual">
                                                <button type="submit" class="btn btn-dark w-100">Pay monthly</button>
                                            </form>
                                            <form method="POST" action="{{ route('client.subscription-packages.select') }}">
                                                @csrf
                                                <input type="hidden" name="package" value="individual-yearly">
                                                <button type="submit" class="btn btn-outline-dark w-100">Pay yearly</button>
                                            </form>
                                        </div>
                                    @else
                                        <button type="button" class="btn btn-outline-secondary w-100" disabled>Coming soon</button>
                                    @endif
                                </div>
                            </div>
                            @if (in_array($user->role, ['teacher', 'student'], true))
                                <div class="col-md-6">
                                    <div class="billing-plan-option h-100">
                                        <div>
                                            <div class="period-label">Get free school access</div>
                                            <p class="small text-muted mb-0 mt-2">Join or link a public-school STEM club.</p>
                                        </div>
                                        <div class="option-foot">
                                            @if ($user->role === 'student')
                                                <a href="{{ route('client.student-club.index') }}" class="btn btn-outline-dark w-100">Link with your club</a>
                                            @elseif ($publicSchoolClub)
                                                <a href="{{ route('client.clubs.join.form', $publicSchoolClub) }}" class="btn btn-outline-dark w-100">Join a STEM club</a>
                                            @else
                                                <a href="{{ route('client.clubs.index') }}" class="btn btn-outline-dark w-100">Browse STEM clubs</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var el = document.getElementById('billing-paywall-modal');
                if (el && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(el).show();
                }
            });
        </script>
        @endpush
    @endif
</x-app-layout>
