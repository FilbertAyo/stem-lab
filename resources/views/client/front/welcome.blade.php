@extends('layouts.landing')

@section('title', config('app.name') . ' | Home')
@section('page', 'home')

@section('content')
<main>

    @include('client.front.partials.hero')
    @include('client.front.partials.reach')
    @include('client.front.partials.concept')
    @include('client.front.partials.labs')
    @include('client.front.partials.xr')
    @include('client.front.partials.outcomes')
    @include('client.front.partials.journey')
   

    <!--  Labs Ecosystem Section -->
    <!-- <section class="pricing-section py-5 py-lg-11 py-xl-12 bg-light-gray">
      <div class="container">
        <div class="d-flex flex-column gap-5 gap-xl-10">
          <div class="d-flex flex-column gap-5 gap-xl-11">
            <div class="row gap-7 gap-xl-0">
              <div class="col-xl-4 col-xxl-4">
                <div class="d-flex align-items-center gap-7 py-2" data-aos="fade-right" data-aos-delay="100"
                  data-aos-duration="1000">
                  <span
                    class="round-36 flex-shrink-0 text-dark rounded-circle bg-primary hstack justify-content-center fw-medium">07</span>
                  <hr class="border-line bg-white">
                  <span class="badge text-bg-dark">Labs Ecosystem</span>
                </div>
              </div>
              <div class="col-xl-8 col-xxl-7">
                <div class="row">
                  <div class="col-xxl-8">
                    <div class="d-flex flex-column gap-6" data-aos="fade-up" data-aos-delay="100"
                      data-aos-duration="1000">
                      <h2 class="mb-0">Comprehensive virtual laboratory platform</h2>
                      <p class="fs-5 mb-0 text-opacity-70">Adilisha STEM Labs provides a complete ecosystem of immersive science experiences, from chemistry and physics to biology and engineering, all accessible through Adilisha XR Pro.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-6 col-xl-4 mb-7 mb-xl-0 d-flex align-items-stretch">
                <div class="card w-100" data-aos="fade-up" data-aos-delay="100" data-aos-duration="1000">
                  <div class="card-body p-7 p-xxl-5 d-flex flex-column gap-8">
                    <div class="d-flex flex-column gap-6">
                      <h5 class="mb-0 fw-medium">Launch</h5>
                      <div class="hstack gap-2">
                        <h3 class="mb-0">$699</h3>
                        <p class="mb-0">/month</p>
                      </div>
                      <p class="mb-0">Ideal for startups and small businesses taking their first steps online.</p>
                    </div>
                    <div class="pt-8 border-top d-flex flex-column gap-6">
                      <h6 class="mb-0 fw-normal">What’s Included:</h6>
                      <ul class="list-unstyled d-flex flex-column gap-3 mb-0">
                        <li class="hstack gap-3">
                          <span
                            class="round-32 rounded-circle bg-primary flex-shrink-0 hstack justify-content-center"><iconify-icon
                              icon="lucide:check" class="fs-6 text-dark"></iconify-icon></span>
                          <h6 class="mb-0 fw-normal">Competitive research & insights</h6>
                        </li>
                        <li class="hstack gap-3">
                          <span
                            class="round-32 rounded-circle bg-primary flex-shrink-0 hstack justify-content-center"><iconify-icon
                              icon="lucide:check" class="fs-6 text-dark"></iconify-icon></span>
                          <h6 class="mb-0 fw-normal">Wireframing and prototyping</h6>
                        </li>
                        <li class="hstack gap-3">
                          <span
                            class="round-32 rounded-circle bg-primary flex-shrink-0 hstack justify-content-center"><iconify-icon
                              icon="lucide:check" class="fs-6 text-dark"></iconify-icon></span>
                          <h6 class="mb-0 fw-normal">Basic tracking setup (Google Analytics, etc.)</h6>
                        </li>
                        <li class="hstack gap-3">
                          <span
                            class="round-32 rounded-circle bg-primary flex-shrink-0 hstack justify-content-center"><iconify-icon
                              icon="lucide:check" class="fs-6 text-dark"></iconify-icon></span>
                          <h6 class="mb-0 fw-normal">Standard contact form integration</h6>
                        </li>
                      </ul>
                    </div>
                    <a href="" class="btn w-100 justify-content-center">
                      <span class="btn-text">Subscribe now</span>
                      <iconify-icon icon="lucide:arrow-up-right"
                        class="btn-icon bg-white text-dark round-52 rounded-circle hstack justify-content-center fs-7 shadow-sm"></iconify-icon>
                    </a>
                  </div>
                </div>
              </div>
              <div class="col-lg-6 col-xl-4 mb-7 mb-xl-0 d-flex align-items-stretch">
                <div class="card w-100" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
                  <div class="card-body p-7 p-xxl-5 d-flex flex-column gap-8">
                    <div class="d-flex flex-column gap-6">
                      <div class="hstack gap-3">
                        <h5 class="mb-0 fw-medium">Scale</h5>
                        <span class="badge text-bg-dark hstack gap-2"><iconify-icon icon="lucide:flame"
                            class="fs-5"></iconify-icon>Most popular</span>
                      </div>
                      <div class="hstack gap-2">
                        <h3 class="mb-0 text-opacity-50 text-dark"><del>$2,199</del></h3>
                        <h3 class="mb-0">$1,699</h3>
                        <p class="mb-0">/month</p>
                      </div>
                      <p class="mb-0">Perfect for growing brands needing more customization and flexibility.</p>
                    </div>
                    <div class="pt-8 border-top d-flex flex-column gap-6">
                      <h6 class="mb-0 fw-normal">What’s Included:</h6>
                      <ul class="list-unstyled d-flex flex-column gap-3 mb-0">
                        <li class="hstack gap-3">
                          <span
                            class="round-32 rounded-circle bg-primary flex-shrink-0 hstack justify-content-center"><iconify-icon
                              icon="lucide:check" class="fs-6 text-dark"></iconify-icon></span>
                          <h6 class="mb-0 fw-normal">Everything in the Launch Plan</h6>
                        </li>
                        <li class="hstack gap-3">
                          <span
                            class="round-32 rounded-circle bg-primary flex-shrink-0 hstack justify-content-center"><iconify-icon
                              icon="lucide:check" class="fs-6 text-dark"></iconify-icon></span>
                          <h6 class="mb-0 fw-normal">Custom design for up to 10 pages</h6>
                        </li>
                        <li class="hstack gap-3">
                          <span
                            class="round-32 rounded-circle bg-primary flex-shrink-0 hstack justify-content-center"><iconify-icon
                              icon="lucide:check" class="fs-6 text-dark"></iconify-icon></span>
                          <h6 class="mb-0 fw-normal">Seamless social media integration</h6>
                        </li>
                        <li class="hstack gap-3">
                          <span
                            class="round-32 rounded-circle bg-primary flex-shrink-0 hstack justify-content-center"><iconify-icon
                              icon="lucide:check" class="fs-6 text-dark"></iconify-icon></span>
                          <h6 class="mb-0 fw-normal">SEO enhancements for key pages</h6>
                        </li>
                      </ul>
                    </div>
                    <a href="" class="btn w-100 justify-content-center">
                      <span class="btn-text">Subscribe now</span>
                      <iconify-icon icon="lucide:arrow-up-right"
                        class="btn-icon bg-white text-dark round-52 rounded-circle hstack justify-content-center fs-7 shadow-sm"></iconify-icon>
                    </a>
                  </div>
                </div>
              </div>
              <div class="col-lg-6 col-xl-4 mb-7 mb-xl-0 d-flex align-items-stretch">
                <div class="card w-100" data-aos="fade-up" data-aos-delay="300" data-aos-duration="1000">
                  <div class="card-body p-7 p-xxl-5 d-flex flex-column gap-8">
                    <div class="d-flex flex-column gap-6">
                      <h5 class="mb-0 fw-medium">Elevate</h5>
                      <div class="hstack gap-2">
                        <h3 class="mb-0">$3,499</h3>
                        <p class="mb-0">/month</p>
                      </div>
                      <p class="mb-0">Best suited for established businesses wanting a fully tailored experience.</p>
                    </div>
                    <div class="pt-8 border-top d-flex flex-column gap-6">
                      <h6 class="mb-0 fw-normal">What’s Included:</h6>
                      <ul class="list-unstyled d-flex flex-column gap-3 mb-0">
                        <li class="hstack gap-3">
                          <span
                            class="round-32 rounded-circle bg-primary flex-shrink-0 hstack justify-content-center"><iconify-icon
                              icon="lucide:check" class="fs-6 text-dark"></iconify-icon></span>
                          <h6 class="mb-0 fw-normal">Everything in the Scale Plan</h6>
                        </li>
                        <li class="hstack gap-3">
                          <span
                            class="round-32 rounded-circle bg-primary flex-shrink-0 hstack justify-content-center"><iconify-icon
                              icon="lucide:check" class="fs-6 text-dark"></iconify-icon></span>
                          <h6 class="mb-0 fw-normal">E-commerce functionality (if needed)</h6>
                        </li>
                        <li class="hstack gap-3">
                          <span
                            class="round-32 rounded-circle bg-primary flex-shrink-0 hstack justify-content-center"><iconify-icon
                              icon="lucide:check" class="fs-6 text-dark"></iconify-icon></span>
                          <h6 class="mb-0 fw-normal">Branded email template design</h6>
                        </li>
                        <li class="hstack gap-3">
                          <span
                            class="round-32 rounded-circle bg-primary flex-shrink-0 hstack justify-content-center"><iconify-icon
                              icon="lucide:check" class="fs-6 text-dark"></iconify-icon></span>
                          <h6 class="mb-0 fw-normal">Priority support for six months after launch</h6>
                        </li>
                      </ul>
                    </div>
                    <a href="" class="btn w-100 justify-content-center">
                      <span class="btn-text">Subscribe now</span>
                      <iconify-icon icon="lucide:arrow-up-right"
                        class="btn-icon bg-white text-dark round-52 rounded-circle hstack justify-content-center fs-7 shadow-sm"></iconify-icon>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="d-flex flex-column gap-8" data-aos="fade-up" data-aos-delay="100" data-aos-duration="1000">
            <p class="fs-5 mb-0 text-center text-dark">Trusted by educational institutions across Africa</p>
            <div class="marquee w-100 d-flex align-items-center overflow-hidden">
              <div class="marquee-content d-flex align-items-center gap-8">
                <div class="marquee-tag hstack justify-content-center">
                  <img src="{{ asset('client/front-end/assets/images/pricing/partners-1.svg') }}" alt="partners" class="img-fluid">
                </div>
                <div class="marquee-tag hstack justify-content-center">
                  <img src="{{ asset('client/front-end/assets/images/pricing/partners-2.svg') }}" alt="partners" class="img-fluid">
                </div>
                <div class="marquee-tag hstack justify-content-center">
                  <img src="{{ asset('client/front-end/assets/images/pricing/partners-3.svg') }}" alt="partners" class="img-fluid">
                </div>
                <div class="marquee-tag hstack justify-content-center">
                  <img src="{{ asset('client/front-end/assets/images/pricing/partners-4.svg') }}" alt="partners" class="img-fluid">
                </div>
                <div class="marquee-tag hstack justify-content-center">
                  <img src="{{ asset('client/front-end/assets/images/pricing/partners-5.svg') }}" alt="partners" class="img-fluid">
                </div>
                <div class="marquee-tag hstack justify-content-center">
                  <img src="{{ asset('client/front-end/assets/images/pricing/partners-1.svg') }}" alt="partners" class="img-fluid">
                </div>
                <div class="marquee-tag hstack justify-content-center">
                  <img src="{{ asset('client/front-end/assets/images/pricing/partners-2.svg') }}" alt="partners" class="img-fluid">
                </div>
                <div class="marquee-tag hstack justify-content-center">
                  <img src="{{ asset('client/front-end/assets/images/pricing/partners-3.svg') }}" alt="partners" class="img-fluid">
                </div>
                <div class="marquee-tag hstack justify-content-center">
                  <img src="{{ asset('client/front-end/assets/images/pricing/partners-4.svg') }}" alt="partners" class="img-fluid">
                </div>
                <div class="marquee-tag hstack justify-content-center">
                  <img src="{{ asset('client/front-end/assets/images/pricing/partners-5.svg') }}" alt="partners" class="img-fluid">
                </div>
                <div class="marquee-tag hstack justify-content-center">
                  <img src="{{ asset('client/front-end/assets/images/pricing/partners-1.svg') }}" alt="partners" class="img-fluid">
                </div>
                <div class="marquee-tag hstack justify-content-center">
                  <img src="{{ asset('client/front-end/assets/images/pricing/partners-2.svg') }}" alt="partners" class="img-fluid">
                </div>
                <div class="marquee-tag hstack justify-content-center">
                  <img src="{{ asset('client/front-end/assets/images/pricing/partners-3.svg') }}" alt="partners" class="img-fluid">
                </div>
                <div class="marquee-tag hstack justify-content-center">
                  <img src="{{ asset('client/front-end/assets/images/pricing/partners-4.svg') }}" alt="partners" class="img-fluid">
                </div>
                <div class="marquee-tag hstack justify-content-center">
                  <img src="{{ asset('client/front-end/assets/images/pricing/partners-5.svg') }}" alt="partners" class="img-fluid">
                </div>
                <div class="marquee-tag hstack justify-content-center">
                  <img src="{{ asset('client/front-end/assets/images/pricing/partners-1.svg') }}" alt="partners" class="img-fluid">
                </div>
                <div class="marquee-tag hstack justify-content-center">
                  <img src="{{ asset('client/front-end/assets/images/pricing/partners-2.svg') }}" alt="partners" class="img-fluid">
                </div>
                <div class="marquee-tag hstack justify-content-center">
                  <img src="{{ asset('client/front-end/assets/images/pricing/partners-3.svg') }}" alt="partners" class="img-fluid">
                </div>
                <div class="marquee-tag hstack justify-content-center">
                  <img src="{{ asset('client/front-end/assets/images/pricing/partners-4.svg') }}" alt="partners" class="img-fluid">
                </div>
                <div class="marquee-tag hstack justify-content-center">
                  <img src="{{ asset('client/front-end/assets/images/pricing/partners-5.svg') }}" alt="partners" class="img-fluid">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section> -->

    @include('client.front.partials.insights')
    
    <!-- <section class="Recent-news bg-light-gray py-5 py-lg-11 py-xl-12">
      <div class="container">
        <div class="d-flex flex-column gap-5 gap-xl-11">
          <div class="row gap-7 gap-xl-0">
            <div class="col-xl-4 col-xxl-4">
              <div class="d-flex align-items-center gap-7 py-2" data-aos="fade-right" data-aos-delay="100"
                data-aos-duration="1000">

                <hr class="border-line bg-white">
                <span class="badge text-bg-dark">Lab Experiences</span>
              </div>
            </div>
            <div class="col-xl-8 col-xxl-7">
              <div class="row">
                <div class="col-xxl-8">
                  <div class="d-flex flex-column gap-6" data-aos="fade-up" data-aos-delay="100"
                    data-aos-duration="1000">
                    <h2 class="mb-0">Discover immersive learning</h2>
                    <p class="fs-5 mb-0 text-opacity-70">Explore how students across Africa are experiencing science education through Adilisha XR Pro, transforming classrooms into virtual laboratories where learning comes alive.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-xl-6 mb-7 mb-xl-0">
              <div class="resources d-flex flex-column gap-6" data-aos="fade-up" data-aos-delay="100"
                data-aos-duration="1000">
                <a href="blog-detail.html"
                  class="resources-img resources-img-first position-relative overflow-hidden d-block">
                  <img src="{{ asset('client/front-end/assets/images/resources/resources-1.jpg') }}" alt="resources" class="img-fluid">
                </a>
                <div class="resources-details">
                  <p class="mb-0">Jan 15, 2026</p>
                  <h4 class="mb-0">Chemistry experiments come alive in rural Kenya</h4>
                </div>
              </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-7 mb-xl-0">
              <div class="resources d-flex flex-column gap-6" data-aos="fade-up" data-aos-delay="200"
                data-aos-duration="1000">
                <a href="javascript:void(0)" class="resources-img position-relative overflow-hidden d-block">
                  <img src="{{ asset('client/front-end/assets/images/resources/resources-2.jpg') }}" alt="resources" class="img-fluid">
                </a>
                <div class="resources-details">
                  <p class="mb-0">Jan 8, 2026</p>
                  <h4 class="mb-0">Students explore molecular structures in immersive biology labs</h4>
                </div>
              </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-7 mb-xl-0">
              <div class="resources d-flex flex-column gap-6" data-aos="fade-up" data-aos-delay="300"
                data-aos-duration="1000">
                <a href="javascript:void(0)" class="resources-img position-relative overflow-hidden d-block">
                  <img src="{{ asset('client/front-end/assets/images/resources/resources-3.jpg') }}" alt="resources" class="img-fluid">
                </a>
                <div class="resources-details">
                  <p class="mb-0">Dec 28, 2025</p>
                  <h4 class="mb-0">Adilisha XR Pro design journey: from concept to prototype</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section> -->

    
  
  </main>
@endsection
