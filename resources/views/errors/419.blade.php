@extends('errors.layout')

@section('title', '419 Page Expired - Sesi Kedaluwarsa')
@section('code', '419')
@section('status', 'Page Expired')
@section('badge-color', 'text-orange-600 dark:text-orange-400')
@section('icon-bg', 'bg-orange-100 text-orange-600 dark:bg-orange-950/40 dark:text-orange-400')

@section('icon')
<svg class="h-10 w-10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
</svg>
@endsection

@section('heading', 'Sesi Telah Kedaluwarsa')

@section('message')
    Sesi Anda telah berakhir atau halaman ini sudah terlalu lama tidak aktif karena alasan keamanan (CSRF Token Expired). Silakan muat ulang halaman atau masuk kembali.
@endsection

@section('actions')
    <button type="button" onclick="window.location.reload()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-orange-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-700 focus:outline-none focus:ring-4 focus:ring-orange-300 dark:focus:ring-orange-800">
        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
        </svg>
        Muat Ulang Halaman
    </button>
@endsection

