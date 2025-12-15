<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Adilisha STEM Lab') }}</title>

        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('client/assets/css/bootstrap.css') }}">
        @stack('styles')
        <link rel="stylesheet" href="{{ asset('client/assets/vendors/perfect-scrollbar/perfect-scrollbar.css') }}">
        <link rel="stylesheet" href="{{ asset('client/assets/vendors/bootstrap-icons/bootstrap-icons.css') }}">
        <link rel="stylesheet" href="{{ asset('client/assets/css/app.css') }}">
        <link rel="shortcut icon" href="{{ asset('images/logo/favicon.png') }}" type="image/x-icon">

    </head>
    <body>
        <div id="app">
            @include('layouts.aside')

            <div id="main">
                <header class="mb-3">
                    @include('layouts.navigation')
                </header>

                {{ $slot }}

                <footer class="mt-3">
                    <div class="footer clearfix mb-0 text-muted">
                        <div class="float-start">
                            @php
                                $siteName = \App\Models\Setting::get('site_name', config('app.name', 'Adilisha STEM Labs'));
                            @endphp
                            <p>{{ now()->year }} &copy; {{ $siteName }}</p>
                        </div>
                        <div class="float-end">
                            @php
                                $appVersion = \App\Models\Setting::get('app_version', '1.0.0');
                            @endphp
                            <p>V{{ $appVersion }}</p>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

        <script src="{{ asset('client/assets/vendors/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
        <script src="{{ asset('client/assets/js/bootstrap.bundle.min.js') }}"></script>
        @stack('scripts')
        <script src="{{ asset('client/assets/js/main.js') }}"></script>
    </body>
</html>
