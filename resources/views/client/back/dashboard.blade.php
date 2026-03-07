@php
    $stats = $stats ?? [
        'total_answers' => 0,
        'correct_answers' => 0,
        'models_completed' => 0,
        'accuracy' => 0,
        'total_vr_attempts' => 0,
        'completed_vr_attempts' => 0,
        'passed_vr_attempts' => 0,
        'total_competitions' => 0,
        'total_submissions' => 0,
        'total_students' => 0,
    ];
    $latestModels = $latestModels ?? collect();
@endphp

<x-app-layout>


 <div class="col-lg-12">

        <div class="dashboard-content-header">
            <h2 class="title mb-0">Dashboard</h2>
        </div>

        {{-- Platform subscription packages --}}
        <div class="col-12 mb-3">
          <h5 class="text-muted mb-0 fw-semibold">
            <i class="bi bi-box-seam me-2"></i>Platform subscription
          </h5>
        </div>
        <div class="row">
          <div class="col-lg-6 col-xl-4 mb-7 mb-xl-0 d-flex align-items-stretch">
            <div class="card w-100" data-aos="fade-up" data-aos-delay="100" data-aos-duration="1000">
              <div class="card-body p-7 p-xxl-5 d-flex flex-column gap-8">
                <div class="d-flex flex-column gap-6">
                  <h5 class="mb-0 fw-medium">Free trial</h5>
                  <div class="hstack gap-2">
                    <h3 class="mb-0">7 days</h3>
                    <p class="mb-0">free</p>
                  </div>
                  <p class="mb-0">Ideal for anyone who wants to explore the platform before subscribing. Full access to STEM resources for one week.</p>
                </div>
                <div class="pt-8 border-top d-flex flex-column gap-6">
                  <h6 class="mb-0 fw-normal">What’s included:</h6>
                  <ul class="list-unstyled d-flex flex-column gap-3 mb-0">
                    <li class="hstack gap-3">
                      <span class="round-32 rounded-circle bg-primary flex-shrink-0 hstack justify-content-center"><iconify-icon icon="lucide:check" class="fs-6 text-dark"></iconify-icon></span>
                      <h6 class="mb-0 fw-normal">Full platform access for 7 days</h6>
                    </li>
                    <li class="hstack gap-3">
                      <span class="round-32 rounded-circle bg-primary flex-shrink-0 hstack justify-content-center"><iconify-icon icon="lucide:check" class="fs-6 text-dark"></iconify-icon></span>
                      <h6 class="mb-0 fw-normal">3D models and VR experiments</h6>
                    </li>
                    <li class="hstack gap-3">
                      <span class="round-32 rounded-circle bg-primary flex-shrink-0 hstack justify-content-center"><iconify-icon icon="lucide:check" class="fs-6 text-dark"></iconify-icon></span>
                      <h6 class="mb-0 fw-normal">VR quizzes and assessments</h6>
                    </li>
                    <li class="hstack gap-3">
                      <span class="round-32 rounded-circle bg-primary flex-shrink-0 hstack justify-content-center"><iconify-icon icon="lucide:check" class="fs-6 text-dark"></iconify-icon></span>
                      <h6 class="mb-0 fw-normal">Projects and learning resources</h6>
                    </li>
                  </ul>
                </div>
                <a href="#" class="btn w-100 justify-content-center js-package-action" data-package="free-trial">
                  <span class="btn-text">Start free trial</span>
                  <iconify-icon icon="lucide:arrow-up-right" class="btn-icon bg-white text-dark round-52 rounded-circle hstack justify-content-center fs-7 shadow-sm"></iconify-icon>
                </a>
              </div>
            </div>
          </div>
          <div class="col-lg-6 col-xl-4 mb-7 mb-xl-0 d-flex align-items-stretch">
            <div class="card w-100" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
              <div class="card-body p-7 p-xxl-5 d-flex flex-column gap-8">
                <div class="d-flex flex-column gap-6">
                  <div class="hstack gap-3">
                    <h5 class="mb-0 fw-medium">Individual</h5>
                    <span class="badge text-bg-dark hstack gap-2"><iconify-icon icon="lucide:flame" class="fs-5"></iconify-icon>Most popular</span>
                  </div>
                  <div class="hstack gap-2 flex-wrap">
                    <h3 class="mb-0 text-opacity-50 text-dark"><del>1,500</del></h3>
                    <h3 class="mb-0">1,000</h3>
                    <p class="mb-0">TZS /month</p>
                  </div>
                  <p class="mb-0">Perfect for teachers, students, and parents who want ongoing access to all STEM content, 3D models, VR experiments, and competitions.</p>
                </div>
                <div class="pt-8 border-top d-flex flex-column gap-6">
                  <h6 class="mb-0 fw-normal">What’s included:</h6>
                  <ul class="list-unstyled d-flex flex-column gap-3 mb-0">
                    <li class="hstack gap-3">
                      <span class="round-32 rounded-circle bg-primary flex-shrink-0 hstack justify-content-center"><iconify-icon icon="lucide:check" class="fs-6 text-dark"></iconify-icon></span>
                      <h6 class="mb-0 fw-normal">For teachers, students & parents</h6>
                    </li>
                    <li class="hstack gap-3">
                      <span class="round-32 rounded-circle bg-primary flex-shrink-0 hstack justify-content-center"><iconify-icon icon="lucide:check" class="fs-6 text-dark"></iconify-icon></span>
                      <h6 class="mb-0 fw-normal">All 3D models and VR experiments</h6>
                    </li>
                    <li class="hstack gap-3">
                      <span class="round-32 rounded-circle bg-primary flex-shrink-0 hstack justify-content-center"><iconify-icon icon="lucide:check" class="fs-6 text-dark"></iconify-icon></span>
                      <h6 class="mb-0 fw-normal">Competitions and club features (teachers)</h6>
                    </li>
                    <li class="hstack gap-3">
                      <span class="round-32 rounded-circle bg-primary flex-shrink-0 hstack justify-content-center"><iconify-icon icon="lucide:check" class="fs-6 text-dark"></iconify-icon></span>
                      <h6 class="mb-0 fw-normal">Weekly sessions and student management</h6>
                    </li>
                  </ul>
                </div>
                <a href="#" class="btn w-100 justify-content-center js-package-action" data-package="individual">
                  <span class="btn-text">Subscribe now</span>
                  <iconify-icon icon="lucide:arrow-up-right" class="btn-icon bg-white text-dark round-52 rounded-circle hstack justify-content-center fs-7 shadow-sm"></iconify-icon>
                </a>
              </div>
            </div>
          </div>
          <div class="col-lg-6 col-xl-4 mb-7 mb-xl-0 d-flex align-items-stretch">
            <div class="card w-100" data-aos="fade-up" data-aos-delay="300" data-aos-duration="1000">
              <div class="card-body p-7 p-xxl-5 d-flex flex-column gap-8">
                <div class="d-flex flex-column gap-6">
                  <h5 class="mb-0 fw-medium">Public secondary school</h5>
                  <div class="hstack gap-2">
                    <h3 class="mb-0">0</h3>
                    <p class="mb-0">TZS /month</p>
                  </div>
                  <p class="mb-0">Fully subsidised access for government-run secondary schools. Same platform features at no cost to support public education.</p>
                </div>
                <div class="pt-8 border-top d-flex flex-column gap-6">
                  <h6 class="mb-0 fw-normal">What’s included:</h6>
                  <ul class="list-unstyled d-flex flex-column gap-3 mb-0">
                    <li class="hstack gap-3">
                      <span class="round-32 rounded-circle bg-primary flex-shrink-0 hstack justify-content-center"><iconify-icon icon="lucide:check" class="fs-6 text-dark"></iconify-icon></span>
                      <h6 class="mb-0 fw-normal">Free access for registered public schools</h6>
                    </li>
                    <li class="hstack gap-3">
                      <span class="round-32 rounded-circle bg-primary flex-shrink-0 hstack justify-content-center"><iconify-icon icon="lucide:check" class="fs-6 text-dark"></iconify-icon></span>
                      <h6 class="mb-0 fw-normal">All STEM resources and activities</h6>
                    </li>
                    <li class="hstack gap-3">
                      <span class="round-32 rounded-circle bg-primary flex-shrink-0 hstack justify-content-center"><iconify-icon icon="lucide:check" class="fs-6 text-dark"></iconify-icon></span>
                      <h6 class="mb-0 fw-normal">3D models, VR experiments & quizzes</h6>
                    </li>
                    <li class="hstack gap-3">
                      <span class="round-32 rounded-circle bg-primary flex-shrink-0 hstack justify-content-center"><iconify-icon icon="lucide:check" class="fs-6 text-dark"></iconify-icon></span>
                      <h6 class="mb-0 fw-normal">Eligibility verified by school registration</h6>
                    </li>
                  </ul>
                </div>
                <a href="#" class="btn w-100 justify-content-center js-package-action" data-package="public-school">
                  <span class="btn-text">Learn more</span>
                  <iconify-icon icon="lucide:arrow-up-right" class="btn-icon bg-white text-dark round-52 rounded-circle hstack justify-content-center fs-7 shadow-sm"></iconify-icon>
                </a>
              </div>
            </div>
          </div>
        </div>

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

        <section class="row mb-4 mt-4">
            <div class="col-6 col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-none h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1 small">Models Completed</h6>
                                <h4 class="mb-0 fw-bold">{{ $stats['models_completed'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-none h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1 small">VR Assessments</h6>
                                <h4 class="mb-0 fw-bold">{{ $stats['completed_vr_attempts'] }}</h4>
                                @if($stats['total_vr_attempts'] > 0)
                                    <small class="text-muted">{{ $stats['passed_vr_attempts'] }} passed</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($stats['total_competitions'] > 0 || $stats['total_students'] > 0)
                <div class="col-6 col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-none h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1 small">Competitions</h6>
                                    <h4 class="mb-0 fw-bold">{{ $stats['total_competitions'] }}</h4>
                                    @if($stats['total_submissions'] > 0)
                                        <small class="text-muted">{{ $stats['total_submissions'] }} submissions</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($stats['total_students'] > 0)
                <div class="col-6 col-lg-3 col-md-6 mb-3">
                    <div class="card border-0 shadow-none h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1 small">My Students</h6>
                                    <h4 class="mb-0 fw-bold">{{ $stats['total_students'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </section>

        @if($latestModels->count() > 0)
            <section class="row">
                <div class="col-12">
                        <div class="card-header bg-transparent border-0 pb-3">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-clock-history me-2"></i>Latest 3D Models
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($latestModels as $model)
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card border-0 shadow-none h-100">
                                            <div class="card-body">
                                                <h6 class="fw-semibold mb-2">{{ $model->name }}</h6>
                                                <p class="text-muted small mb-2">
                                                    {{ Str::limit($model->description ?? 'No description provided.', 60) }}
                                                </p>
                                                <a href="{{ route('client.models.3d.show', $model) }}" class="badge bg-primary text-white btn-sm">
                                                    View Model
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    
                </div>
            </section>
        @endif
          </div>


@push('scripts')
<script>
(function() {
  document.querySelectorAll('.js-package-action').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
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
</x-app-layout>