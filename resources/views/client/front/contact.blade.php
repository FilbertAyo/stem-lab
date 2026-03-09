@extends('layouts.landing')

@section('title', config('app.name') . ' | Contact & Support')
@section('page', 'support')

@section('content')
<main>
    <!-- Banner Section -->
    <section class="banner-section banner-inner-section position-relative overflow-hidden d-flex align-items-end"
      style="background-image: url({{ asset('client/assets/images/backgrounds/support.jpg') }});">
      <div class="container">
        <div class="d-flex flex-column gap-4 pb-5 pb-xl-10 position-relative z-1">
          <div class="row align-items-center">
            <div class="col-xl-4">
              <div class="d-flex align-items-center gap-4" data-aos="fade-up" data-aos-delay="100"
                data-aos-duration="1000">
                <p class="mb-0 text-white fs-5 text-opacity-70">Ready to <span class="text-primary">start something</span> great? Reach out we’d love to hear from you.</p>
              </div>
            </div>
          </div>
          <div class="d-flex align-items-end gap-3" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
            <h1 class="mb-0 fs-16 text-white lh-1">Contact & Support</h1>
            <a href="#get-in-touch" class="p-1 ps-7 bg-primary rounded-pill">
              <span class="bg-white round-52 rounded-circle d-flex align-items-center justify-content-center">
                <iconify-icon icon="lucide:arrow-up-right" class="fs-8 text-dark"></iconify-icon>
              </span>
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- Get in touch Section -->
    <section id="get-in-touch" class="get-in-touch py-5 py-lg-11 py-xl-12">
      <div class="container">
        <div class="d-flex flex-column gap-5 gap-xl-10">
          <div class="row gap-7 gap-xl-0">
            <div class="col-xl-4 col-xxl-4">
              <div class="d-flex align-items-center gap-7 py-2" data-aos="fade-right" data-aos-delay="100"
                data-aos-duration="1000">

                <hr class="border-line bg-white">
                <span class="badge text-bg-dark">Contact us</span>
              </div>
            </div>
            <div class="col-xl-8 col-xxl-7">
              <div class="row">
                <div class="col-xxl-8">
                  <div class="d-flex flex-column gap-6" data-aos="fade-up" data-aos-delay="100"
                    data-aos-duration="1000">
                    <h2 class="mb-0">Get in touch</h2>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="row justify-content-between gap-7 gap-xl-0">
            <div class="col-xl-3">
              <p class="mb-0 fs-5" data-aos="fade-right" data-aos-delay="100" data-aos-duration="1000">Let’s collaborate and create something amazing. Tell us about your project or question we’re here to help.</p>
            </div>
            <div class="col-xl-8">
              @if(session('success'))
                <div class="alert alert-success mb-4" role="alert">{{ session('success') }}</div>
              @endif
              @if(session('error'))
                <div class="alert alert-danger mb-4" role="alert">{{ session('error') }}</div>
              @endif
              <form action="{{ route('support.submit') }}" method="POST" class="d-flex flex-column gap-7" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
                @csrf
                <div>
                  <input type="text" name="name" class="form-control border-bottom border-dark @error('name') is-invalid @enderror" id="formGroupExampleInput"
                    placeholder="Name" value="{{ old('name') }}" required>
                  @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div>
                  <input type="email" name="email" class="form-control border-bottom border-dark @error('email') is-invalid @enderror" id="exampleInputEmail1"
                    placeholder="Email" value="{{ old('email') }}" required>
                  @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div>
                  <textarea name="message" class="form-control border-bottom border-dark @error('message') is-invalid @enderror" id="exampleFormControlTextarea1"
                    placeholder="Tell us about your project or how we can help" rows="3" required>{{ old('message') }}</textarea>
                  @error('message')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <button type="submit" class="btn w-100 justify-content-center">
                  <span class="btn-text">Submit message</span>
                  <iconify-icon icon="lucide:arrow-up-right"
                    class="btn-icon bg-white text-dark round-52 rounded-circle hstack justify-content-center fs-7 shadow-sm"></iconify-icon>
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
</main>
@endsection
