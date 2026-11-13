@extends('errors.layout')

@section('title', '429 Too Many Requests - Terlalu Banyak Permintaan')
@section('code', '429')
@section('status', 'Too Many Requests')
@section('badge-color', 'text-purple-600 dark:text-purple-400')
@section('icon-bg', 'bg-purple-100 text-purple-600 dark:bg-purple-950/40 dark:text-purple-400')

@section('icon')
<svg class="h-10 w-10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.002A11.959 11.959 0 0 1 12 2.714Zm0 14.286a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z" />
</svg>
@endsection

@section('heading', 'Terlalu Banyak Permintaan')

@section('message')
    {{ $exception->getMessage() ?: 'Sistem mendeteksi aktivitas berlebih dalam waktu singkat (Rate Limiting). Silakan tunggu beberapa saat sebelum mencoba kembali.' }}
@endsection

