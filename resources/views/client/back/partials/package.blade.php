{{-- Platform subscription packages --}}
@php
  $packageUser = Auth::user();
  $isStudent = $packageUser?->role === 'student';
  $isLinkedStudent = $isStudent && $packageUser->hasPublicSchoolAccess();
  $isUnlinkedStudent = $isStudent && ! $packageUser->isClubLinked();
  $publicSchoolPackage = $subscriptionPackages->firstWhere('slug', 'public-school');
  $activePackageSlug = $packageUser?->activeSubscriptionPackageSlug();
  $hasLockedPlan = $packageUser?->hasLockedSubscriptionPlan() ?? false;
  $paymentsEnabled = (bool) config('subscriptions.payments_enabled', false);
@endphp

<div class="col-12 mb-3" id="platform-subscription">
  <h5 class="text-muted mb-0 fw-semibold">
   Platform subscription
  </h5>
</div>

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
  @foreach ($subscriptionPackages as $package)
    @php
      $isActivePackage = $activePackageSlug === $package->slug;
      $ctaAction = $package->resolvedCtaAction($isUnlinkedStudent);
      $isPaidPackage = in_array($package->slug, ['free-trial', 'individual'], true);
      $packageActionDisabled = $isActivePackage
        || ($hasLockedPlan && ! $isActivePackage)
        || ($isPaidPackage && ! $paymentsEnabled);
    @endphp
    <div class="col-lg-6 col-xl-4 mb-7 mb-xl-0 d-flex align-items-stretch">
      <div class="card border shadow-none w-100 {{ $isActivePackage ? 'border-success-subtle' : '' }}" data-aos="fade-up" data-aos-delay="{{ $package->sort_order * 100 }}" data-aos-duration="1000">
        <div class="card-body p-7 p-xxl-5 d-flex flex-column gap-8">
          <div class="d-flex flex-column gap-6">
            <div class="hstack gap-3 flex-wrap">
              <h5 class="mb-0 fw-medium">{{ $package->name }}</h5>
              @if ($isActivePackage)
                <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">Your plan</span>
              @endif
              @if ($package->badge_label)
                <span class="badge text-bg-dark hstack gap-2"><iconify-icon icon="lucide:flame" class="fs-5"></iconify-icon>{{ $package->badge_label }}</span>
              @endif
            </div>
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
            @if ($package->description)
              <p class="mb-0">{{ $package->description }}</p>
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
          @elseif ($packageActionDisabled)
            <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 w-100 justify-content-center" disabled>
              <span class="fw-semibold">
                @if ($hasLockedPlan)
                  Included in your plan
                @elseif ($isPaidPackage)
                  Coming soon
                @else
                  Unavailable
                @endif
              </span>
            </button>
          @else
            <a href="#" class="btn btn-dark d-inline-flex align-items-center gap-2 w-100 justify-content-center js-package-action" data-package="{{ $ctaAction }}" data-slug="{{ $package->slug }}">
              <span class="fw-semibold">{{ $package->cta_label }}</span>
              <iconify-icon icon="lucide:arrow-up-right" class="fs-5"></iconify-icon>
            </a>
          @endif
        </div>
      </div>
    </div>
  @endforeach
</div>
@endif

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

{{-- Toast: subscription action placeholder (replace with real flow later) --}}
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

  document.querySelectorAll('.js-package-action').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
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

      if (selectForm && selectInput && selectForm.getAttribute('data-disabled') !== '1' && (packageSlug === 'free-trial' || packageSlug === 'individual')) {
        selectInput.value = packageSlug;
        selectForm.submit();
        return;
      }

      if (packageSlug === 'free-trial' || packageSlug === 'individual') {
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
