<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Adiisha STEM Lab </title>

    <link rel="icon" href="{{ asset('images/logo/favicon.png') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap"
        id="main-font-link">
    <link rel="stylesheet" href="{{ asset('admin/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/style.min.css') }}" id="main-style-link">
    <link rel="stylesheet" href="{{ asset('admin/css/style-preset.min.css') }}">

</head>

<body>

    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>

    <div class="auth-main">
        <div class="auth-wrapper v3">
            <div class="auth-form">

                {{ $slot }}

                <div class="auth-footer row">
                    <div class="col my-1">
                        <p class="m-0">Copyright © <a href="{{ config('app.url', 'https://adilishastemlabs.com') }}">Adilisha STEM Labs</a>
                        </p>
                    </div>
                    <div class="col-auto my-1">
                        <ul class="list-inline footer-link mb-0">
                            <li class="list-inline-item"><a href="{{ url('https://adilisha.or.tz') }}">Adilisha Child and Youth Development Organization</a></li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="{{ asset('admin/js/plugins/popper.min.js') }}"></script>
    <script src="{{ asset('admin/js/plugins/simplebar.min.js') }}"></script>
    <script src="{{ asset('admin/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('admin/js/fonts/custom-font.js') }}"></script>
    <script src="{{ asset('admin/js/pcoded.js') }}"></script>
    <script src="{{ asset('admin/js/plugins/feather.min.js') }}"></script>

    <script>
        layout_change('light');
    </script>

    <script>
        change_box_container('false');
    </script>

    <script>
        layout_rtl_change('false');
    </script>

    <script>
        preset_change("preset-1");
    </script>

    <script>
        font_change("Public-Sans");
    </script>

</body>

</html>
