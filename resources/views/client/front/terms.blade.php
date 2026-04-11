@extends('layouts.landing')

@section('title', config('app.name') . ' | Terms & Conditions')
@section('page', 'terms-conditions')

@push('styles')
<style>
  .legal-content { font-size: 1.1rem; color: #212529; }
  .legal-content p, .legal-content li { font-weight: 500; margin-bottom: 1rem; }
  .legal-content ul, .legal-content ol { margin-bottom: 1.25rem; padding-left: 1.25rem; }
  .legal-content h1, .legal-content h2, .legal-content h3, .legal-content h4, .legal-content h5 { margin-top: 1.5rem; margin-bottom: 1rem; font-weight: 600; }
  .legal-content a { color: var(--bs-primary, #0d6efd); }
</style>
@endpush

@section('content')
<main>
    <section class="banner-section banner-inner-section position-relative overflow-hidden d-flex align-items-end"
      style="background-image: url({{ asset('client/assets/images/backgrounds/privacy-policy-banner.jpg') }});">
      <div class="container">
        <div class="d-flex flex-column gap-4 pb-5 pb-xl-10 position-relative z-1">
          <div class="row align-items-center">
            <div class="col-xl-4">
              <div class="d-flex align-items-center gap-4" data-aos="fade-up" data-aos-delay="100"
                data-aos-duration="1000">
                <img src="{{ asset('client/assets/images/svgs/primary-leaf.svg') }}" alt="" class="img-fluid animate-spin">
                <p class="mb-0 text-white fs-5 text-opacity-70">Understand the <span class="text-primary">Rules &
                    Guidelines</span> Before Using Our Services</p>
              </div>
            </div>
          </div>
          <div class="d-flex align-items-end gap-3" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
            <h1 class="mb-0 fs-16 text-white lh-1">Terms & Conditions</h1>
            <a href="javascript:void(0)" class="p-1 ps-7 bg-primary rounded-pill">
              <span class="bg-white round-52 rounded-circle d-flex align-items-center justify-content-center">
                <iconify-icon icon="lucide:arrow-up-right" class="fs-8 text-dark"></iconify-icon>
              </span>
            </a>
          </div>
        </div>
      </div>
    </section>

    <section class="terms-conditions py-5 py-lg-11">
      <div class="container">
        <div class="legal-content">
          @php
            $storedTerms = trim((string) \App\Models\Setting::get('terms_conditions_html', ''));
          @endphp
          @if ($storedTerms !== '')
            {!! \Stevebauman\Purify\Facades\Purify::config('legal')->clean($storedTerms) !!}
          @else
            @include('client.front.partials.terms-default')
          @endif
        </div>
      </div>
    </section>
</main>
@endsection
