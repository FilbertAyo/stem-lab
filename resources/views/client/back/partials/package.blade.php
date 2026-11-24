{{-- Platform subscription packages --}}
@php
  $packageUser = Auth::user();
  $isStudent = $packageUser?->role === 'student';
  $isLinkedStudent = $isStudent && $packageUser->hasPublicSchoolAccess();
  $isUnlinkedStudent = $isStudent && ! $packageUser->isClubLinked();
  $publicSchoolPackage = $subscriptionPackages->firstWhere('slug', 'public-school');
  $yearlyPackage = $yearlyPackage ?? null;
  $monthlyPackage = $subscriptionPackages->firstWhere('slug', 'individual');
  $twelveMonthCost = $monthlyPackage ? ((int) $monthlyPackage->price_amount_tzs * 12) : 0;
  $yearlyCharge = $yearlyPackage ? (int) $yearlyPackage->price_amount_tzs : 0;
  $yearlySavings = max(0, $twelveMonthCost - $yearlyCharge);
  $activePackageSlug = $packageUser?->activeSubscriptionPackageSlug();
  $hasLockedPlan = $packageUser?->hasLockedSubscriptionPlan() ?? false;
  $paymentsEnabled = (bool) config('subscriptions.payments_enabled', false);
  $trialUsed = $packageUser?->trial_used_at !== null;
  $hasActivePrepaid = $packageUser?->hasActivePrepaidAccess() ?? false;
  $expiresAt = $packageUser?->subscription_expires_at;
  $secondsLeft = ($expiresAt && ! $expiresAt->isPast()) ? ($expiresAt->getTimestamp() - time()) : 0;
  $prepaidDaysLeft = $secondsLeft > 0 ? (int) ceil($secondsLeft / 86400) : 0;
  $prepaidEndsToday = $secondsLeft > 0 && $prepaidDaysLeft <= 1;
  $onTrial = $hasActivePrepaid && $packageUser?->subscription_tier === 'trial';
  $dashboardPackages = $subscriptionPackages->reject(fn ($item) => $item->slug === 'individual-yearly');
  $embedInModal = $embedInModal ?? false;
@endphp

@unless ($embedInModal)
<div class="col-12 mb-3" id="platform-subscription">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
    <h5 class="text-muted mb-0 fw-semibold">
     Platform subscription
    </h5>
    @unless (request()->routeIs('client.billing.index'))
      <a href="{{ route('client.billing.index') }}" class="small text-decoration-none">My plan</a>
    @endunless
  </div>
</div>
@endunless

@if($isLinkedStudent && $publicSchoolPackage)
  <div class="row">
    <div class="col-lg-8 col-xl-6">
      <div class="card border shadow-none w-100 border-success-subtle">
        <div class="card-body p-7 p-xxl-5 d-flex flex-column gap-6">
          <div>
            <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle mb-3">Active plan</span>
            <h5 class="mb-2 fw-medium">{{ $publicSchoolPackage->linked_title ?: $publicSchoolPackage->name }}</h5>
            <p class="text-muted mb-0">
              {{ $publicSchoolPackage->linked_description ?: $publicSchoolPackage->description }}
            </p>
          </div>
          <ul class="list-unstyled d-flex flex-column gap-3 mb-0">
            @foreach ($publicSchoolPackage->linkedFeatureList() as $feature)
              <li class="hstack gap-3">
                <span class="round-32 rounded-circle bg-primary flex-shrink-0 hstack justify-content-center"><iconify-icon icon="lucide:check" class="fs-6 text-dark"></iconify-icon></span>
                <h6 class="mb-0 fw-normal">{{ $feature }}</h6>
              </li>
            @endforeach
          </ul>
          <a href="{{ route('client.student-club.index') }}" class="btn btn-outline-dark d-inline-flex align-items-center gap-2 w-100 justify-content-center">
            <span class="fw-semibold">View my club</span>
            <iconify-icon icon="lucide:arrow-up-right" class="fs-5"></iconify-icon>
          </a>
        </div>
      </div>
    </div>
  </div>
@else
<div class="row">
  @foreach ($dashboardPackages as $package)
    @php
      $isActivePackage = $activePackageSlug === $package->slug;
      $ctaAction = $package->resolvedCtaAction($isUnlinkedStudent);
      $isIndividualCard = $package->slug === 'individual';
      $isPaidPackage = in_array($package->slug, ['free-trial', 'individual'], true);
      $trialUnavailable = $package->slug === 'free-trial' && $trialUsed;
      $canRenewIndividual = $isIndividualCard && $paymentsEnabled && ! $hasLockedPlan;
      $packageActionDisabled = ($isActivePackage && ! $canRenewIndividual)
        || ($hasLockedPlan && ! $isActivePackage)
        || ($isPaidPackage && ! $paymentsEnabled)
        || $trialUnavailable
        || ($package->slug === 'free-trial' && $hasActivePrepaid && ! $isActivePackage);
      $ctaLabel = $package->cta_label;
      if ($isIndividualCard && $paymentsEnabled && ! $hasLockedPlan) {
          if ($hasActivePrepaid) {
              $ctaLabel = 'Extend with mobile money';
          } elseif ($trialUsed || filled($packageUser?->subscription_tier)) {
              $ctaLabel = 'Renew with mobile money';
          } else {
              $ctaLabel = 'Pay with mobile money';
          }
      }
    @endphp
    <div class="col-lg-6 col-xl-4 mb-7 mb-xl-0 d-flex align-items-stretch">
      <div class="card border shadow-none w-100 {{ $isActivePackage ? 'border-success-subtle' : '' }}" @if($isIndividualCard) data-individual-card @endif data-aos="fade-up" data-aos-delay="{{ $package->sort_order * 100 }}" data-aos-duration="1000">
        <div class="card-body p-7 p-xxl-5 d-flex flex-column gap-8">
          <div class="d-flex flex-column gap-6">
            <div class="hstack gap-3 flex-wrap">
              <h5 class="mb-0 fw-medium">{{ $package->name }}</h5>
              @if ($isActivePackage)
                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">Your plan</span>
              @endif
              @if ($package->slug === 'free-trial' && $onTrial)
                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">
                  @if ($prepaidEndsToday)
                    Ends today
                  @else
                    {{ $prepaidDaysLeft }} {{ $prepaidDaysLeft === 1 ? 'day' : 'days' }} left
                  @endif
                </span>
              @endif
              @if ($package->badge_label && ! $isIndividualCard)
                <span class="badge text-bg-dark hstack gap-2"><iconify-icon icon="lucide:flame" class="fs-5"></iconify-icon>{{ $package->badge_label }}</span>
              @endif
              @if ($isIndividualCard)
                <span class="badge text-bg-dark hstack gap-2 js-individual-badge" data-monthly-badge="{{ $package->badge_label }}" data-yearly-badge="{{ $yearlyPackage?->badge_label }}" @if(! $package->badge_label) hidden @endif>
                  <iconify-icon icon="lucide:flame" class="fs-5"></iconify-icon>
                  <span>{{ $package->badge_label }}</span>
                </span>
              @endif
            </div>
            @if ($isIndividualCard && $yearlyPackage)
              <div class="btn-group" role="group" aria-label="Billing period">
                <button type="button" class="btn btn-sm btn-dark js-period-btn" data-period="monthly" aria-pressed="true">Monthly</button>
                <button type="button" class="btn btn-sm btn-outline-dark js-period-btn" data-period="yearly" aria-pressed="false">Yearly</button>
              </div>
            @endif
            @if ($isIndividualCard && $yearlyPackage)
              <div class="js-period-copy" data-period="monthly">
                <div class="hstack gap-2 @if($package->price_compare) flex-wrap @endif">
                  @if ($package->price_compare)
                    <h3 class="mb-0 text-opacity-50 text-dark"><del>{{ $package->price_compare }}</del></h3>
                  @endif
                  @if ($package->price_heading)
                    <h3 class="mb-0">{{ $package->price_heading }}</h3>
                  @endif
                  @if ($package->price_subheading)
                    <p class="mb-0">{{ $package->price_subheading }}</p>
                  @endif
                </div>
              </div>
              <div class="js-period-copy d-none" data-period="yearly">
                <div class="hstack gap-2 flex-wrap">
                  @if ($twelveMonthCost > 0)
                    <h3 class="mb-0 text-opacity-50 text-dark"><del>{{ number_format($twelveMonthCost) }}</del></h3>
                  @endif
                  @if ($yearlyPackage->price_heading)
                    <h3 class="mb-0">{{ $yearlyPackage->price_heading }}</h3>
                  @endif
                  @if ($yearlyPackage->price_subheading)
                    <p class="mb-0">{{ $yearlyPackage->price_subheading }}</p>
                  @endif
                </div>
                @if ($yearlySavings > 0)
                  <p class="small text-success mb-0">Save {{ number_format($yearlySavings) }} TZS compared with 12 monthly payments.</p>
                @endif
              </div>
            @else
              <div class="hstack gap-2 @if($package->price_compare) flex-wrap @endif">
                @if ($package->price_compare)
                  <h3 class="mb-0 text-opacity-50 text-dark"><del>{{ $package->price_compare }}</del></h3>
                @endif
                @if ($package->price_heading)
                  <h3 class="mb-0">{{ $package->price_heading }}</h3>
                @endif
                @if ($package->price_subheading)
                  <p class="mb-0">{{ $package->price_subheading }}</p>
                @endif
              </div>
            @endif
            @if ($package->description)
              <p class="mb-0 js-period-description" data-monthly-description="{{ $package->description }}" @if($yearlyPackage) data-yearly-description="{{ $yearlyPackage->description }}" @endif>{{ $package->description }}</p>
            @endif
          </div>
          @if (! empty($package->features))
            <div class="pt-8 border-top d-flex flex-column gap-6">
              <h6 class="mb-0 fw-normal">What’s included:</h6>
              <ul class="list-unstyled d-flex flex-column gap-3 mb-0">
                @foreach ($package->features as $feature)
                  <li class="hstack gap-3">
                    <span class="round-32 rounded-circle bg-primary flex-shrink-0 hstack justify-content-center"><iconify-icon icon="lucide:check" class="fs-6 text-dark"></iconify-icon></span>
                    <h6 class="mb-0 fw-normal">{{ $feature }}</h6>
                  </li>
                @endforeach
              </ul>
            </div>
          @endif
          @if ($isActivePackage)
            <button type="button" class="btn btn-outline-success d-inline-flex align-items-center gap-2 w-100 justify-content-center" disabled>
              <span class="fw-semibold">Current plan</span>
              <iconify-icon icon="lucide:check" class="fs-5"></iconify-icon>
            </button>
            @if ($isIndividualCard && $paymentsEnabled && ! $hasLockedPlan)
              <a href="#" class="btn btn-link text-decoration-none d-inline-flex align-items-center gap-2 w-100 justify-content-center js-package-action px-0" data-package="{{ $ctaAction }}" data-slug="{{ $package->slug }}" @if($yearlyPackage) data-yearly-slug="{{ $yearlyPackage->slug }}" data-monthly-label="{{ $ctaLabel }}" data-yearly-label="Extend yearly" @endif>
                <span class="fw-semibold js-package-cta-label small">{{ $ctaLabel }}</span>
                <iconify-icon icon="lucide:arrow-up-right" class="fs-6"></iconify-icon>
              </a>
            @endif
          @elseif ($packageActionDisabled)
            <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 w-100 justify-content-center" disabled>
              <span class="fw-semibold">
                @if ($hasLockedPlan)
                  Included in your plan
                @elseif ($trialUnavailable)
                  Trial already used
                @elseif ($isPaidPackage)
                  Coming soon
                @else
                  Unavailable
                @endif
              </span>
            </button>
          @else
            <a href="#" class="btn btn-dark d-inline-flex align-items-center gap-2 w-100 justify-content-center js-package-action" data-package="{{ $ctaAction }}" data-slug="{{ $package->slug }}" @if($isIndividualCard && $yearlyPackage) data-yearly-slug="{{ $yearlyPackage->slug }}" data-monthly-label="{{ $ctaLabel }}" data-yearly-label="{{ $hasActivePrepaid ? 'Extend yearly' : 'Pay yearly' }}" @endif>
              <span class="fw-semibold js-package-cta-label">{{ $ctaLabel }}</span>
              <iconify-icon icon="lucide:arrow-up-right" class="fs-5"></iconify-icon>
            </a>
          @endif
        </div>
      </div>
    </div>
  @endforeach
</div>
@endif

@push('modals')
{{-- Modal: Public secondary school – teachers --}}
<div class="modal fade" id="public-school-modal-teacher" tabindex="-1" aria-labelledby="public-school-modal-teacher-label" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-semibold" id="public-school-modal-teacher-label">Public secondary school – free access for schools</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-2">
        <p class="text-muted small mb-4 lh-lg">
          Adilisha STEM Labs promotes gender equity and inclusive learning by creating a safe, accessible, and engaging digital STEM environment for all learners. Our platform prioritizes underserved public schools with free subscription access, ensuring students in rural and marginalized communities can participate in practical experiments and project-based learning.
        </p>
        <p class="text-muted small mb-4 lh-lg">
          STEM Clubs within schools require at least 70% girl membership, directly encouraging female participation and building confidence through collaborative, hands-on activities. Virtual content features female scientists and mentors to challenge stereotypes and inspire girls in STEM.
        </p>
        <div class="border-top pt-4">
          <p class="small fw-medium mb-2">Register your school for free access</p>
          <p class="small text-muted mb-3">
            Join <strong>Chomoza STEM Labs</strong> as a teacher or school representative. Once your request is approved, your school won’t need to subscribe—you’ll have full platform access at 0 TZS/month, including student roster management and weekly sessions.
          </p>
          @if ($publicSchoolClub)
            <a href="{{ route('client.clubs.join.form', $publicSchoolClub) }}" class="btn btn-dark d-inline-flex align-items-center gap-2 w-100 justify-content-center">
              <span class="fw-semibold">Join {{ $publicSchoolClub->name }}</span>
              <iconify-icon icon="lucide:arrow-up-right" class="fs-5"></iconify-icon>
            </a>
          @else
            <a href="{{ route('client.clubs.index') }}" class="btn btn-dark d-inline-flex align-items-center gap-2 w-100 justify-content-center">
              <span class="fw-semibold">Browse STEM clubs</span>
              <iconify-icon icon="lucide:arrow-up-right" class="fs-5"></iconify-icon>
            </a>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Modal: Public secondary school – students --}}
<div class="modal fade" id="public-school-modal-student" tabindex="-1" aria-labelledby="public-school-modal-student-label" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-semibold" id="public-school-modal-student-label">Public secondary school – free access for students</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-2">
        <p class="text-muted small mb-4 lh-lg">
          If you attend a registered public secondary school and belong to a STEM club on this platform, you can use Adilisha STEM Labs at no cost. The same STEM resources, 3D models, VR experiments, and quizzes are available to you at 0 TZS/month when your club membership is verified.
        </p>
        <p class="text-muted small mb-4 lh-lg">
          Your STEM club teacher uploads the club student list and shares two codes with you: your personal <strong>admission number</strong> and your school’s <strong>club code</strong>. You’ll use both to link your account to your club.
        </p>
        <div class="bg-light rounded p-3 mb-4">
          <p class="small fw-medium mb-2">How to get free access</p>
          <ol class="small text-muted mb-0 ps-3">
            <li class="mb-2">Ask your STEM club teacher for your admission number and club code.</li>
            <li class="mb-2">Go to <strong>Activities → My Club</strong> on this platform.</li>
            <li>Enter both codes, confirm your roster details, and link your account.</li>
          </ol>
        </div>
        <div class="border-top pt-4">
          <p class="small fw-medium mb-2">Ready to link your club?</p>
          <p class="small text-muted mb-3">
            You’ll need the admission number and club code from your teacher. Linking confirms you are on your school’s STEM club roster.
          </p>
          <a href="{{ route('client.student-club.index') }}" class="btn btn-dark d-inline-flex align-items-center gap-2 w-100 justify-content-center">
            <span class="fw-semibold">Link with your club</span>
            <iconify-icon icon="lucide:arrow-up-right" class="fs-5"></iconify-icon>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<form id="package-select-form" method="POST" action="{{ route('client.subscription-packages.select') }}" class="d-none" @if(! $paymentsEnabled) data-disabled="1" @endif>
  @csrf
  <input type="hidden" name="package" id="package-select-input" value="">
</form>
@endpush

{{-- Toast: subscription action feedback --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
  <div id="package-action-toast" class="toast align-items-center border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body d-flex align-items-center gap-3">
        <span class="rounded-circle bg-warning bg-opacity-25 p-2 d-flex align-items-center justify-content-center flex-shrink-0">
          <iconify-icon icon="lucide:info" class="fs-5 text-warning"></iconify-icon>
        </span>
        <div>
          <strong class="d-block mb-1">Request could not be completed</strong>
          <p class="mb-0 small text-muted">We're unable to process your request at the moment. Please try again in a few moments. If this continues, contact our support team.</p>
        </div>
      </div>
      <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

@push('scripts')
<script>
(function() {
  var selectForm = document.getElementById('package-select-form');
  var selectInput = document.getElementById('package-select-input');
  var packageSubmitting = false;

  function lockPackageActions() {
    packageSubmitting = true;
    document.querySelectorAll('.js-package-action').forEach(function(btn) {
      btn.disabled = true;
      btn.setAttribute('aria-disabled', 'true');
    });
    if (selectForm) {
      if (window.StemLabFormGuard) {
        window.StemLabFormGuard.lock(selectForm);
      } else {
        selectForm.dataset.submitting = '1';
      }
    }
  }

  document.querySelectorAll('[data-individual-card]').forEach(function(card) {
    var buttons = card.querySelectorAll('.js-period-btn');
    var copies = card.querySelectorAll('.js-period-copy');
    var action = card.querySelector('.js-package-action');
    var badge = card.querySelector('.js-individual-badge');
    var description = card.querySelector('.js-period-description');

    function setPeriod(period) {
      buttons.forEach(function(btn) {
        var active = btn.getAttribute('data-period') === period;
        btn.classList.toggle('btn-dark', active);
        btn.classList.toggle('btn-outline-dark', !active);
        btn.setAttribute('aria-pressed', active ? 'true' : 'false');
      });
      copies.forEach(function(copy) {
        copy.classList.toggle('d-none', copy.getAttribute('data-period') !== period);
      });
      if (action) {
        var yearlySlug = action.getAttribute('data-yearly-slug');
        if (yearlySlug) {
          action.setAttribute('data-slug', period === 'yearly' ? yearlySlug : 'individual');
          var label = action.querySelector('.js-package-cta-label');
          if (label) {
            label.textContent = period === 'yearly'
              ? (action.getAttribute('data-yearly-label') || 'Pay yearly')
              : (action.getAttribute('data-monthly-label') || 'Pay with mobile money');
          }
        }
      }
      if (badge) {
        var text = period === 'yearly' ? badge.getAttribute('data-yearly-badge') : badge.getAttribute('data-monthly-badge');
        var span = badge.querySelector('span');
        if (text) {
          badge.hidden = false;
          if (span) span.textContent = text;
        } else {
          badge.hidden = true;
        }
      }
      if (description) {
        var next = period === 'yearly'
          ? description.getAttribute('data-yearly-description')
          : description.getAttribute('data-monthly-description');
        if (next) {
          description.textContent = next;
        }
      }
    }

    buttons.forEach(function(btn) {
      btn.addEventListener('click', function() {
        setPeriod(btn.getAttribute('data-period') || 'monthly');
      });
    });
  });

  document.querySelectorAll('.js-package-action').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      if (packageSubmitting) {
        return;
      }

      var packageType = btn.getAttribute('data-package');
      var packageSlug = btn.getAttribute('data-slug');
      var modalId = null;

      if (packageType === 'public-school-teacher') {
        modalId = 'public-school-modal-teacher';
      } else if (packageType === 'public-school-student') {
        modalId = 'public-school-modal-student';
      }

      if (modalId) {
        var modalEl = document.getElementById(modalId);
        if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
          var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
          modal.show();
        }
        return;
      }

      if (selectForm && selectInput && selectForm.getAttribute('data-disabled') !== '1' && (packageSlug === 'free-trial' || packageSlug === 'individual' || packageSlug === 'individual-yearly')) {
        selectInput.value = packageSlug;
        lockPackageActions();
        selectForm.submit();
        return;
      }

      if (packageSlug === 'free-trial' || packageSlug === 'individual' || packageSlug === 'individual-yearly') {
        return;
      }

      var toastEl = document.getElementById('package-action-toast');
      if (toastEl && typeof bootstrap !== 'undefined' && bootstrap.Toast) {
        var toast = bootstrap.Toast.getOrCreateInstance(toastEl);
        toast.show();
      }
    });
  });
})();
</script>
@endpush
