<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', config('app.name'))</title>
  <link rel="shortcut icon" type="image/png" href="{{ asset('client/assets/images/logos/favicon.svg') }}" />
  @include('layouts.client.partials.styles', ['animations' => true, 'ui' => false])
</head>

<body>

  <!--  Page Wrapper -->
  <div class="page-wrapper overflow-hidden">

    <!--  Error Section -->
    <section
      class="bg-light-gray d-flex align-items-center justify-content-center min-vh-100">
      <div class="container py-3">
        <div class="d-flex flex-column justify-content-center gap-8">
          @yield('error_content')
        </div>
      </div>
    </section>

  </div>

  <div class="get-template hstack gap-2">
    
    <button class="btn bg-primary p-2 round-52 rounded-circle hstack justify-content-center flex-shrink-0"
      id="scrollToTopBtn">
      <iconify-icon icon="lucide:arrow-up" class="fs-7 text-dark"></iconify-icon>
    </button>
  </div>


  @include('layouts.client.partials.scripts', ['animations' => true])
</body>

</html>
