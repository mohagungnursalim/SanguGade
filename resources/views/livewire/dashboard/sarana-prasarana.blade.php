<div
    class="p-6 text-gray-900 dark:text-gray-100"
    x-data="pasarManager()"
    x-init="init()"
    @pasar-saved.window="closeCreateModal()"
    @pasar-updated.window="closeEditModal()"
    @notify.window="showNotify($event.detail)"
>

    {{-- =====================================================================
         NOTIFIKASI
    ====================================================================== --}}
    <div
        x-show="notify.show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        x-cloak
        class="fixed top-5 right-5 z-[100] flex items-center gap-3 px-5 py-3 rounded-lg shadow-lg text-white text-sm font-medium"
        :class="notify.type === 'success' ? 'bg-green-600' : 'bg-red-600'"
    >
        <i class="fas" :class="notify.type === 'success' ? 'fa-check-circle' : 'fa-times-circle'"></i>
        <span x-text="notify.message"></span>
    </div>


    {{-- =====================================================================
         HEADER
    ====================================================================== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-800 dark:text-white">Sarana Prasarana Pasar</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                Total:
                <span class="font-semibold text-blue-600 dark:text-blue-400">{{ $totalPasar }} Pasar</span>
            </p>
        </div>

        {{-- Tombol Tambah --}}
        <button
            type="button"
            @click="openCreateModal()"
            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow transition"
        >
            <i class="fas fa-plus"></i>
            Tambah Pasar
        </button>
    </div>


    {{-- =====================================================================
         SEARCH
    ====================================================================== --}}
    <div class="mb-5">
        <div class="relative max-w-sm">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <i class="fa-solid fa-magnifying-glass text-gray-400 dark:text-gray-500"></i>
            </div>
            <input
                type="text"
                wire:model.live.debounce.400ms="search"
                placeholder="Cari nama pasar, kecamatan, kelurahan..."
                class="bg-gray-50 border border-gray-300 text-gray-900 placeholder-gray-400 dark:placeholder-gray-500 text-sm rounded-lg
                       focus:ring-blue-500 focus:border-blue-500
                       block w-full pl-10 pr-4 py-2.5
                       dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
            />
        </div>
        <div wire:loading wire:target="search" class="flex items-center gap-2 mt-2 text-gray-500 dark:text-gray-400">
            <i class="fas fa-spinner fa-spin text-xs"></i>
            <span class="text-xs">Mencari...</span>
        </div>
    </div>


    {{-- =====================================================================
         TABEL DATA
    ====================================================================== --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-center text-gray-700 dark:text-gray-300 border-collapse">

    {{-- ── THEAD: 2 baris dengan rowspan & colspan ── --}}
    <thead>
        {{-- Baris 1 --}}
        <tr class="bg-gray-200 dark:bg-gray-700 dark:text-gray-200">
            <th rowspan="2" class="border border-gray-600 px-2 py-2 align-middle whitespace-nowrap">No</th>
            <th rowspan="2" class="border border-gray-600 px-2 py-2 align-middle whitespace-nowrap min-w-[150px]">Nama Pasar</th>
            <th rowspan="2" class="border border-gray-600 px-2 py-2 align-middle whitespace-nowrap">Foto</th>
            <th rowspan="2" class="border border-gray-600 px-2 py-2 align-middle whitespace-nowrap">Tahun Pembangunan</th>
            <th rowspan="2" class="border border-gray-600 px-2 py-2 align-middle whitespace-nowrap">Luas Lahan m²</th>

            <th colspan="2" class="border border-gray-600 px-2 py-2 align-middle">Sertifikat</th>

            <th rowspan="2" class="border border-gray-600 px-2 py-2 align-middle whitespace-nowrap">Kecamatan</th>
            <th rowspan="2" class="border border-gray-600 px-2 py-2 align-middle whitespace-nowrap">Kelurahan</th>
            <th rowspan="2" class="border border-gray-600 px-2 py-2 align-middle whitespace-nowrap">Status Pasar</th>

            <th colspan="6" class="border border-gray-600 px-2 py-2 align-middle">Jumlah</th>

            <th colspan="3" class="border border-gray-600 px-2 py-2 align-middle">Kondisi Bangunan</th>

            <th colspan="4" class="border border-gray-600 px-2 py-2 align-middle">Jumlah Tenaga</th>

            <th rowspan="2" class="border border-gray-600 px-2 py-2 align-middle whitespace-nowrap">Aksi</th>
        </tr>

        {{-- Baris 2 --}}
        <tr class="bg-gray-200 dark:bg-gray-700 dark:text-gray-200">

            {{-- Sertifikat --}}
            <th class="border border-gray-600 px-2 py-1.5 whitespace-nowrap">Ada</th>
            <th class="border border-gray-600 px-2 py-1.5 whitespace-nowrap">Tidak</th>

            {{-- Jumlah --}}
            <th class="border border-gray-600 px-2 py-1.5 whitespace-nowrap">Pedagang Tetap (PT)</th>
            <th class="border border-gray-600 px-2 py-1.5 whitespace-nowrap">Pedagang Tidak Tetap (PTT)</th>
            <th class="border border-gray-600 px-2 py-1.5 whitespace-nowrap">Kios Petak</th>
            <th class="border border-gray-600 px-2 py-1.5 whitespace-nowrap">Los Petak</th>
            <th class="border border-gray-600 px-2 py-1.5 whitespace-nowrap">Lapak/ Pelataran</th>
            <th class="border border-gray-600 px-2 py-1.5 whitespace-nowrap">Ruko</th>

            {{-- Kondisi --}}
            <th class="border border-gray-600 px-2 py-1.5 whitespace-nowrap">Baik</th>
            <th class="border border-gray-600 px-2 py-1.5 whitespace-nowrap">Rusak</th>
            <th class="border border-gray-600 px-2 py-1.5 whitespace-nowrap">Terpakai</th>

            {{-- Tenaga --}}
            <th class="border border-gray-600 px-2 py-1.5 whitespace-nowrap">Kepala Pasar</th>
            <th class="border border-gray-600 px-2 py-1.5 whitespace-nowrap">Kebersihan</th>
            <th class="border border-gray-600 px-2 py-1.5 whitespace-nowrap">Keamanan</th>
            <th class="border border-gray-600 px-2 py-1.5 whitespace-nowrap">Retribusi</th>

        </tr>
    </thead>

    {{-- ── TBODY ── --}}
    <tbody @click.stop="
        const btn = $event.target.closest('.btn-edit-pasar');
        if (btn) {
            const pasarData = JSON.parse(btn.dataset.pasar);
            openEditModal(pasarData);
        }
    ">
        @forelse ($pasars as $index => $pasar)
        <tr wire:key="pasar-row{{ $pasar->id }}" class="bg-white dark:bg-gray-800 hover:bg-blue-50 dark:hover:bg-gray-700 transition border-b border-gray-200 dark:border-gray-700">

            {{-- No --}}
            <td class="border border-gray-600 dark:border-gray-700 px-2 py-2">
                {{ $pasars->firstItem() + $index }}
            </td>

            {{-- Nama Pasar --}}
            <td class="border border-gray-600 dark:border-gray-700 px-2 py-2 text-left font-medium text-gray-900 dark:text-white">
                {{ $pasar->nama_pasar }}
            </td>

            {{-- Foto --}}
            <td class="border border-gray-600 dark:border-gray-700 px-2 py-2">
                @if ($pasar->image)
                    <img
                        src="{{ asset('storage/' . $pasar->image) }}"
                        alt="{{ $pasar->nama_pasar }}"
                        @click="$dispatch('open-image-modal', { src: '{{ asset('storage/' . $pasar->image) }}', title: '{{ $pasar->nama_pasar }}' })"
                        class="w-12 h-12 object-cover rounded border border-gray-600 dark:border-gray-600 mx-auto cursor-pointer hover:opacity-80 transition duration-150 shadow-sm"
                        title="Klik untuk memperbesar"
                    />
                @else
                    <div class="w-12 h-12 flex items-center justify-center rounded bg-gray-100 dark:bg-gray-700 border border-gray-600 dark:border-gray-600 mx-auto">
                        <i class="fas fa-store text-gray-300 dark:text-gray-600"></i>
                    </div>
                @endif
            </td>

            {{-- Tahun Pembangunan --}}
            <td class="border border-gray-600 dark:border-gray-700 px-2 py-2">
                {{ $pasar->tahun_pembangunan ?? '-' }}
            </td>

            {{-- Luas Lahan --}}
            <td class="border border-gray-600 dark:border-gray-700 px-2 py-2">
                {{ $pasar->luas_lahan ? number_format($pasar->luas_lahan) : '-' }}
            </td>

            {{-- Sertifikat: Ada --}}
            <td class="border border-gray-600 dark:border-gray-700 px-2 py-2">
                @if ($pasar->ada_sertifikat)
                    <i class="fas fa-check text-green-600 dark:text-green-400"></i>
                @endif
            </td>

            {{-- Sertifikat: Tidak --}}
            <td class="border border-gray-600 dark:border-gray-700 px-2 py-2">
                @if (!$pasar->ada_sertifikat)
                    <i class="fas fa-times text-red-500 dark:text-red-400"></i>
                @endif
            </td>

            {{-- Kecamatan --}}
            <td class="border border-gray-600 dark:border-gray-700 px-2 py-2">{{ $pasar->kecamatan }}</td>

            {{-- Kelurahan --}}
            <td class="border border-gray-600 dark:border-gray-700 px-2 py-2">{{ $pasar->kelurahan }}</td>

            {{-- Status --}}
            <td class="border border-gray-600 dark:border-gray-700 px-2 py-2">
                @if ($pasar->status_pasar === 'harian')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                        Harian
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                        Mingguan
                    </span>
                @endif
            </td>

            {{-- Jumlah Pedagang --}}
            <td class="border border-gray-600 dark:border-gray-700 px-2 py-2">{{ $pasar->jumlah_pedagang }}</td>
            <td class="border border-gray-600 dark:border-gray-700 px-2 py-2">{{ $pasar->ptt }}</td>

            {{-- Kios --}}
            <td class="border border-gray-600 dark:border-gray-700 px-2 py-2">{{ $pasar->kios }}</td>

            {{-- Los --}}
            <td class="border border-gray-600 dark:border-gray-700 px-2 py-2">{{ $pasar->los }}</td>

            {{-- Lapak --}}
            <td class="border border-gray-600 dark:border-gray-700 px-2 py-2">{{ $pasar->lapak }}</td>

            {{-- Ruko --}}
            <td class="border border-gray-600 dark:border-gray-700 px-2 py-2">{{ $pasar->ruko }}</td>

            {{-- Kondisi Baik --}}
            <td class="border border-gray-600 dark:border-gray-700 px-2 py-2 text-green-700 dark:text-green-400 font-medium">{{ $pasar->kondisi_baik }}</td>

            {{-- Kondisi Rusak --}}
            <td class="border border-gray-600 dark:border-gray-700 px-2 py-2 text-red-600 dark:text-red-400 font-medium">{{ $pasar->kondisi_rusak }}</td>

            {{-- Terpakai --}}
            <td class="border border-gray-600 dark:border-gray-700 px-2 py-2">{{ $pasar->kondisi_terpakai }}</td>

            {{-- Kepala Pasar --}}
            <td class="border border-gray-600 dark:border-gray-700 px-2 py-2">{{ $pasar->kepala_pasar }}</td>

            {{-- Kebersihan --}}
            <td class="border border-gray-600 dark:border-gray-700 px-2 py-2">{{ $pasar->petugas_kebersihan }}</td>

            {{-- Keamanan --}}
            <td class="border border-gray-600 dark:border-gray-700 px-2 py-2">{{ $pasar->petugas_keamanan }}</td>

            {{-- Retribusi --}}
            <td class="border border-gray-600 dark:border-gray-700 px-2 py-2">{{ $pasar->retribusi }}</td>


            {{-- Aksi --}}
            <td class="border border-gray-600 dark:border-gray-700 px-2 py-2">
                <div class="flex items-center justify-center gap-1">

                    {{-- Tombol Edit menggunakan class 'btn-edit-pasar' dan dataset JSON --}}
                    <button
                        type="button"
                        data-pasar="{{ json_encode($pasar) }}"
                        class="btn-edit-pasar inline-flex items-center justify-center w-7 h-7 rounded bg-amber-400 hover:bg-amber-500 text-white transition cursor-pointer"
                        title="Edit"
                    >
                        <i class="fas fa-edit text-xs pointer-events-none"></i>
                    </button>

                    {{-- Hapus --}}
                    <button
                        type="button"
                        wire:click="confirmDelete({{ $pasar->id }})"
                        wire:loading.attr="disabled"
                        wire:target="confirmDelete({{ $pasar->id }})"
                        class="inline-flex items-center justify-center w-7 h-7 rounded
                               bg-red-500 hover:bg-red-600 text-white transition disabled:opacity-50"
                        title="Hapus"
                    >
                        <i class="fas fa-trash text-xs"></i>
                    </button>

                </div>
            </td>

        </tr>
        @empty
        <tr wire:key="pasar-empty">
            <td colspan="25" class="px-6 py-16 text-center border border-gray-200 dark:border-gray-700">
                <div class="flex flex-col items-center gap-3 text-gray-400 dark:text-gray-500">
                    <i class="fas fa-store-slash text-4xl"></i>
                    <p class="font-medium text-gray-500 dark:text-gray-400">Belum ada data pasar</p>
                    <p class="text-xs">Klik tombol <span class="font-semibold text-blue-500">Tambah Pasar</span> untuk menambahkan data.</p>
                </div>
            </td>
        </tr>
        @endforelse
    </tbody>

    {{-- ── TFOOT: Baris Total ── --}}
    @if ($pasars->isNotEmpty())
    <tfoot>
        <tr class="bg-gray-200 dark:bg-gray-700 dark:text-gray-200 font-semibold text-xs">

            <td colspan="5" class="border border-gray-600 px-3 py-2 text-center font-bold tracking-wide uppercase">
                Total
            </td>

            {{-- Total Luas --}}
            {{-- <td class="border border-gray-600 px-2 py-2 text-center">
                <span class="bg-blue-600 text-white text-xs font-bold px-1.5 py-0.5 rounded">
                    {{ number_format($totals->total_luas ?? 0) }} m²
                </span>
            </td> --}}

            {{-- Sertifikat Ada --}}
            <td class="border border-gray-600 px-2 py-2 text-center">
                <span class="bg-blue-500 text-white text-xs font-bold px-1.5 py-0.5 rounded">
                    {{ $totals->total_sertifikat_ada ?? 0 }}
                </span>
            </td>

            {{-- Sertifikat Tidak --}}
            <td class="border border-gray-600 px-2 py-2 text-center">
                <span class="bg-gray-500 text-white text-xs font-bold px-1.5 py-0.5 rounded">
                    {{ $totals->total_sertifikat_tidak ?? 0 }}
                </span>
            </td>

            {{-- Kecamatan, Kelurahan, Status (kosong) --}}
            <td class="border border-gray-600 px-2 py-2"></td>
            <td class="border border-gray-600 px-2 py-2"></td>
            <td class="border border-gray-600 px-2 py-2"></td>

            {{-- Total Pedagang --}}
            <td class="border border-gray-600 px-2 py-2 text-center">
                <span class="bg-green-600 text-white text-xs font-bold px-1.5 py-0.5 rounded">
                    {{ number_format($totals->total_pedagang ?? 0) }}
                </span>
            </td>

            {{-- PTT: total belum tersedia --}}
            <td class="border border-gray-600 px-2 py-2">
                <span class="bg-green-600 text-white text-xs font-bold px-1.5 py-0.5 rounded">
                    {{ number_format($totals->total_ptt ?? 0) }}
                </span>
            </td>

            {{-- Total Kios --}}
            <td class="border border-gray-600 px-2 py-2 text-center">
                <span class="bg-green-600 text-white text-xs font-bold px-1.5 py-0.5 rounded">
                    {{ number_format($totals->total_kios ?? 0) }}
                </span>
            </td>

            {{-- Total Los --}}
            <td class="border border-gray-600 px-2 py-2 text-center">
                <span class="bg-green-600 text-white text-xs font-bold px-1.5 py-0.5 rounded">
                    {{ number_format($totals->total_los ?? 0) }}
                </span>
            </td>

            {{-- Total Lapak --}}
            <td class="border border-gray-600 px-2 py-2 text-center">
                <span class="bg-green-600 text-white text-xs font-bold px-1.5 py-0.5 rounded">
                    {{ number_format($totals->total_lapak ?? 0) }}
                </span>
            </td>

            {{-- Total Ruko --}}
            <td class="border border-gray-600 px-2 py-2 text-center">
                <span class="bg-green-600 text-white text-xs font-bold px-1.5 py-0.5 rounded">
                    {{ number_format($totals->total_ruko ?? 0) }}
                </span>
            </td>

            {{-- Total Kondisi Baik --}}
            <td class="border border-gray-600 px-2 py-2 text-center">
                <span class="bg-green-600 text-white text-xs font-bold px-1.5 py-0.5 rounded">
                    {{ number_format($totals->total_baik ?? 0) }}
                </span>
            </td>

            {{-- Total Kondisi Rusak --}}
            <td class="border border-gray-600 px-2 py-2 text-center">
                <span class="bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded">
                    {{ number_format($totals->total_rusak ?? 0) }}
                </span>
            </td>

            {{-- Total Terpakai --}}
            <td class="border border-gray-600 px-2 py-2 text-center">
                <span class="bg-green-600 text-white text-xs font-bold px-1.5 py-0.5 rounded">
                    {{ number_format($totals->total_terpakai ?? 0) }}
                </span>
            </td>

            {{-- Total Kepala Pasar --}}
            <td class="border border-gray-600 px-2 py-2 text-center">
                <span class="bg-green-600 text-white text-xs font-bold px-1.5 py-0.5 rounded">
                    {{ number_format($totals->total_kepala_pasar ?? 0) }}
                </span>
            </td>

            {{-- Total Kebersihan --}}
            <td class="border border-gray-600 px-2 py-2 text-center">
                <span class="bg-green-600 text-white text-xs font-bold px-1.5 py-0.5 rounded">
                    {{ number_format($totals->total_kebersihan ?? 0) }}
                </span>
            </td>

            {{-- Total Keamanan --}}
            <td class="border border-gray-600 px-2 py-2 text-center">
                <span class="bg-green-600 text-white text-xs font-bold px-1.5 py-0.5 rounded">
                    {{ number_format($totals->total_keamanan ?? 0) }}
                </span>
            </td>

            {{-- Total Retribusi --}}
            <td class="border border-gray-600 px-2 py-2 text-center">
                <span class="bg-green-600 text-white text-xs font-bold px-1.5 py-0.5 rounded">
                    {{ number_format($totals->total_retribusi ?? 0) }}
                </span>
            </td>

            {{-- PTT kedua: total belum tersedia --}}
            <td class="border border-gray-600 px-2 py-2"></td>

            {{-- Aksi (kosong di footer) --}}
            <td class="border border-gray-600 px-2 py-2"></td>

        </tr>
    </tfoot>
    @endif

</table>
        </div>

        {{-- Pagination --}}
        @if ($pasars->hasPages())
        <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
            {{ $pasars->links() }}
        </div>
        @endif
    </div>


    {{-- =====================================================================
         MODAL TAMBAH & EDIT (SATU MODAL REUSABLE)
    ====================================================================== --}}
    <div
        x-show="modalOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-cloak
        class="fixed inset-0 z-50 flex items-start justify-center bg-black/50 backdrop-blur-sm overflow-y-auto py-8 px-4"
    >
        <div
            
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="w-full max-w-3xl bg-white dark:bg-gray-800 rounded-2xl shadow-2xl"
        >

            {{-- Header Modal --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center"
                         :class="mode === 'create' ? 'bg-blue-100 dark:bg-blue-900/40' : 'bg-amber-100 dark:bg-amber-900/40'">
                        <i class="fas text-sm"
                           :class="mode === 'create' ? 'fa-plus text-blue-600 dark:text-blue-400' : 'fa-pencil text-amber-600 dark:text-amber-400'"></i>
                    </div>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white"
                        x-text="mode === 'create' ? 'Tambah Pasar Baru' : 'Edit Data Pasar'"></h2>
                </div>
                <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>


            {{-- ─── FORM CREATE ──────────────────────────────────────────── --}}
            <div x-show="mode === 'create'">
                <form
                    wire:submit.prevent="store"
                    x-data="{ progress: 0, isUploading: false }"
                    x-on:livewire-upload-start="isUploading = true; progress = 0"
                    x-on:livewire-upload-progress="progress = $event.detail.progress"
                    x-on:livewire-upload-finish="isUploading = false; progress = 0"
                    x-on:livewire-upload-error="isUploading = false; progress = 0"
                    class="px-6 py-5 space-y-6">

                    {{-- 1. Informasi Umum --}}
                    <fieldset class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <legend class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-3 flex items-center gap-2">
                            <i class="fas fa-info-circle"></i> Informasi Umum
                        </legend>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                            {{-- Nama Pasar --}}
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Nama Pasar <span class="text-red-500">*</span>
                                </label>
                                <input
                                    wire:model.defer="nama_pasar"
                                    type="text"
                                    placeholder="Masukkan nama pasar..."
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg
                                           bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                           focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                           placeholder-gray-400 dark:placeholder-gray-500"
                                />
                                @error('nama_pasar')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Foto Pasar --}}
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Foto Pasar</label>

                               {{-- Dropzone Create --}}
                                <div class="relative border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4 text-center hover:border-blue-400 transition">
                                    <input
                                        type="file"
                                        accept="image/*"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                        x-on:change="
                                            const file = $event.target.files[0];
                                            if (!file) return;

                                            if (file.size > 2 * 1024 * 1024) { 
                                                $event.target.value = null; 
                                                $dispatch('notify', { type: 'error', message: 'Gagal unggah: Ukuran tidak boleh melebihi 2MB!' });
                                                return; 
                                            }

                                            // Aktifkan state loading manual
                                            isUploading = true;
                                            progress = 0;

                                            // Eksekusi upload Livewire dengan menangkap callback progress & finish
                                            $wire.upload('image', file, (uploadedFilename) => {
                                                // Selesai upload sukses
                                                isUploading = false;
                                            }, (error) => {
                                                // Jika error
                                                isUploading = false;
                                                progress = 0;
                                                $dispatch('notify', { type: 'error', message: 'Gagal mengunggah gambar!' });
                                            }, (event) => {
                                                // Menangkap angka persentase progres secara real-time!
                                                progress = event.detail.progress;
                                            });
                                        "
                                    />
                                    
                                    {{-- Tampil jika TIDAK sedang upload --}}
                                    <div x-show="!isUploading">
                                        <i class="fas fa-cloud-upload-alt text-2xl text-gray-300 dark:text-gray-500 mb-1"></i>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">Klik atau seret gambar ke sini</p>
                                        <p class="text-xs text-gray-300 dark:text-gray-600">JPG, PNG, WEBP — maks. 2MB</p>
                                    </div>
                                    
                                    {{-- Tampil HANYA jika file sedang di-upload secara nyata --}}
                                    <div x-show="isUploading" x-cloak class="flex items-center justify-center gap-2 text-blue-500 text-sm py-3">
                                        <i class="fas fa-spinner fa-spin"></i> Mengunggah... <span x-text="progress"></span>%
                                    </div>
                                </div>

                                {{-- Progress Bar --}}
                                <div x-show="isUploading && progress > 0" class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full transition-all duration-75"
                                        :class="progress < 100 ? 'bg-blue-500' : 'bg-green-500'"
                                        :style="'width:' + progress + '%'"></div>
                                </div>

                                {{-- Preview --}}
                                @if ($image)
                                <div class="mt-3 flex items-center gap-3">
                                    <img src="{{ $image->temporaryUrl() }}" alt="Preview"
                                         class="w-20 h-20 object-cover rounded-lg border border-gray-200 dark:border-gray-600" />
                                    <button type="button" wire:click="removeImage"
                                            class="text-xs text-red-500 hover:text-red-700 flex items-center gap-1">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </div>
                                @endif

                                @error('image')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Tahun Pembangunan --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tahun Pembangunan</label>
                                <input
                                    wire:model.defer="tahun_pembangunan"
                                    type="number"
                                    min="1900"
                                    max="{{ date('Y') }}"
                                    placeholder="cth. 2005"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 placeholder:text-gray-400 rounded-lg
                                           bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                           focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                                @error('tahun_pembangunan')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Luas Lahan --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Luas Lahan (m²)</label>
                                <input
                                    wire:model.defer="luas_lahan"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="cth. 1500.50"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 placeholder:text-gray-400 rounded-lg
                                           bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                           focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                                @error('luas_lahan')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Sertifikat --}}
                            <div class="sm:col-span-2 flex items-center gap-2">
                                <input
                                    wire:model.defer="ada_sertifikat"
                                    id="ada_sertifikat"
                                    type="checkbox"
                                    class="w-4 h-4 text-blue-600 rounded border-gray-300 dark:border-gray-600 focus:ring-blue-500"
                                />
                                <label for="ada_sertifikat" class="text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                    Memiliki sertifikat tanah
                                </label>
                            </div>

                        </div>
                    </fieldset>

                    {{-- 2. Lokasi & Status --}}
                    <fieldset class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <legend class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-3 flex items-center gap-2">
                            <i class="fas fa-map-marker-alt"></i> Lokasi &amp; Status
                        </legend>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Kecamatan <span class="text-red-500">*</span>
                                </label>
                                <input
                                    wire:model.defer="kecamatan"
                                    type="text"
                                    placeholder="Kecamatan..."
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 placeholder:text-gray-400 rounded-lg
                                           bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                           focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                                @error('kecamatan')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Kelurahan <span class="text-red-500">*</span>
                                </label>
                                <input
                                    wire:model.defer="kelurahan"
                                    type="text"
                                    placeholder="Kelurahan..."
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 placeholder:text-gray-400 rounded-lg
                                           bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                           focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                                @error('kelurahan')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Status Pasar <span class="text-red-500">*</span>
                                </label>
                                <select
                                    wire:model.defer="status_pasar"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg
                                        bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                        focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="harian">Harian</option>
                                    <option value="mingguan">Mingguan</option>
                                </select>
                                @error('status_pasar')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>
                    </fieldset>

                    {{-- 3. Aktivitas & Kapasitas --}}
                    <fieldset class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <legend class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-3 flex items-center gap-2">
                            <i class="fas fa-users"></i> Data Aktivitas &amp; Kapasitas
                        </legend>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                            @foreach([
                                ['jumlah_pedagang', 'Pedagang Tetap'],
                                ['ptt', 'Pedagang Tidak Tetap (PTT)'],
                                ['kios', 'Kios/Petak'],
                                ['los', 'Los/Petak'],
                                ['lapak', 'Lapak Pelataran'],
                                ['ruko', 'Ruko'],
                            ] as [$field, $label])
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ $label }}</label>
                                <input
                                    wire:model.defer="{{ $field }}"
                                    type="number"
                                    min="0"
                                    value="0"
                                    class="w-full px-2 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg
                                           bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                           focus:ring-2 focus:ring-blue-500 focus:border-transparent text-center"
                                />
                                @error($field)
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            @endforeach
                        </div>
                    </fieldset>

                    {{-- 4. Kondisi Sarana --}}
                    <fieldset class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <legend class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-3 flex items-center gap-2">
                            <i class="fas fa-building"></i> Kondisi Sarana
                        </legend>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach([
                                ['kondisi_baik', 'Kondisi Baik', 'green'],
                                ['kondisi_rusak', 'Kondisi Rusak', 'red'],
                                ['kondisi_terpakai', 'Terpakai', 'blue'],
                            ] as [$field, $label, $color])
                            <div>
                                <label class="block text-xs font-medium mb-1
                                    {{ $color === 'green' ? 'text-green-600 dark:text-green-400' : ($color === 'red' ? 'text-red-600 dark:text-red-400' : 'text-blue-600 dark:text-blue-400') }}">
                                    {{ $label }}
                                </label>
                                <input
                                    wire:model.defer="{{ $field }}"
                                    type="number"
                                    min="0"
                                    value="0"
                                    class="w-full px-2 py-2 text-sm border rounded-lg text-center
                                           bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                           focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                           {{ $color === 'green' ? 'border-green-300 dark:border-green-700' : ($color === 'red' ? 'border-red-300 dark:border-red-700' : 'border-blue-300 dark:border-blue-700') }}"
                                />
                                @error($field)
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            @endforeach
                        </div>
                    </fieldset>

                    {{-- 5. Pengelolaan --}}
                    <fieldset class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <legend class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-3 flex items-center gap-2">
                            <i class="fas fa-user-tie"></i> Pengelolaan Pasar
                        </legend>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            @foreach([
                                ['kepala_pasar', 'Kepala Pasar'],
                                ['petugas_kebersihan', 'Kebersihan'],
                                ['petugas_keamanan', 'Keamanan'],
                                ['retribusi', 'Retribusi (Rp)'],
                            ] as [$field, $label])
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ $label }}</label>
                                <input
                                    wire:model.defer="{{ $field }}"
                                    type="number"
                                    min="0"
                                    value="0"
                                    class="w-full px-2 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg
                                           bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                           focus:ring-2 focus:ring-blue-500 focus:border-transparent text-center"
                                />
                                @error($field)
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            @endforeach
                        </div>
                    </fieldset>


                    {{-- Footer Form Create --}}
                    <div class="flex justify-center gap-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="closeModal()"
                                class="px-5 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition">
                            Batal
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="store,image"
                            class="px-6 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow transition disabled:opacity-70 disabled:cursor-not-allowed min-w-[100px]"
                        >
                            <span wire:loading.remove wire:target="store">
                                <i class="fas fa-save mr-1"></i> Simpan
                            </span>
                            <span wire:loading wire:target="store" class="flex items-center justify-center gap-2">
                                <i class="fas fa-spinner fa-spin"></i> Menyimpan...
                            </span>
                        </button>
                    </div>

                </form>
            </div>


            {{-- ─── FORM EDIT ────────────────────────────────────────────── --}}
            <div x-show="mode === 'edit'">
                <form
                    wire:submit.prevent="update"
                    x-data="{ progress: 0, isUploading: false, tempEditPreview: null }"
                    x-on:livewire-upload-start="isUploading = true; progress = 0"
                    x-on:livewire-upload-progress="progress = $event.detail.progress"
                    x-on:livewire-upload-finish="isUploading = false; progress = 0"
                    x-on:livewire-upload-error="isUploading = false; progress = 0"
                    class="px-6 py-5 space-y-6">

                    {{-- 1. Informasi Umum --}}
                    <fieldset class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <legend class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-3 flex items-center gap-2">
                            <i class="fas fa-info-circle"></i> Informasi Umum
                        </legend>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                            {{-- Nama Pasar --}}
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Nama Pasar <span class="text-red-500">*</span>
                                </label>
                                <input
                                    wire:model.defer="editNamaPasar"
                                    type="text"
                                    placeholder="Masukkan nama pasar..."
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg
                                           bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                           focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                                @error('editNamaPasar')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Foto Pasar (Edit) --}}
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Foto Pasar</label>

                                {{-- Foto lama --}}
                                @if ($editImagePath && !$editImage)
                                <div class="mb-3 flex items-center gap-3">
                                    <img src="{{ asset('storage/' . $editImagePath) }}" alt="Foto Pasar"
                                         class="w-20 h-20 object-cover rounded-lg border border-gray-200 dark:border-gray-600" />
                                    <p class="text-xs text-gray-400 dark:text-gray-500">Foto saat ini. Unggah file baru untuk menggantinya.</p>
                                </div>
                                @endif

                               {{-- Dropzone Edit --}}
                                <div class="relative border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4 text-center hover:border-amber-400 transition">
                                    <input
                                        type="file"
                                        accept="image/*"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                        x-on:change="
                                            const file = $event.target.files[0];
                                            if (!file) return;

                                            if (file.size > 2 * 1024 * 1024) { 
                                                $event.target.value = null; 
                                                $dispatch('notify', { type: 'error', message: 'Batal unggah: Ukuran melebihi 2MB!' });
                                                return; 
                                            }

                                            isUploading = true;
                                            progress = 0;

                                            $wire.upload('editImage', file, (uploadedFilename) => {
                                                isUploading = false;
                                            }, (error) => {
                                                isUploading = false;
                                                progress = 0;
                                                $dispatch('notify', { type: 'error', message: 'Gagal mengunggah gambar!' });
                                            }, (event) => {
                                                progress = event.detail.progress;
                                            });
                                        "
                                    />
                                    
                                    {{-- Tampil jika TIDAK sedang upload --}}
                                    <div x-show="!isUploading">
                                        <i class="fas fa-cloud-upload-alt text-2xl text-gray-300 dark:text-gray-500 mb-1"></i>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">Klik untuk mengganti foto</p>
                                        <p class="text-xs text-gray-300 dark:text-gray-600">JPG, PNG, WEBP — maks. 2MB</p>
                                    </div>
                                    
                                    {{-- Tampil HANYA jika file sedang diupload --}}
                                    <div x-show="isUploading" x-cloak class="flex items-center justify-center gap-2 text-amber-500 text-sm py-1">
                                        <i class="fas fa-spinner fa-spin"></i> Mengunggah... <span x-text="progress"></span>%
                                    </div>
                                </div>

                                {{-- Progress Bar Edit --}}
                                <div x-show="isUploading &&progress > 0" class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full transition-all duration-75"
                                        :class="progress < 100 ? 'bg-amber-500' : 'bg-green-500'"
                                        :style="'width:' + progress + '%'"></div>
                                </div>

                                {{-- Preview foto baru --}}
                                @if ($editImage)
                                <div class="mt-3 flex items-center gap-3">
                                    <img src="{{ $editImage->temporaryUrl() }}" alt="Preview"
                                         class="w-20 h-20 object-cover rounded-lg border border-gray-200 dark:border-gray-600" />
                                    <button type="button" wire:click="removeEditImage"
                                            class="text-xs text-red-500 hover:text-red-700 flex items-center gap-1">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </div>
                                @endif

                                @error('editImage')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Tahun Pembangunan (Edit) --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tahun Pembangunan</label>
                                <input
                                    wire:model.defer="editTahunPembangunan"
                                    type="number"
                                    min="1900"
                                    max="{{ date('Y') }}"
                                    placeholder="cth. 2005"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 placeholder:text-gray-400 rounded-lg
                                           bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                           focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                                @error('editTahunPembangunan')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Luas Lahan (Edit) --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Luas Lahan (m²)</label>
                                <input
                                    wire:model.defer="editLuasLahan"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="cth. 1500.50"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg
                                           bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                           focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                                @error('editLuasLahan')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Sertifikat (Edit) --}}
                            <div class="sm:col-span-2 flex items-center gap-2">
                                <input
                                    wire:model.defer="editAdaSertifikat"
                                    id="edit_ada_sertifikat"
                                    type="checkbox"
                                    class="w-4 h-4 text-blue-600 rounded border-gray-300 dark:border-gray-600 focus:ring-blue-500"
                                />
                                <label for="edit_ada_sertifikat" class="text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                    Memiliki sertifikat tanah
                                </label>
                            </div>

                        </div>
                    </fieldset>

                    {{-- 2. Lokasi & Status (Edit) --}}
                    <fieldset class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <legend class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-3 flex items-center gap-2">
                            <i class="fas fa-map-marker-alt"></i> Lokasi &amp; Status
                        </legend>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Kecamatan <span class="text-red-500">*</span>
                                </label>
                                <input
                                    wire:model.defer="editKecamatan"
                                    type="text"
                                    placeholder="Kecamatan..."
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg
                                           bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                           focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                                @error('editKecamatan')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Kelurahan <span class="text-red-500">*</span>
                                </label>
                                <input
                                    wire:model.defer="editKelurahan"
                                    type="text"
                                    placeholder="Kelurahan..."
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg
                                           bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                           focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                                @error('editKelurahan')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Status Pasar <span class="text-red-500">*</span>
                                </label>
                                <select
                                    wire:model.defer="editStatusPasar"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg
                                           bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                           focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option value="harian">Harian</option>
                                    <option value="mingguan">Mingguan</option>
                                </select>
                                @error('editStatusPasar')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>
                    </fieldset>

                    {{-- 3. Aktivitas & Kapasitas (Edit) --}}
                    <fieldset class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <legend class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-3 flex items-center gap-2">
                            <i class="fas fa-users"></i> Data Aktivitas &amp; Kapasitas
                        </legend>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                            @foreach([
                                ['editJumlahPedagang', 'Pedagang Tetap'],
                                ['editPtt', 'Pedagang Tidak Tetap (PTT)'],
                                ['editKios', 'Kios/Petak'],
                                ['editLos', 'Los/Petak'],
                                ['editLapak', 'Lapak Pelataran'],
                                ['editRuko', 'Ruko'],
                            ] as [$field, $label])
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ $label }}</label>
                                <input
                                    wire:model.defer="{{ $field }}"
                                    type="number"
                                    min="0"
                                    class="w-full px-2 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg
                                           bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                           focus:ring-2 focus:ring-blue-500 focus:border-transparent text-center"
                                />
                                @error($field)
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            @endforeach
                        </div>
                    </fieldset>

                    {{-- 4. Kondisi Sarana (Edit) --}}
                    <fieldset class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <legend class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-3 flex items-center gap-2">
                            <i class="fas fa-building"></i> Kondisi Sarana
                        </legend>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach([
                                ['editKondisiBaik', 'Kondisi Baik', 'green'],
                                ['editKondisiRusak', 'Kondisi Rusak', 'red'],
                                ['editKondisiTerpakai', 'Terpakai', 'blue'],
                            ] as [$field, $label, $color])
                            <div>
                                <label class="block text-xs font-medium mb-1
                                    {{ $color === 'green' ? 'text-green-600 dark:text-green-400' : ($color === 'red' ? 'text-red-600 dark:text-red-400' : 'text-blue-600 dark:text-blue-400') }}">
                                    {{ $label }}
                                </label>
                                <input
                                    wire:model.defer="{{ $field }}"
                                    type="number"
                                    min="0"
                                    class="w-full px-2 py-2 text-sm border rounded-lg text-center
                                           bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                           focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                           {{ $color === 'green' ? 'border-green-300 dark:border-green-700' : ($color === 'red' ? 'border-red-300 dark:border-red-700' : 'border-blue-300 dark:border-blue-700') }}"
                                />
                                @error($field)
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            @endforeach
                        </div>
                    </fieldset>

                    {{-- 5. Pengelolaan (Edit) --}}
                    <fieldset class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <legend class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-3 flex items-center gap-2">
                            <i class="fas fa-user-tie"></i> Pengelolaan Pasar
                        </legend>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            @foreach([
                                ['editKepalaPasar', 'Kepala Pasar'],
                                ['editPetugasKebersihan', 'Kebersihan'],
                                ['editPetugasKeamanan', 'Keamanan'],
                                ['editRetribusi', 'Retribusi (Rp)'],
                            ] as [$field, $label])
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ $label }}</label>
                                <input
                                    wire:model.defer="{{ $field }}"
                                    type="number"
                                    min="0"
                                    class="w-full px-2 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg
                                           bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                           focus:ring-2 focus:ring-blue-500 focus:border-transparent text-center"
                                />
                                @error($field)
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            @endforeach
                        </div>
                    </fieldset>

                    {{-- Footer Form Edit --}}
                    <div class="flex justify-center gap-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="closeModal()"
                                class="px-5 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition">
                            Batal
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="update,editImage"
                            class="px-6 py-2 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-lg shadow transition disabled:opacity-70 disabled:cursor-not-allowed min-w-[100px]">
                            <span wire:loading.remove wire:target="update">
                                <i class="fas fa-save mr-1"></i> Perbarui
                            </span>
                            <span wire:loading wire:target="update" class="flex items-center justify-center gap-2">
                                <i class="fas fa-spinner fa-spin"></i> Memperbarui...
                            </span>
                        </button>
                    </div>

                </form>
            </div>

        </div>{{-- end modal box --}}
    </div>{{-- end modal overlay --}}


    {{-- =====================================================================
         MODAL KONFIRMASI HAPUS
    ====================================================================== --}}
    <div
        x-data="{ showDeleteModal: @entangle('showDeleteModal') }">
        <div
            x-show="showDeleteModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4">
            <div
                @click.outside="showDeleteModal = false"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="w-full max-w-sm bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6">
                <div class="flex flex-col items-center text-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <i class="fas fa-trash-alt text-red-500 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800 dark:text-white text-base">Hapus Data Pasar?</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Data <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $deleteName }}</span>
                            akan dihapus permanen beserta fotonya.
                        </p>
                        <p class="text-xs text-red-500 mt-1 font-medium">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                    <div class="flex gap-3 w-full">
                        <button type="button" @click="showDeleteModal = false"
                                class="flex-1 px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition">
                            Batal
                        </button>
                        <button
                            wire:click="delete"
                            wire:target="delete"
                            wire:loading.attr="disabled"
                            class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg shadow transition disabled:opacity-70 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="delete">
                                <i class="fas fa-trash mr-1"></i> Hapus
                            </span>
                            <span wire:loading wire:target="delete" class="flex items-center justify-center gap-2">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- =====================================================================
         MODAL PREVIEW FOTO MENYELURUH (STANDALONE)
    ====================================================================== --}}
    <div
        x-data="{
            imageModalOpen: false,
            imgSrc: '',
            imgTitle: ''
        }"
        @open-image-modal.window="
            imgSrc = $event.detail.src;
            imgTitle = $event.detail.title;
            imageModalOpen = true;
        "
        x-show="imageModalOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @keydown.escape.window="imageModalOpen = false"
        x-cloak
        class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm overflow-y-auto">
        {{-- Backdrop --}}
        <div class="fixed inset-0" @click="imageModalOpen = false"></div>

        {{-- Box Modal --}}
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl overflow-hidden max-w-4xl w-full z-10 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-image text-blue-500"></i>
                    <span x-text="imgTitle"></span>
                </h3>
                <button type="button" @click="imageModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition focus:outline-none">
                    <i class="fas fa-times text-base"></i>
                </button>
            </div>
            <div class="p-4 flex items-center justify-center bg-gray-900/5 dark:bg-gray-900/40">
                <img :src="imgSrc" :alt="imgTitle" class="max-h-[75vh] w-auto object-contain rounded-lg shadow-md border border-gray-200 dark:border-gray-700" />
            </div>
            <div class="px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center text-xs">
                <span class="text-gray-500 dark:text-gray-400">Tekan <kbd class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 rounded text-[10px]">ESC</kbd> untuk menutup</span>
                <a :href="imgSrc" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                    <i class="fas fa-external-link-alt text-[10px]"></i> Buka Tab Baru
                </a>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
/**
 * Alpine.js component untuk manajemen modal Sarana Prasarana.
 * Modal dibuka sepenuhnya di sisi klien — tidak ada request ke server.
 */
function pasarManager() {
    return {
        // ─── State Modal ──────────────────────────────────────────────────
        modalOpen : false,
        mode      : 'create', // 'create' | 'edit'

        // ─── Notify ───────────────────────────────────────────────────────
        notify: { show: false, type: 'success', message: '', _timer: null },

        init() {
            // Listeners sudah ditangani oleh @pasar-saved.window dan @pasar-updated.window
        },

        // ─── Buka modal tambah ────────────────────────────────────────────
        openCreateModal() {
            this.mode = 'create';
            this.modalOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        // ─── Buka modal edit (data sudah ada di frontend via toJson()) ────
        openEditModal(pasar) {
            this.mode = 'edit';
            this.tempEditPreview = null;
            /**
             * Sinkronisasi nilai ke Livewire properties menggunakan $wire.set
             * agar form edit terisi tanpa round-trip tambahan ke server.
             * $wire.set tidak memicu re-render penuh, hanya memperbarui property.
             */
            this.$wire.set('editId',               pasar.id);
            this.$wire.set('editNamaPasar',         pasar.nama_pasar       ?? '');
            this.$wire.set('editTahunPembangunan',  String(pasar.tahun_pembangunan ?? ''));
            this.$wire.set('editLuasLahan',         String(pasar.luas_lahan        ?? ''));
            this.$wire.set('editAdaSertifikat',     !!pasar.ada_sertifikat);
            this.$wire.set('editKecamatan',         pasar.kecamatan        ?? '');
            this.$wire.set('editKelurahan',         pasar.kelurahan        ?? '');
            this.$wire.set('editStatusPasar',       pasar.status_pasar     ?? 'harian');
            this.$wire.set('editJumlahPedagang',    pasar.jumlah_pedagang  ?? 0);
            this.$wire.set('editPtt',               pasar.ptt              ?? 0);
            this.$wire.set('editKios',              pasar.kios             ?? 0);
            this.$wire.set('editLos',               pasar.los              ?? 0);
            this.$wire.set('editLapak',             pasar.lapak            ?? 0);
            this.$wire.set('editRuko',              pasar.ruko             ?? 0);
            this.$wire.set('editKondisiBaik',       pasar.kondisi_baik     ?? 0);
            this.$wire.set('editKondisiRusak',      pasar.kondisi_rusak    ?? 0);
            this.$wire.set('editKondisiTerpakai',   pasar.kondisi_terpakai ?? 0);
            this.$wire.set('editKepalaPasar',       pasar.kepala_pasar     ?? 0);
            this.$wire.set('editPetugasKebersihan', pasar.petugas_kebersihan ?? 0);
            this.$wire.set('editPetugasKeamanan',   pasar.petugas_keamanan ?? 0);
            this.$wire.set('editRetribusi',         pasar.retribusi        ?? 0);
            this.$wire.set('editPtt',               pasar.ptt              ?? 0);
            this.$wire.set('editImagePath',         pasar.image            ?? null);
            this.$wire.set('editImage',             null);

            this.modalOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        // ─── Tutup modal create ───────────────────────────────────────────
        closeCreateModal() {
            this.modalOpen = false;
            document.body.classList.remove('overflow-hidden');
        },

        // ─── Tutup modal edit ─────────────────────────────────────────────
        closeEditModal() {
            this.modalOpen = false;
            document.body.classList.remove('overflow-hidden');
        },

        // ─── Tutup modal (generik) ────────────────────────────────────────
        closeModal() {
            this.modalOpen = false;
            document.body.classList.remove('overflow-hidden');
        },

        // ─── Notifikasi ───────────────────────────────────────────────────
        showNotify(detail) {
            clearTimeout(this.notify._timer);
            this.notify.type    = detail.type    ?? 'success';
            this.notify.message = detail.message ?? '';
            this.notify.show    = true;
            this.notify._timer  = setTimeout(() => { this.notify.show = false; }, 3500);
        },
    };
}
</script>
@endpush