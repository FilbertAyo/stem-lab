<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', config('app.name', 'Adilisha STEM Labs'))</title>
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo/favicon.png') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo/favicon.png') }}" media="(prefers-color-scheme: light)">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo/favicon.png') }}" media="(prefers-color-scheme: dark)">
  <link rel="shortcut icon" type="image/png" href="{{ asset('client/assets/images/logos/favicon.svg') }}" />

  <!-- Include Bootstrap Icons CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <link rel="stylesheet" href="{{ asset('client/assets/libs/owl.carousel/dist/assets/owl.carousel.min.css') }}">
  <link rel="stylesheet" href="{{ asset('client/assets/libs/aos-master/dist/aos.css') }}">
  <link rel="stylesheet" href="{{ asset('client/assets/css/styles.css') }}" />
  <link rel="stylesheet" href="{{ asset('client/assets/css/dashboard.css') }}" />
  <link rel="stylesheet" href="{{ asset('client/assets/css/client-layout-width.css') }}" />
  <link rel="stylesheet" href="{{ asset('client/assets/css/client-button-sizing.css') }}" />
  <link rel="stylesheet" href="{{ asset('client/assets/css/client-form-controls.css') }}" />
  <link rel="stylesheet" href="{{ asset('client/assets/css/client-modal-sizing.css') }}" />

  @stack('head')
  @stack('styles')
</head>

<body>

@include('layouts.client.navigation')

  <!-- Page Wrapper -->
  <div class="page-wrapper overflow-hidden bg-light" style="padding-top: 70px;">
    <div class="dashboard-main">
      <div class="container">
        <div class="row">
          <!-- Left Sidebar (hidden on profile, dashboard, VR experiments, 3D models, projects — full width) -->
          @if (!request()->routeIs('profile.*')
               && !request()->routeIs('dashboard')
               && !request()->routeIs('client.billing.*')
               && !request()->routeIs('client.vr-experiments.*')
               && !request()->routeIs('client.models.3d.*')
               && !request()->routeIs('client.projects.*'))
            @include('layouts.client.aside')
          @endif

          {{ $slot }}
          
        </div>
      </div>
    </div>
  </div>

  @include('client.front.partials.footer')

  <div class="get-template hstack gap-2">
    <button class="btn bg-primary p-2 round-52 rounded-circle hstack justify-content-center flex-shrink-0" id="scrollToTopBtn">
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
  <div class="dashboard-nav-backdrop" id="dashboardNavBackdrop" aria-hidden="true"></div>
  <script>
    (function () {
      var nav = document.getElementById('dashboardNav');
      var toggle = document.getElementById('navToggle');
      var backdrop = document.getElementById('dashboardNavBackdrop');
      if (!nav || !toggle) return;

      function setNavOpen(open) {
        nav.classList.toggle('show', open);
        backdrop?.classList.toggle('show', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (backdrop) backdrop.setAttribute('aria-hidden', open ? 'false' : 'true');
      }

      function isNavOpen() {
        return nav.classList.contains('show');
      }

      toggle.setAttribute('aria-expanded', 'false');
      toggle.setAttribute('aria-controls', 'dashboardNav');

      toggle.addEventListener('click', function (e) {
        e.stopPropagation();
        setNavOpen(!isNavOpen());
      });

      backdrop?.addEventListener('click', function () {
        setNavOpen(false);
      });

      nav.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
          setNavOpen(false);
        });
      });

      document.addEventListener('click', function (e) {
        if (!isNavOpen()) return;
        if (nav.contains(e.target) || toggle.contains(e.target)) return;
        setNavOpen(false);
      });

      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && isNavOpen()) {
          setNavOpen(false);
        }
      });
    })();
  </script>
  @stack('scripts')
</body>

</html>
