@extends('errors.layout')

@section('title', config('app.name') . ' | Page Expired')

@section('error_content')
  <h1 class="display-1 fw-bold text-center mb-3" data-aos="fade-up" data-aos-delay="100" data-aos-duration="1000">419</h1>
  <h2 class="mb-0 text-center" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">Page Expired</h2>
  <p class="text-center text-muted mb-4" data-aos="fade-up" data-aos-delay="250" data-aos-duration="1000">Your session has expired. Please refresh the page and try again.</p>
  <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center align-items-center" data-aos="fade-up" data-aos-delay="300" data-aos-duration="1000">
    <button type="button" class="btn" onclick="window.location.reload()">
      <span class="btn-text">Refresh this page</span>
      <iconify-icon icon="lucide:refresh-cw"
        class="btn-icon bg-white text-dark round-52 rounded-circle hstack justify-content-center fs-7 shadow-sm"></iconify-icon>
    </button>
    <a href="{{ url('/') }}" class="btn">
      <span class="btn-text">Back to Home</span>
      <iconify-icon icon="lucide:arrow-up-right"
        class="btn-icon bg-white text-dark round-52 rounded-circle hstack justify-content-center fs-7 shadow-sm"></iconify-icon>
    </a>
  </div>
@endsection
