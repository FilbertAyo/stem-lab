@php
    $page = trim($__env->yieldContent('page', 'home'));
    $bodyClass = trim($__env->yieldContent('body_class'));
@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', config('app.name'))</title>
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo/favicon.png') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo/favicon.png') }}" media="(prefers-color-scheme: light)">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo/favicon.png') }}" media="(prefers-color-scheme: dark)">
  <link rel="shortcut icon" type="image/png" href="{{ asset('client/assets/images/logos/favicon.svg') }}" />
  <link rel="stylesheet" href="{{ asset('client/assets/libs/owl.carousel/dist/assets/owl.carousel.min.css') }}">
  <link rel="stylesheet" href="{{ asset('client/assets/libs/aos-master/dist/aos.css') }}">
  <link rel="stylesheet" href="{{ asset('client/assets/css/styles.css') }}" />
  <link rel="stylesheet" href="{{ asset('client/assets/css/client-layout-width.css') }}" />
  <link rel="stylesheet" href="{{ asset('client/assets/css/client-button-sizing.css') }}" />
  <link rel="stylesheet" href="{{ asset('client/assets/css/client-form-controls.css') }}" />
  <link rel="stylesheet" href="{{ asset('client/assets/css/client-modal-sizing.css') }}" />

  @stack('head')
</head>

<body>

  <!--  Page Wrapper -->
  <div class="page-wrapper overflow-hidden bg-light">

  @yield('content')

  </div>

  <div class="get-template hstack gap-2">
    <button class="btn bg-primary p-2 round-52 rounded-circle hstack justify-content-center flex-shrink-0"
      id="scrollToTopBtn">
      <iconify-icon icon="lucide:arrow-up" class="fs-7 text-dark"></iconify-icon>
    </button>
  </div>


    <script src="{{ asset('client/assets/libs/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('client/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('client/assets/libs/owl.carousel/dist/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('client/assets/libs/aos-master/dist/aos.js') }}"></script>
    <script src="{{ asset('client/assets/js/custom.js') }}"></script>
  <!-- solar icons -->
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
  @stack('scripts')
</body>

</html>
