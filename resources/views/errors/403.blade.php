@extends('errors.layout')

@section('title', '403 Forbidden - Hak Akses Dibatasi')
@section('code', '403')
@section('status', 'Forbidden')
@section('badge-color', 'text-amber-600 dark:text-amber-400')
@section('icon-bg', 'bg-amber-100 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400')

@section('icon')
<svg class="h-10 w-10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
</svg>
@endsection

@section('heading', 'Akses Ditolak')

@section('message')
    {{ $exception->getMessage() ?: 'Maaf, Anda tidak memiliki izin atau hak akses untuk membuka halaman ini. Silakan gunakan akun yang berwenang atau hubungi administrator.' }}
@endsection
