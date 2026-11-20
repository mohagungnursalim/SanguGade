<div wire:poll.5s
    class="p-6 text-gray-900 dark:text-gray-100"
    x-data="pedagangManager()"
    x-init="init()"
    @pedagang-saved.window="closeModal()"
    @pedagang-deleted.window="closeDeleteModal()"
    @csv-imported.window="closeImportModal()"
    @notify.window="showNotify($event.detail)"
>
    {{-- Floating Toast Notification --}}
    <div
        x-show="notify.show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        x-cloak
        class="fixed top-5 right-5 z-[100] flex items-center gap-3 px-5 py-3 rounded-xl shadow-lg text-white text-xs font-semibold"
        :class="notify.type === 'success' ? 'bg-green-600' : 'bg-red-600'"
    >
        <i class="fas" :class="notify.type === 'success' ? 'fa-check-circle' : 'fa-times-circle'"></i>
        <span x-text="notify.message"></span>
    </div>

    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-800 dark:text-white">Data Pedagang</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola data pedagang pasar, sarana, tarif retribusi per meter, dan titik kordinat.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            {{-- Tombol Import CSV --}}
            @can('create')
            <button
                type="button"
                @click="openImportModal()"
                class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700 rounded-lg shadow-sm transition"
            >
                <i class="fas fa-file-import text-blue-500"></i> Import CSV
            </button>
            @endcan

            {{-- Tombol Export Excel --}}
            @can('read')
            <button
                type="button"
                wire:click="exportExcel"
                wire:target="exportExcel"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition disabled:opacity-50"
            >
                <i class="fas fa-file-excel" wire:loading.remove wire:target="exportExcel"></i>
                <i class="fas fa-spinner fa-spin" wire:loading wire:target="exportExcel"></i>
                Export Excel
            </button>
            @if ($lastExportFile)
                <button
                    type="button"
                    wire:click="downloadExport('{{ $lastExportFile }}')"
                    wire:target="downloadExport"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-emerald-700 bg-emerald-100 hover:bg-emerald-200 border border-emerald-300 dark:bg-emerald-950/40 dark:text-emerald-300 rounded-lg shadow-sm transition disabled:opacity-50"
                >
                    <i class="fas fa-download"></i> Unduh Hasil Export
                </button>
            @endif
            @endcan

            {{-- Tombol Tambah Pedagang (Client-side Alpine Modal) --}}
            @can('create')
            <button
                type="button"
                @click="openCreateModal()"
                class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition"
            >
                <i class="fas fa-plus text-xs"></i> Tambah Pedagang
            </button>
            @endcan
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-6 flex flex-col sm:flex-row gap-4 items-center justify-between">
        <div class="flex-1 w-full sm:w-auto relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                <i class="fas fa-search"></i>
            </span>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari berdasarkan nama, NIK, jenis sarana, jenis usaha..."
                class="w-full pl-9 pr-4 py-2 text-xs bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-white"
            >
        </div>

        @if(auth()->user()->hasRole('admin'))
        <div class="w-full sm:w-64">
            <select
                wire:model.live="selectedPasarId"
                class="w-full py-2 px-3 text-xs bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-gray-900 dark:text-white"
            >
                <option value="">Semua Lokasi Pasar</option>
                @foreach($pasars as $pasar)
                    <option value="{{ $pasar->id }}">{{ $pasar->nama_pasar }}</option>
                @endforeach
            </select>
        </div>
        @endif
    </div>

    {{-- Tabel Data Pedagang --}}
    @can('read')
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 uppercase font-semibold">
                    <tr>
                        <th class="px-3 py-3 w-10 text-center">No</th>
                        <th class="px-4 py-3">Nama Pedagang</th>
                        <th class="px-4 py-3">NIK</th>
                        <th class="px-4 py-3">Alamat Tinggal</th>
                        <th class="px-3 py-3">Jenis Sarana</th>
                        <th class="px-3 py-3 text-right">Ukuran</th>
                        <th class="px-3 py-3 text-right">Tarif / m²</th>
                        <th class="px-4 py-3 text-right">Total Tarif / Bln</th>
                        <th class="px-3 py-3">Jenis Usaha</th>
                        <th class="px-4 py-3">Titik Kordinat</th>
                        <th class="px-4 py-3">Lokasi Usaha</th>
                        <th class="px-3 py-3">Pasar</th>
                        <th class="px-3 py-3 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($pedagangs as $index => $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                        <td class="px-3 py-3 text-center font-medium text-gray-500">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                            {{ $item->nama }}
                        </td>
                        <td class="px-4 py-3 font-mono text-gray-600 dark:text-gray-300 whitespace-nowrap">
                            {{ $item->nik ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300 min-w-[140px] max-w-xs truncate" title="{{ $item->alamat }}">
                            {{ $item->alamat ?? '-' }}
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">
                                {{ $item->jenis_sarana }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-right font-medium whitespace-nowrap">
                            {{ $item->ukuran ? number_format($item->ukuran, 2, ',', '.') . ' m²' : '-' }}
                        </td>
                        <td class="px-3 py-3 text-right font-medium whitespace-nowrap">
                            {{ $item->tarif_per_meter ? 'Rp ' . number_format($item->tarif_per_meter, 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                            {{ $item->tarif_bulanan ? 'Rp ' . number_format($item->tarif_bulanan, 0, ',', '.') : 'Rp 0' }}
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">
                                {{ $item->jenis_usaha }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-mono text-[11px] whitespace-nowrap">
                            @if($item->latitude && $item->longitude)
                                <a
                                    href="https://www.google.com/maps?q={{ $item->latitude }},{{ $item->longitude }}"
                                    target="_blank"
                                    class="text-blue-600 hover:underline flex items-center gap-1"
                                    title="Buka di Maps"
                                >
                                    <i class="fas fa-map-marker-alt text-red-500"></i>
                                    {{ round($item->latitude, 4) }}, {{ round($item->longitude, 4) }}
                                </a>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300 min-w-[140px] max-w-xs truncate" title="{{ $item->alamat_usaha }}">
                            {{ $item->alamat_usaha ?? '-' }}
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap font-medium text-gray-700 dark:text-gray-300">
                            {{ $item->pasar?->nama_pasar ?? '-' }}
                        </td>
                        <td class="px-3 py-3 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1">
                                {{-- Tombol Edit via Alpine (Zero roundtrip) --}}
                                @can('update')
                                    @if(auth()->user()->hasRole('admin') || auth()->user()->pasar_id === $item->pasar_id)
                                    <button
                                        type="button"
                                        @click="openEditModal(@js($item))"
                                        class="p-1.5 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 rounded hover:bg-blue-50 dark:hover:bg-blue-900/30 transition"
                                        title="Edit Pedagang"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @endif
                                @endcan

                                {{-- Tombol Hapus via Alpine confirmation --}}
                                @can('delete')
                                    @if(auth()->user()->hasRole('admin') || auth()->user()->pasar_id === $item->pasar_id)
                                    <button
                                        type="button"
                                        @click="openDeleteModal(@js($item))"
                                        class="p-1.5 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 rounded hover:bg-red-50 dark:hover:bg-red-900/30 transition"
                                        title="Hapus Pedagang"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="px-4 py-12 text-center text-gray-400 dark:text-gray-500">
                            <i class="fas fa-store-slash text-3xl mb-2 block"></i>
                            Belum ada data pedagang yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="p-8 text-center bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <i class="fas fa-lock text-3xl text-gray-400 mb-2"></i>
        <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">Anda tidak memiliki izin membaca data pedagang.</p>
    </div>
    @endcan

    {{-- =========================================================================
         MODAL CREATE / EDIT PEDAGANG (Client-Side Alpine Modal, Zero Roundtrip)
    ========================================================================== --}}
    <div
        x-show="modalOpen"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog"
        aria-modal="true"
    >
        <div
            x-show="modalOpen"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
            @click="closeModal()"
        ></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div
                x-show="modalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative w-full max-w-2xl bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden transform transition-all my-8"
                @click.stop
            >
                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white" x-text="mode === 'create' ? 'Tambah Data Pedagang' : 'Edit Data Pedagang'"></h3>
                    <button
                        type="button"
                        @click="closeModal()"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1.5 rounded-lg"
                    >
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                {{-- Modal Form --}}
                <form wire:submit="save" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Nama --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Nama Pedagang <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                wire:model="nama"
                                placeholder="Contoh: Budi Santoso"
                                class="w-full px-3 py-2 text-xs bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                            >
                            @error('nama') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- NIK --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">NIK (16 Digit)</label>
                            <input
                                type="text"
                                wire:model="nik"
                                placeholder="Contoh: 7271012345678901"
                                maxlength="20"
                                class="w-full px-3 py-2 text-xs bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                            >
                            @error('nik') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Alamat Tinggal --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Alamat Tinggal</label>
                        <textarea
                            wire:model="alamat"
                            rows="2"
                            placeholder="Alamat domisili tempat tinggal pedagang..."
                            class="w-full px-3 py-2 text-xs bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                        ></textarea>
                        @error('alamat') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Jenis Sarana --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Jenis Sarana <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                wire:model="jenis_sarana"
                                placeholder="Kios / Los / Lapak / Tenda"
                                class="w-full px-3 py-2 text-xs bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                            >
                            @error('jenis_sarana') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Jenis Usaha --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Jenis Usaha <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                wire:model="jenis_usaha"
                                placeholder="Sembako / Sayur / Ikan / Daging / Pakaian"
                                class="w-full px-3 py-2 text-xs bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                            >
                            @error('jenis_usaha') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Ukuran, Tarif/m2, Total Tarif Bulanan --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-gray-50 dark:bg-gray-750 p-3 rounded-xl border border-gray-200 dark:border-gray-700">
                        <span class="col-span-full text-[11px] font-semibold text-red-500 dark:text-red-300 mb-1">*Perhitungan Tarif Retribusi sesuai ketentuan Pasar.</span>

                        </span>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-700 dark:text-gray-300 mb-1">Ukuran (m²)</label>
                            <input
                                type="number"
                                step="0.01"
                                wire:model.live.debounce.300ms="ukuran"
                                wire:change="calculateTarifBulanan"
                                placeholder="Contoh: 6.00"
                                class="w-full px-3 py-1.5 text-xs bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                            >
                            @error('ukuran') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-gray-700 dark:text-gray-300 mb-1">Tarif per meter (m²)</label>
                            <input
                                type="number"
                                step="100"
                                wire:model.live.debounce.300ms="tarif_per_meter"
                                wire:change="calculateTarifBulanan"
                                placeholder="Contoh: 25000"
                                class="w-full px-3 py-1.5 text-xs bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                            >
                            @error('tarif_per_meter') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-gray-700 dark:text-gray-300 mb-1">Total Tarif Ditagih (Rp)</label>
                            <input
                                type="number"
                                step="100"
                                wire:model="tarif_bulanan"
                                placeholder="Otomatis terhitung"
                                class="w-full px-3 py-1.5 text-xs bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-bold text-emerald-600 dark:text-emerald-400 focus:ring-blue-500 focus:border-blue-500"
                            >
                            @error('tarif_bulanan') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Titik Kordinat (Lat & Long) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Latitude</label>
                            <input
                                type="text"
                                wire:model="latitude"
                                placeholder="Contoh: -0.891234"
                                class="w-full px-3 py-2 text-xs bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                            >
                            @error('latitude') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Longitude</label>
                            <input
                                type="text"
                                wire:model="longitude"
                                placeholder="Contoh: 119.876543"
                                class="w-full px-3 py-2 text-xs bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                            >
                            @error('longitude') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Lokasi Usaha --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Lokasi Usaha (Kios/Lapak/Los)</label>
                        <textarea
                            wire:model="alamat_usaha"
                            rows="2"
                            placeholder="Contoh: Blok A No. 12, Pintu Timur Pasar..."
                            class="w-full px-3 py-2 text-xs bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                        ></textarea>
                        @error('alamat_usaha') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Pasar Selection (Hanya Admin yang bisa ubah, Operator terkunci ke pasarnya) --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Lokasi Pasar <span class="text-red-500">*</span></label>
                        @if(auth()->user()->hasRole('admin'))
                        <select
                            wire:model="pasar_id"
                            class="w-full px-3 py-2 text-xs bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">Pilih Pasar</option>
                            @foreach($pasars as $p)
                                <option value="{{ $p->id }}">{{ $p->nama_pasar }}</option>
                            @endforeach
                        </select>
                        @else
                        <input
                            type="text"
                            readonly
                            value="{{ auth()->user()->pasar?->nama_pasar ?? 'Pasar Terdaftar' }}"
                            class="w-full px-3 py-2 text-xs bg-gray-100 dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg text-gray-700 dark:text-gray-200 cursor-not-allowed"
                        >
                        @endif
                        @error('pasar_id') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Footer Buttons --}}
                    <div class="flex items-center justify-center gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button
                            type="button"
                            @click="closeModal()"
                            class="px-4 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 transition"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            wire:target="save"
                            wire:loading.attr="disabled"
                            class="px-5 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition disabled:opacity-50">
                            <span wire:loading.remove wire:target="save">Simpan Data</span>
                            <span wire:loading wire:target="save"><i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- =========================================================================
         MODAL IMPORT CSV
    ========================================================================== --}}
    <div
        x-show="importModalOpen"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog"
        aria-modal="true"
    >
        <div
            x-show="importModalOpen"
            class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
            @click="closeImportModal()"
        ></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div
                class="relative w-full max-w-lg bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden transform transition-all my-8"
                @click.stop
            >
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Import Data Pedagang (.CSV)</h3>
                    <button
                        type="button"
                        @click="closeImportModal()"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1.5 rounded-lg"
                    >
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div class="p-3 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 rounded-xl text-xs text-blue-800 dark:text-blue-300">
                        <div class="flex items-center justify-between font-semibold mb-1">
                            <span><i class="fas fa-info-circle mr-1"></i> Format Kolom CSV:</span>
                            <button
                                type="button"
                                wire:click="downloadTemplateCsv"
                                class="text-blue-600 hover:underline font-bold"
                            >
                                <i class="fas fa-download mr-1"></i> Unduh Format CSV
                            </button>
                        </div>
                        <p class="text-[11px] text-gray-600 dark:text-gray-400">
                            Nama, NIK, Alamat Tinggal, Jenis Sarana, Ukuran, Tarif per meter (m2), Total tarif bulanan (Rp), Jenis Usaha, Titik Kordinat (lat, lng), Alamat Usaha, Pasar
                        </p>
                        <p class="text-[10px] text-blue-600 dark:text-blue-400 mt-1 font-medium">
                            <i class="fas fa-bolt mr-1"></i> File akan diproses di background Queue (aman untuk ribuan data sekaligus).
                        </p>
                    </div>

                    <form wire:submit="importCsv" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Pilih File CSV</label>
                            <input
                                type="file"
                                wire:model="csvFile"
                                accept=".csv,text/csv"
                                class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700"
                            >
                            @error('csvFile') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <button
                                type="button"
                                @click="closeImportModal()"
                                class="px-4 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 transition"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                wire:target="importCsv,csvFile"
                                wire:loading.attr="disabled"
                                class="px-5 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition disabled:opacity-50"
                            >
                                <span wire:loading.remove wire:target="importCsv">Mulai Import</span>
                                <span wire:loading wire:target="importCsv"><i class="fas fa-spinner fa-spin mr-1"></i> Memproses CSV...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================================================================
         MODAL KONFIRMASI DELETE (Alpine Modal)
    ========================================================================== --}}
    <div
        x-show="deleteModalOpen"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog"
        aria-modal="true"
    >
        <div
            x-show="deleteModalOpen"
            class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
            @click="closeDeleteModal()"
        ></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div
                class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 p-6 text-center transform transition-all"
                @click.stop
            >
                <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center mx-auto mb-4 text-xl">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">Hapus Data Pedagang?</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">
                    Apakah Anda yakin ingin menghapus pedagang <span class="font-bold text-gray-900 dark:text-white" x-text="targetPedagang?.nama"></span>? Data yang dihapus tidak dapat dipulihkan.
                </p>

                <div class="flex items-center justify-center gap-3">
                    <button
                        type="button"
                        @click="closeDeleteModal()"
                        class="px-4 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 transition"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="delete"
                        wire:target="delete"
                        wire:loading.attr="disabled"
                        class="px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                        <span wire:loading wire:target="delete"><i class="fas fa-spinner fa-spin mr-1"></i> Menghapus...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<style>
    [x-data="pedagangManager()"] input::placeholder,
    [x-data="pedagangManager()"] textarea::placeholder {
        color: #9ca3af;
        opacity: 1;
    }
</style>
<script>
function pedagangManager() {
    return {
        modalOpen: false,
        deleteModalOpen: false,
        importModalOpen: false,
        mode: 'create', // 'create' | 'edit'
        targetPedagang: null,
        notify: {
            show: false,
            type: 'success',
            message: '',
            _timer: null,
        },

        init() {
            // ready
        },

        openCreateModal() {
            this.mode = 'create';
            this.targetPedagang = null;
            this.$wire.resetForm();
            this.modalOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        openEditModal(item) {
            this.mode = 'edit';
            this.targetPedagang = item;

            // Set ke Livewire component via Alpine client-side tanpa roundtrip server
            this.$wire.set('pedagangId', item.id);
            this.$wire.set('nama', item.nama || '');
            this.$wire.set('nik', item.nik || '');
            this.$wire.set('alamat', item.alamat || '');
            this.$wire.set('jenis_sarana', item.jenis_sarana || '');
            this.$wire.set('ukuran', item.ukuran || '');
            this.$wire.set('tarif_per_meter', item.tarif_per_meter || '');
            this.$wire.set('tarif_bulanan', item.tarif_bulanan || '');
            this.$wire.set('jenis_usaha', item.jenis_usaha || '');
            this.$wire.set('latitude', item.latitude || '');
            this.$wire.set('longitude', item.longitude || '');
            this.$wire.set('alamat_usaha', item.alamat_usaha || '');
            this.$wire.set('pasar_id', item.pasar_id || '');

            this.modalOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        closeModal() {
            this.modalOpen = false;
            document.body.classList.remove('overflow-hidden');
        },

        openDeleteModal(item) {
            this.targetPedagang = item;
            this.$wire.set('deleteId', item.id);
            this.$wire.set('deleteName', item.nama || '');
            this.deleteModalOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        closeDeleteModal() {
            this.deleteModalOpen = false;
            this.targetPedagang = null;
            document.body.classList.remove('overflow-hidden');
        },

        openImportModal() {
            this.importModalOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        closeImportModal() {
            this.importModalOpen = false;
            document.body.classList.remove('overflow-hidden');
        },

        showNotify(detail) {
            clearTimeout(this.notify._timer);
            const payload = Array.isArray(detail) ? (detail[0] || {}) : (detail || {});
            this.notify.type = payload.type ?? 'success';
            this.notify.message = payload.message ?? '';
            this.notify.show = true;
            this.notify._timer = setTimeout(() => { this.notify.show = false; }, 3500);
        }
    };
}
</script>
@endpush
