<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Vehicle Location')</title>
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal.css') }}">
    @stack('styles')
</head>
<body>
    <x-header />
    
    <main>
        @yield('content')
    </main>

    <!-- Modal de réservation -->
    <x-reservation-modal />

    <script src="{{ asset('js/header.js') }}"></script>
    <script src="{{ asset('js/reservation-modal.js') }}"></script>
    @stack('scripts')
</body>
</html>
