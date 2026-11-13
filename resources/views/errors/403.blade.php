<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="robots" content="noindex, nofollow">
	<title>Akses Ditolak (403)</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-50 px-6 py-12">
	<main class="w-full max-w-xl rounded-lg border border-gray-200 bg-white p-8 text-center shadow-sm sm:p-12">
		<div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-amber-100">
			<svg class="h-8 w-8 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
				<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
			</svg>
		</div>
		<p class="mb-2 text-sm font-semibold uppercase tracking-widest text-amber-600">Error 403</p>
		<h1 class="mb-4 text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">Akses Ditolak</h1>
		<p class="mb-8 leading-relaxed text-gray-600">
			Maaf, Anda tidak memiliki izin untuk membuka halaman ini. Silakan masuk menggunakan akun yang memiliki akses, atau hubungi administrator jika Anda merasa ini adalah kesalahan.
		</p>
		<div class="flex flex-col justify-center gap-3 sm:flex-row">
			<a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-700 px-5 py-3 text-sm font-medium text-white hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300">
				<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
					<path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955a1.125 1.125 0 0 1 1.592 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125h4.125v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
				</svg>
				Kembali ke Beranda
			</a>
			<button type="button" onclick="window.history.back()" class="rounded-lg border border-gray-300 bg-white px-5 py-3 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-200">
				Halaman Sebelumnya
			</button>
		</div>
		<p class="mt-8 text-sm text-gray-500">Butuh bantuan? Silakan hubungi administrator.</p>
	</main>
	
</body>
</html>
