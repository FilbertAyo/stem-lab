<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', config('app.name', 'Adilisha STEM Labs'))</title>
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo/favicon.png') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo/favicon.png') }}" media="(prefers-color-scheme: light)">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo/favicon.png') }}" media="(prefers-color-scheme: dark)">
  <link rel="shortcut icon" type="image/png" href="{{ asset('client/front-end/assets/images/logos/favicon.svg') }}" />

  <!-- Include Bootstrap Icons CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <link rel="stylesheet" href="{{ asset('client/front-end/assets/libs/owl.carousel/dist/assets/owl.carousel.min.css') }}">
  <link rel="stylesheet" href="{{ asset('client/front-end/assets/libs/aos-master/dist/aos.css') }}">
  <link rel="stylesheet" href="{{ asset('client/front-end/assets/css/styles.css') }}" />
  <link rel="stylesheet" href="{{ asset('client/front-end/assets/css/dashboard.css') }}" />

  @stack('head')
</head>

<body>

@include('layouts.client.navigation')

  <!-- Page Wrapper -->
  <div class="page-wrapper overflow-hidden" style="padding-top: 70px;">
    <div class="dashboard-main">
      <div class="container">
        <div class="row">
          <!-- Left Sidebar (hidden on profile and dashboard) -->
          @if (!request()->routeIs('profile.*') && !request()->routeIs('dashboard'))
            @include('layouts.client.aside')
          @endif

          {{ $slot }}
          
        </div>
      </div>
    </div>
  </div>

  <footer class="footer  py-5 py-lg-8">
    <div class="container">
      <div class="row">
        <div class="col-md-4 mb-4 mb-md-0">
          @php
            $siteName = \App\Models\Setting::get('site_name', config('app.name', 'Adilisha STEM Labs'));
          @endphp
          <p class="mb-0 text-dark fs-6">&copy; {{ now()->year }} {{ $siteName }}</p>
        </div>
        <div class="col-md-8">
          <ul class="footer-menu list-unstyled mb-0 d-flex flex-wrap gap-3 justify-content-md-end">
            @php
              $appVersion = \App\Models\Setting::get('app_version', '1.0.0');
            @endphp
            <li>
              <span class="link-hover fs-6 text-dark">V{{ $appVersion }}</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </footer>

  <div class="get-template hstack gap-2">
    <button class="btn bg-primary p-2 round-52 rounded-circle hstack justify-content-center flex-shrink-0" id="scrollToTopBtn">
      <iconify-icon icon="lucide:arrow-up" class="fs-7 text-dark"></iconify-icon>
    </button>
  </div>

  <script src="{{ asset('client/front-end/assets/libs/jquery/dist/jquery.min.js') }}"></script>
  <script src="{{ asset('client/front-end/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('client/front-end/assets/libs/owl.carousel/dist/owl.carousel.min.js') }}"></script>
  <script src="{{ asset('client/front-end/assets/libs/aos-master/dist/aos.js') }}"></script>
  <script src="{{ asset('client/front-end/assets/js/custom.js') }}"></script>
  <!-- solar icons -->
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
  <script>
    document.getElementById('navToggle')?.addEventListener('click', function() {
      document.getElementById('dashboardNav')?.classList.toggle('show');
    });
  </script>
  @stack('scripts')
</body>

</html>
