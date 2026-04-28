<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="light">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>
            @yield('title', $companyName ?? config('app.name', 'SaaS Platform'))
        </title>
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- Google Maps (loaded globally via callback so form-field can detect readiness) --}}
        @if(config('services.google_maps.key'))
        <script>
            window._gmapReady = false;
            function _gmapCallback() {
                window._gmapReady = true;
                document.dispatchEvent(new CustomEvent('gmap-ready'));
            }
        </script>
        <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&callback=_gmapCallback" async defer></script>
        @endif

        @stack('styles')
    </head>
    <body class="bg-body-tertiary">
        <div class="d-flex vh-100 w-100" style="overflow: hidden;">
            {{-- Sidebar Navigation --}}
            <x-sidebar />
            
            {{-- Main Content Area --}}
            <div class="d-flex flex-column flex-grow-1" style="overflow: hidden;">
                {{-- Header/Topbar --}}
                <x-topbar :breadcrumbs="$breadcrumbs ?? [['name' => __('messages.dashboard'), 'url' => '/']]" />

                {{-- Page Content --}}
                <main class="flex-grow-1 p-4 p-md-5 d-flex flex-column" style="overflow: hidden; padding: 1.5rem !important;">
                    @yield('content')
                </main>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>