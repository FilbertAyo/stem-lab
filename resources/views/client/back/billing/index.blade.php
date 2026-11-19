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
@endphp

<x-app-layout>
    <div class="col-lg-12">
        <div class="dashboard-content-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h4 class="mb-1">My plan</h4>
                <p class="text-muted mb-0">Manage prepaid access, school membership, and mobile-money payments.</p>
            </div>
            <a href="{{ route('client.billing.transactions') }}" class="btn btn-outline-dark btn-sm">Billing history</a>
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

        <div class="row g-4 mb-4">
            <div class="col-lg-5">
                <div class="card border shadow-none h-100 {{ $hasAccess ? 'border-success-subtle' : 'border-warning-subtle' }}">
                    <div class="card-body p-7 d-flex flex-column gap-4">
                        <div>
                            <span class="badge {{ $user->platformPlanBadgeClass() }} mb-3">{{ $user->platformPlanLabel() }}</span>
                            <h5 class="mb-2">Current access</h5>
                            @if ($hasLockedPlan)
                                <p class="text-muted mb-0">Your STEM club covers platform access at no cost. You do not need to pay while this membership stays approved.</p>
                            @elseif ($onTrial)
                                <p class="text-muted mb-0">
                                    You are on a 7-day trial
                                    @if ($endsToday)
                                        that ends today.
                                    @elseif ($expiresAt)
                                        with {{ $daysLeft }} {{ $daysLeft === 1 ? 'day' : 'days' }} remaining (until {{ $expiresAt->timezone(config('app.timezone'))->format('d M Y') }}).
                                    @else
                                        .
                                    @endif
                                    You can pay anytime to keep access.
                                </p>
                            @elseif ($onIndividual)
                                <p class="text-muted mb-0">
                                    Individual prepaid access
                                    @if ($expiresAt)
                                        is active until <strong>{{ $expiresAt->timezone(config('app.timezone'))->format('d M Y') }}</strong>.
                                    @else
                                        is active.
                                    @endif
                                    This plan does not auto-renew.
                                </p>
                            @elseif ($trialUsed)
                                <p class="text-muted mb-0">Your free trial has already been used. Subscribe with mobile money or join a public-school STEM club to continue.</p>
                            @else
                                <p class="text-muted mb-0">Subscribe or join a public-school STEM club to continue using learning features.</p>
                            @endif
                        </div>
                        <div class="pt-3 border-top small text-muted">
                            Trial status:
                            @if ($onTrial)
                                active
                            @elseif ($trialUsed)
                                already used
                            @else
                                available
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card border shadow-none h-100">
                    <div class="card-body p-7 d-flex flex-column gap-4">
                        <div>
                            <h5 class="mb-2">Pay with mobile money</h5>
                            <p class="text-muted mb-0">Prepaid access for 30 days or 365 days. Yearly is cheaper than paying 12 months.</p>
                        </div>

                        @if ($hasLockedPlan)
                            <p class="mb-0">School access is already included. You do not need an Individual plan.</p>
                        @elseif (! $paymentsEnabled)
                            <p class="mb-0 text-muted">Online subscription is coming soon.</p>
                        @else
                            <div class="row g-3">
                                @if ($monthlyPackage)
                                    <div class="col-md-6">
                                        <div class="border rounded p-4 h-100 d-flex flex-column gap-3">
                                            <div>
                                                <div class="small text-muted text-uppercase">Monthly</div>
                                                <div class="fs-4 fw-semibold">{{ $monthlyPackage->price_heading }}</div>
                                                <div class="small text-muted">{{ $monthlyPackage->price_subheading }}</div>
                                            </div>
                                            <form method="POST" action="{{ route('client.subscription-packages.select') }}">
                                                @csrf
                                                <input type="hidden" name="package" value="individual">
                                                <button type="submit" class="btn btn-dark w-100">
                                                    {{ $onIndividual ? 'Extend 30 days' : 'Pay monthly' }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                                @if ($yearlyPackage)
                                    <div class="col-md-6">
                                        <div class="border rounded p-4 h-100 d-flex flex-column gap-3">
                                            <div>
                                                <div class="small text-muted text-uppercase">Yearly</div>
                                                <div class="fs-4 fw-semibold">
                                                    @if ($twelveMonthCost > 0)
                                                        <del class="text-muted fs-6 me-1">{{ number_format($twelveMonthCost) }}</del>
                                                    @endif
                                                    {{ $yearlyPackage->price_heading }}
                                                </div>
                                                <div class="small text-muted">{{ $yearlyPackage->price_subheading }}</div>
                                                @if ($yearlySavings > 0)
                                                    <div class="small text-success mt-1">Save {{ number_format($yearlySavings) }} TZS vs 12 months</div>
                                                @endif
                                            </div>
                                            <form method="POST" action="{{ route('client.subscription-packages.select') }}">
                                                @csrf
                                                <input type="hidden" name="package" value="individual-yearly">
                                                <button type="submit" class="btn btn-outline-dark w-100">
                                                    {{ $onIndividual ? 'Extend 365 days' : 'Pay yearly' }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if (! $hasLockedPlan && in_array($user->role, ['teacher', 'student'], true))
            <div class="card border shadow-none mb-4">
                <div class="card-body p-7">
                    <h5 class="mb-2">Get free school access</h5>
                    <p class="text-muted mb-4">Public secondary schools stay free through an approved STEM club. Teachers join a club; students link with an admission number and club code.</p>
                    <div class="d-flex flex-wrap gap-3">
                        @if ($user->role === 'student')
                            <a href="{{ route('client.student-club.index') }}" class="btn btn-dark">Link with your club</a>
                        @else
                            @if ($publicSchoolClub)
                                <a href="{{ route('client.clubs.join.form', $publicSchoolClub) }}" class="btn btn-dark">Join {{ $publicSchoolClub->name }}</a>
                            @endif
                            <a href="{{ route('client.clubs.index') }}" class="btn btn-outline-dark">Browse STEM clubs</a>
                        @endif
                    </div>
                </div>
            </div>
        @endif
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
                        <p class="text-muted mb-4">Learning features are paused until you subscribe or join a public-school STEM club. Your dashboard, profile, and billing stay available.</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="border rounded p-4 h-100">
                                    <h6 class="mb-2">Pay with mobile money</h6>
                                    <p class="small text-muted mb-3">Prepaid monthly or yearly Individual access. No auto-renewal.</p>
                                    @if ($paymentsEnabled)
                                        <div class="d-flex flex-column gap-2">
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
                                        <p class="small text-muted mb-0">Online payment is coming soon.</p>
                                    @endif
                                </div>
                            </div>
                            @if (in_array($user->role, ['teacher', 'student'], true))
                            <div class="col-md-6">
                                <div class="border rounded p-4 h-100">
                                    <h6 class="mb-2">Get free school access</h6>
                                    <p class="small text-muted mb-3">Approved public-school STEM clubs keep teachers and linked students on free access.</p>
                                    @if ($user->role === 'student')
                                        <a href="{{ route('client.student-club.index') }}" class="btn btn-outline-dark w-100">Link with your club</a>
                                    @elseif ($publicSchoolClub)
                                        <a href="{{ route('client.clubs.join.form', $publicSchoolClub) }}" class="btn btn-outline-dark w-100">Join a STEM club</a>
                                    @else
                                        <a href="{{ route('client.clubs.index') }}" class="btn btn-outline-dark w-100">Browse STEM clubs</a>
                                    @endif
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
