<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', ($title ?? 'Download').(isset($titleSuffix) ? ' '.$titleSuffix : ' List'))</title>
    <style>
        @page { margin: 60px 40px 50px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #000000; }
        header { position: fixed; top: -50px; left: 0; right: 0; text-align: center; }
        header img { height: 30px; width: auto; }
        footer { position: fixed; bottom: -35px; left: 0; right: 0; text-align: right; font-size: 10px; color: #000000; }
        footer .pagenum:before { content: counter(page); }
        h1 { margin: 0 0 6px; font-size: 20px; color: #000000; }
        .meta { color: #000000; margin: 0 0 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000000; padding: 6px 8px; text-align: left; vertical-align: top; color: #000000; }
        th { font-weight: bold; }
        .sn { width: 36px; text-align: center; }
        .empty { padding: 16px; text-align: center; color: #000000; }
    </style>
    @stack('styles')
</head>
<body>
    <header>
        <img src="{{ public_path('images/logo/logo-dark.png') }}" alt="Adilisha STEM Labs">
    </header>
    <footer>Page <span class="pagenum"></span></footer>

    <h1>@yield('heading', ($title ?? 'Download').(isset($titleSuffix) ? ' '.$titleSuffix : ' List'))</h1>
    @hasSection('meta')
        @yield('meta')
    @else
        <p class="meta">
            Generated {{ ($generatedAt ?? now())->format('M d, Y h:i A') }}
            @if(! empty($filterSummary))
                &middot; {{ $filterSummary }}
            @endif
        </p>
    @endif

    @yield('content')
</body>
</html>
