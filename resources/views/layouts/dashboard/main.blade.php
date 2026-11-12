<!DOCTYPE html>
<html lang="en" x-data :class="$store.theme?.dark ? 'dark' : ''">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">


    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    <meta name="theme-color" content="#0f172a">

    <meta name="mobile-web-app-capable" content="yes">

    <meta name="apple-mobile-web-app-capable" content="yes">

    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <meta name="apple-mobile-web-app-title" content="Sangu Gade">

    <link rel="apple-touch-icon" href="{{ asset('icons/launchericon-192x192.png') }}">

    <style>[x-cloak]{display:none !important}</style>

    {{-- Hindari flicker: set "dark" sebelum CSS di-load --}}
    <script>
        (() => {
            try {
                const dark = localStorage.getItem('dark') === 'true';
                if (dark) document.documentElement.classList.add('dark');
            } catch (_) {}
        })();
    </script>

    <title>Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>

<body class="bg-white text-gray-900 dark:bg-gray-900 dark:text-gray-100">

    {{-- Navbar (akan kita isi tombolnya) --}}
    @include('layouts.dashboard.navbar')

    {{-- Sidebar --}}
    @include('layouts.dashboard.sidebar')

    {{-- Konten --}}
    <div class="p-4 sm:ml-64">
        <div class="p-4 border-2 border-gray-200 border-dashed rounded-lg dark:border-gray-700 mt-14">
            {{ $slot }}
        </div>
    </div>

    @include('layouts.dashboard.footer')

    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
    @stack('scripts')


    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('PWA Service Worker aktif:', registration.scope);
                    })
                    .catch(error => {
                        console.error('PWA Service Worker gagal:', error);
                    });
            });
        }
    </script>

</body>
</html>
