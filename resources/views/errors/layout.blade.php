<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') - Sangu Gade</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-50 px-6 py-12 dark:bg-gray-900">
    <main class="w-full max-w-xl rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-lg sm:p-12 dark:border-gray-800 dark:bg-gray-800">
        {{-- Icon Container --}}
        <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full @yield('icon-bg', 'bg-red-100 text-red-600 dark:bg-red-950/40 dark:text-red-400')">
            @yield('icon')
        </div>

        {{-- Error Code Badge / Subtitle --}}
        <p class="mb-2 text-xs font-bold uppercase tracking-widest @yield('badge-color', 'text-red-600 dark:text-red-400')">
            Error @yield('code') &bull; @yield('status')
        </p>

        {{-- Title --}}
        <h1 class="mb-3 text-2xl font-extrabold tracking-tight text-gray-900 sm:text-3xl dark:text-white">
            @yield('heading')
        </h1>

        {{-- Message --}}
        <p class="mb-8 text-sm leading-relaxed text-gray-600 sm:text-base dark:text-gray-300">
            @yield('message')
        </p>

        {{-- Action Buttons --}}
        <div class="flex flex-col justify-center gap-3 sm:flex-row">
            @yield('actions')
            <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-800">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955a1.125 1.125 0 0 1 1.592 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125h4.125v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
                </svg>
                Kembali ke Beranda
            </a>
            <button type="button" onclick="window.history.back()" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 dark:focus:ring-gray-700">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Halaman Sebelumnya
            </button>
        </div>

        <p class="mt-8 text-xs text-gray-400 dark:text-gray-500">
            Sangu Gade &bull; Butuh bantuan? Silakan hubungi administrator sistem.
        </p>
    </main>
</body>
</html>

