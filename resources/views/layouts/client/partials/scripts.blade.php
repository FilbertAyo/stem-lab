@php
    $jquery = $jquery ?? false;
    $bootstrap = $bootstrap ?? false;
    $carousel = $carousel ?? false;
    $animations = $animations ?? false;
@endphp
@if($jquery)
<script src="{{ asset('client/assets/libs/jquery/dist/jquery.min.js') }}"></script>
@endif
@if($bootstrap)
<script src="{{ asset('client/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
@endif
@if($carousel)
<script src="{{ asset('client/assets/libs/owl.carousel/dist/owl.carousel.min.js') }}"></script>
@endif
@if($animations)
<script src="{{ asset('client/assets/libs/aos-master/dist/aos.js') }}"></script>
@endif
<script src="{{ asset('client/assets/js/custom.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
