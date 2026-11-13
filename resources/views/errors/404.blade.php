@extends('errors.layout')

@section('title', '404 Not Found - Halaman Tidak Ditemukan')
@section('code', '404')
@section('status', 'Not Found')
@section('badge-color', 'text-blue-600 dark:text-blue-400')
@section('icon-bg', 'bg-blue-100 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400')

@section('icon')
<svg class="h-10 w-10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM10.5 7.5v6m3-3h-6" />
</svg>
@endsection

@section('heading', 'Halaman Tidak Ditemukan')

@section('message')
    {{ $exception->getMessage() ?: 'Halaman atau data yang Anda cari tidak dapat ditemukan. Periksa kembali tautan yang dimasukkan atau navigasikan kembali ke halaman utama.' }}
@endsection
