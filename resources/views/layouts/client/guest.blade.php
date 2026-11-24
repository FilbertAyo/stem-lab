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
  @include('layouts.client.partials.styles')

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


  @include('layouts.client.partials.scripts')
  @stack('scripts')
</body>

</html>
