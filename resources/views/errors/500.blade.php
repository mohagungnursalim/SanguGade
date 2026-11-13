@extends('errors.layout')

@section('title', '500 Server Error - Kesalahan Aplikasi')
@section('code', '500')
@section('status', 'Server Error')
@section('badge-color', 'text-rose-600 dark:text-rose-400')
@section('icon-bg', 'bg-rose-100 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400')

@section('icon')
<svg class="h-10 w-10" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
</svg>
@endsection

@section('heading', 'Terjadi Kesalahan pada Server')

@section('message')
    Maaf, terjadi kendala teknis internal pada sistem kami. Tim developer telah menerima laporan dan sedang berupaya memperbaikinya secepat mungkin.
@endsection

