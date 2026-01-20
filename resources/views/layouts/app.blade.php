<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Vehicle Location')</title>
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    @stack('styles')
</head>
<body>
    <x-header />
    
    <main>
        @yield('content')
    </main>

    <script src="{{ asset('js/header.js') }}"></script>
    @stack('scripts')
</body>
</html>
