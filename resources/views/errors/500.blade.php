@extends('errors.layout')

@section('title', config('app.name') . ' | Server Error')

@section('error_content')
  <h1 class="display-1 fw-bold text-center mb-3" data-aos="fade-up" data-aos-delay="100" data-aos-duration="1000">500</h1>
  <h2 class="mb-0 text-center" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">Server Error</h2>
  <p class="text-center text-muted mb-4" data-aos="fade-up" data-aos-delay="250" data-aos-duration="1000">Something went wrong on our end. Please try again later.</p>
  <a href="{{ url('/') }}" class="btn mx-auto" data-aos="fade-up" data-aos-delay="300" data-aos-duration="1000">
    <span class="btn-text">Back to Home</span>
    <iconify-icon icon="lucide:arrow-up-right"
      class="btn-icon bg-white text-dark round-52 rounded-circle hstack justify-content-center fs-7 shadow-sm"></iconify-icon>
  </a>
@endsection
