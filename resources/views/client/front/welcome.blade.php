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
                    <p class="fs-5 mb-0 text-opacity-70">Explore how students across Africa are experiencing science education through Adilisha XR, transforming classrooms into virtual laboratories where learning comes alive.</p>
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
                  <img src="{{ asset('client/assets/images/resources/resources-1.jpg') }}" alt="resources" class="img-fluid">
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
                  <img src="{{ asset('client/assets/images/resources/resources-2.jpg') }}" alt="resources" class="img-fluid">
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
                  <img src="{{ asset('client/assets/images/resources/resources-3.jpg') }}" alt="resources" class="img-fluid">
                </a>
                <div class="resources-details">
                  <p class="mb-0">Dec 28, 2025</p>
                  <h4 class="mb-0">Adilisha XR design journey: from concept to prototype</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section> -->

    
  
  </main>
@endsection
