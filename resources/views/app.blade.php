<!DOCTYPE html>
<html lang="pt-PT" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script>window.__LOCALE__ = 'pt_PT';</script>
    @viteReactRefresh
    @vite(['resources/js/app.jsx', 'resources/css/app.css'])
</head>
<body class="font-sans antialiased">
    @inertia
</body>
</html>
