@php
    $carousel = $carousel ?? false;
    $animations = $animations ?? false;
    $dashboard = $dashboard ?? false;
    $ui = $ui ?? true;
@endphp
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap">
<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<link rel="preconnect" href="https://api.iconify.design" crossorigin>
@if($dashboard)
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endif
@if($carousel)
<link rel="stylesheet" href="{{ asset('client/assets/libs/owl.carousel/dist/assets/owl.carousel.min.css') }}">
@endif
@if($animations)
<link rel="stylesheet" href="{{ asset('client/assets/libs/aos-master/dist/aos.css') }}">
@endif
<link rel="stylesheet" href="{{ asset('client/assets/css/styles.css') }}" />
@if($dashboard)
<link rel="stylesheet" href="{{ asset('client/assets/css/dashboard.css') }}" />
@endif
@if($ui)
<link rel="stylesheet" href="{{ asset('client/assets/css/client-layout-width.css') }}" />
<link rel="stylesheet" href="{{ asset('client/assets/css/client-button-sizing.css') }}" />
<link rel="stylesheet" href="{{ asset('client/assets/css/client-form-controls.css') }}" />
<link rel="stylesheet" href="{{ asset('client/assets/css/client-modal-sizing.css') }}" />
@endif
