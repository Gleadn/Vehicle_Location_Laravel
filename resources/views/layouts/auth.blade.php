<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Connexion - Vehicle Location')</title>
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    @stack('styles')
</head>
<body class="login-page">
    <main>
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
