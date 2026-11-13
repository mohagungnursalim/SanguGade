@extends('errors.layout')

@section('title', '503 Service Unavailable - Pemeliharaan Sistem')
@section('code', '503')
@section('status', 'Service Unavailable')
@section('badge-color', 'text-amber-600 dark:text-amber-400')
@section('icon-bg', 'bg-amber-100 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400')

@section('icon')
<svg class="h-10 w-10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.07a4.5 4.5 0 0 0 4.486-6.32l-3.27 3.27-2.956-.59-1.02-2.957 3.27-3.27a4.5 4.5 0 0 0-6.334 4.482c.115.58.087 1.19-.077 1.74" />
</svg>
@endsection

@section('heading', 'Sistem Sedang Dalam Pemeliharaan')

@section('message')
    {{ $exception->getMessage() ?: 'Aplikasi Sangu Gade saat ini sedang dalam peningkatan performa atau pemeliharaan rutin. Kami akan segera kembali beroperasi normal.' }}
@endsection

@section('actions')
    <button type="button" onclick="window.location.reload()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-4 focus:ring-amber-300 dark:focus:ring-amber-800">
        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
        </svg>
        Cek Status Kembali
    </button>
@endsection

