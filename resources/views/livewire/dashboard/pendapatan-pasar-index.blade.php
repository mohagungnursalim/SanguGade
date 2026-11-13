<div
    class="px-5 py-5 md:px-7 text-gray-900 dark:text-gray-100"
    x-data="ssrdManager()"
    x-init="init()"
    @ssrd-saved.window="closeModal()"
    @ssrd-updated.window="closeModal()"
    @notify.window="showNotify($event.detail)"
>

    {{-- =====================================================================
         NOTIFIKASI FLOATING
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
         HEADER HALAMAN & STATISTIK KARTU
    ====================================================================== --}}
    <header class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">Pendapatan Pasar</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Surat Setoran Retribusi Daerah (SSRD) - Monitoring dan Pencatatan Pembayaran Retribusi Pasar.</p>
        </div>

        <div class="flex items-center gap-2">
            <button
                type="button"
                @click="tab = tab === 'ringkasan' ? 'transaksi' : 'ringkasan'"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium border-b border-transparent hover:border-gray-400 text-gray-600 dark:text-gray-300 transition"
            >
                <i class="fas text-xs" :class="tab === 'ringkasan' ? 'fa-list' : 'fa-chart-pie'"></i>
                <span x-text="tab === 'ringkasan' ? 'Lihat tabel' : 'Ringkasan pasar'"></span>
            </button>

            <button
                type="button"
                @click="openCreateModal()"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white rounded-md transition"
            >
                <i class="fas fa-plus text-xs"></i>
                Tambah SSRD
            </button>
        </div>
    </header>

    {{-- ─── 4 KARTU STATISTIK ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-gray-200 dark:divide-gray-700 mb-5 pb-5 border-b border-gray-200 dark:border-gray-700">
        {{-- Total Pendapatan Seluruhnya --}}
        <div class="px-4 py-2 first:pl-0">
            <div>
                <span class="text-xs text-gray-500 dark:text-gray-400">Total Akumulasi (Semua Pasar)</span>
                <p class="text-lg font-bold text-gray-900 dark:text-white">
                    Rp {{ number_format($totalNominalAll, 0, ',', '.') }}
                </p>
                <span class="text-[11px] text-gray-400">{{ $totalTransaksiAll }} Bukti Setoran</span>
            </div>
        </div>

        {{-- Total Hasil Filter --}}
        <div class="px-4 py-2">
            <div>
                <span class="text-xs text-gray-500 dark:text-gray-400">Hasil Filter Terpilih @if($namaPasarTerpilih)
                - {{ $namaPasarTerpilih }}</span>
            @else
                (Semua Pasar)
            @endif
                <p class="text-lg font-bold text-blue-600 dark:text-blue-400">
                    Rp {{ number_format($totalNominalFiltered, 0, ',', '.') }}
                </p>
                <span class="text-[11px] text-gray-400">{{ $totalTransaksiFiltered }} Transaksi SSRD</span>
            </div>
        </div>

        {{-- Pasar Terdaftar --}}
        <div class="px-4 py-2">
            <div>
                <span class="text-xs text-gray-500 dark:text-gray-400">Total Pasar</span>
                <p class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ $pasars->count() }} Pasar
                </p>
                <span class="text-[11px] text-gray-400">Terdaftar di sistem</span>
            </div>
        </div>

        {{-- Rata-rata per Setoran --}}
        <div class="px-4 py-2">
            <div>
                <span class="text-xs text-gray-500 dark:text-gray-400">Rata-rata Setoran</span>
                <p class="text-lg font-bold text-green-600 dark:text-white">
                    Rp {{ $totalTransaksiFiltered > 0 ? number_format($totalNominalFiltered / $totalTransaksiFiltered, 0, ',', '.') : '0' }}
                </p>
                <span class="text-[11px] text-gray-400">Pada filter aktif</span>
            </div>
        </div>
    </div>

    {{-- =====================================================================
         VIEW: TABEL RINGKASAN PER PASAR (TOGGLE)
    ====================================================================== --}}
    <div x-show="tab === 'ringkasan'" x-cloak class="mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-chart-column text-emerald-600"></i>
                        Rekapitulasi Total Pendapatan Retribusi Berdasarkan Pasar
                    </h2>
                    <p class="text-xs text-gray-500">Klik tombol detail pada masing-masing pasar untuk melihat riwayat penyetoran lengkap.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left text-gray-700 dark:text-gray-300">
                    <thead class="bg-gray-100 dark:bg-gray-700 uppercase font-semibold text-gray-600 dark:text-gray-300">
                        <tr>
                            <th class="px-3 py-2.5">No</th>
                            <th class="px-3 py-2.5">Nama Pasar</th>
                            <th class="px-3 py-2.5">Kecamatan</th>
                            <th class="px-3 py-2.5 text-center">Jumlah SSRD</th>
                            <th class="px-3 py-2.5 text-right">Total Pendapatan (Rp)</th>
                            <th class="px-3 py-2.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($ringkasanPerPasar as $idx => $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition">
                            <td class="px-3 py-2.5">{{ $idx + 1 }}</td>
                            <td class="px-3 py-2.5 font-medium text-gray-900 dark:text-white">
                                {{ $item->nama_pasar }}
                            </td>
                            <td class="px-3 py-2.5 text-gray-500">{{ $item->kecamatan }}</td>
                            <td class="px-3 py-2.5 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                    {{ $item->pendapatan_pasars_count }} Transaksi
                                </span>
                            </td>
                            <td class="px-3 py-2.5 text-right font-bold text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format($item->pendapatan_pasars_sum_jumlah_retribusi ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-2.5 text-center">
                                <button
                                    type="button"
                                    wire:click="viewPasarDetail({{ $item->id }})"
                                    class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800 transition"
                                >
                                    <i class="fas fa-eye text-[11px]"></i> Detail Riwayat
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada data pasar.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- =====================================================================
         PANEL FILTER & PENCARIAN
    ====================================================================== --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
            {{-- Search Text --}}
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">
                    Cari Nomor SSRD / Rekening / Penyetor
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </div>
                    <input
                        type="text"
                        wire:model.live.debounce.350ms="search"
                        placeholder="Ketik kata kunci pencarian..."
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg
                               focus:ring-emerald-500 focus:border-emerald-500
                               block w-full pl-9 pr-3 py-2
                               dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                    />
                </div>
            </div>

            {{-- Filter Pasar --}}
            <div>
                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Pasar</label>
                <select
                    wire:model.live="filterPasar"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg
                           focus:ring-emerald-500 focus:border-emerald-500 block w-full py-2 px-2.5
                           dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                >
                    <option value="">Semua Pasar</option>
                    @foreach ($pasars as $p)
                        <option value="{{ $p->id }}">{{ $p->nama_pasar }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Tahun --}}
            <div>
                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Tahun Retribusi</label>
                <select
                    wire:model.live="filterTahun"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg
                           focus:ring-emerald-500 focus:border-emerald-500 block w-full py-2 px-2.5
                           dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                >
                    <option value="">Semua Tahun</option>
                    @foreach ($tahunList as $th)
                        <option value="{{ $th }}">{{ $th }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Tanggal Mulai --}}
            <div>
                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Dari Tgl Setor</label>
                <input
                    type="date"
                    wire:model.live="filterStartDate"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg
                           focus:ring-emerald-500 focus:border-emerald-500 block w-full py-2 px-2.5
                           dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                />
            </div>

            {{-- Filter Tanggal Akhir --}}
            <div>
                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">Sampai Tgl Setor</label>
                <input
                    type="date"
                    wire:model.live="filterEndDate"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg
                           focus:ring-emerald-500 focus:border-emerald-500 block w-full py-2 px-2.5
                           dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                />
            </div>
        </div>

        {{-- Baris Filter Jenis & Reset --}}
        <div class="flex flex-wrap items-center justify-between gap-2 mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500 font-medium">Jenis Retribusi:</span>
                <div class="flex flex-wrap gap-1">
                    @foreach (['SKRD', 'SKRDT', 'SKRDB', 'SKRDKBT', 'STRD', 'STPRD', 'SK Pembetulan', 'SK Keberatan', 'Lain-lain'] as $jns)
                        <button
                            type="button"
                            wire:click="$set('filterJenis', '{{ $filterJenis === $jns ? '' : $jns }}')"
                            class="px-2 py-1 text-[11px] rounded font-medium border transition {{ $filterJenis === $jns ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:bg-gray-200' }}"
                        >
                            {{ $jns }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if ($search || $filterPasar || $filterTahun || $filterStartDate || $filterEndDate || $filterJenis)
                    <button
                        type="button"
                        wire:click="resetFilters" wire:loading.remove wire:target="search, filterPasar, filterTahun, filterStartDate, filterEndDate, filterJenis" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-1 px-3 py-1 text-xs text-red-600 hover:text-red-700 dark:text-red-400 font-medium transition">
                        <i class="fas fa-rotate-left"></i> Reset Filter
                    </button>
                @endif
                <div wire:loading class="text-xs text-emerald-600 dark:text-emerald-400 flex items-center gap-1 font-medium">
                    <i class="fas fa-spinner fa-spin"></i> Memuat data...
                </div>
            </div>
        </div>
    </div>

    {{-- =====================================================================
         TABEL DATA SSRD
    ====================================================================== --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left text-gray-700 dark:text-gray-300 border-collapse">
                <thead class="bg-gray-800 dark:bg-gray-900 text-white text-[11px] uppercase tracking-wider">
                    <tr>
                        <th class="px-3 py-3 text-center w-12 border-r border-gray-700">No</th>
                        <th class="px-3 py-3 border-r border-gray-700">Pasar</th>
                        <th class="px-3 py-3 border-r border-gray-700">No. SSRD</th>
                        <th class="px-3 py-3 border-r border-gray-700">No. Rekening</th>
                        <th class="px-3 py-3 border-r border-gray-700">Masa / Thn Retribusi</th>
                        <th class="px-3 py-3 border-r border-gray-700">Jenis & Ayat</th>
                        <th class="px-3 py-3 border-r border-gray-700 text-right">Jumlah Setor (Rp)</th>
                        <th class="px-3 py-3 border-r border-gray-700">Tgl Setor</th>
                        <th class="px-3 py-3 border-r border-gray-700">Penyetor / Petugas Bank</th>
                        <th class="px-3 py-3 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($ssrdList as $index => $item)
                        <tr class="hover:bg-emerald-50/40 dark:hover:bg-gray-900 transition">
                            {{-- No --}}
                            <td class="px-3 py-3 text-center text-gray-400 font-mono">
                                {{ $ssrdList->firstItem() + $index }}
                            </td>

                            {{-- Pasar --}}
                            <td class="px-3 py-3 font-semibold text-gray-900 dark:text-white">
                                <div class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-shop text-emerald-600 text-xs"></i>
                                    <span>{{ $item->pasar->nama_pasar ?? '-' }}</span>
                                </div>
                                <span class="text-[10px] text-gray-400 font-normal">Kec. {{ $item->pasar->kecamatan ?? '-' }}</span>
                            </td>

                            {{-- No SSRD & Thn SSRD --}}
                            <td class="px-3 py-3">
                                <span class="font-mono font-medium text-gray-900 dark:text-gray-100">
                                    {{ $item->nomor_urut_ssrd ?: '-' }}
                                </span>
                                <div class="text-[10px] text-gray-400">Tahun SSRD: {{ $item->tahun_ssrd }}</div>
                            </td>

                            {{-- No Rekening Bank --}}
                            <td class="px-3 py-3 font-mono text-gray-600 dark:text-gray-400">
                                {{ $item->no_rekening_bank ?: '-' }}
                            </td>

                            {{-- Masa & Tahun Retribusi --}}
                            <td class="px-3 py-3">
                                <div class="font-medium text-gray-800 dark:text-gray-200">
                                    {{ $item->masa_retribusi ?: '-' }}
                                </div>
                                <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold">
                                    Tahun {{ $item->tahun_retribusi }}
                                </span>
                            </td>

                            {{-- Jenis & Ayat --}}
                            <td class="px-3 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    {{ $item->jenis_retribusi }}
                                </span>
                                @if ($item->nomor_ayat)
                                    <div class="text-[10px] text-gray-400 mt-0.5">Ayat: {{ $item->nomor_ayat }}</div>
                                @endif
                            </td>

                            {{-- Jumlah Retribusi --}}
                            <td class="px-3 py-3 text-right">
                                <span class="font-bold text-gray-900 dark:text-emerald-400 text-xs">
                                    Rp {{ number_format($item->jumlah_retribusi, 0, ',', '.') }}
                                </span>
                                <div class="text-[9px] text-gray-400 max-w-[150px] truncate ml-auto" title="{{ $item->terbilang }}">
                                    {{ $item->terbilang }}
                                </div>
                            </td>

                            {{-- Tanggal Penyetoran --}}
                            <td class="px-3 py-3 whitespace-nowrap">
                                <div class="font-medium text-gray-800 dark:text-gray-200">
                                    {{ $item->tanggal_penyetoran ? $item->tanggal_penyetoran->format('d/m/Y') : '-' }}
                                </div>
                            </td>

                            {{-- Penyetor & Petugas Bank --}}
                            <td class="px-3 py-3">
                                <div class="font-medium text-gray-900 dark:text-white flex items-center gap-1">
                                    <i class="fa-solid fa-user text-[10px] text-gray-400"></i>
                                    {{ $item->nama_penyetor }}
                                </div>
                                @if ($item->petugas_bank)
                                    <div class="text-[10px] text-gray-400 flex items-center gap-1 mt-0.5">
                                        <i class="fa-solid fa-building-columns text-[9px]"></i>
                                        Bank: {{ $item->petugas_bank }}
                                    </div>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-3 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    {{-- Tombol Detail Quick View --}}
                                    <button
                                        type="button"
                                        @click="viewDetail(@js($item))"
                                        class="inline-flex items-center justify-center w-7 h-7 rounded bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 transition"
                                        title="Detail Formulir SSRD"
                                    >
                                        <i class="fas fa-file-lines text-xs"></i>
                                    </button>

                                    {{-- Tombol Edit --}}
                                    <button
                                        type="button"
                                        @click="openEditModal(@js($item))"
                                        class="inline-flex items-center justify-center w-7 h-7 rounded bg-amber-50 text-amber-600 hover:bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400 transition"
                                        title="Edit Data SSRD"
                                    >
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>

                                    {{-- Tombol Hapus --}}
                                    <button
                                        type="button"
                                        wire:click="confirmDelete({{ $item->id }})"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center justify-center w-7 h-7 rounded bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 transition"
                                        title="Hapus Data"
                                    >
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="fa-solid fa-receipt text-4xl text-gray-300 dark:text-gray-600"></i>
                                    <p class="font-medium">Tidak ada data pendapatan SSRD yang sesuai.</p>
                                    <p class="text-[11px] text-gray-400">Silakan sesuaikan filter atau klik tombol "Tambah SSRD" untuk mencatat setoran baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if ($ssrdList->isNotEmpty())
                <tfoot class="bg-gray-100 dark:bg-gray-900 font-bold text-xs text-gray-800 dark:text-gray-200">
                    <tr>
                        <td colspan="6" class="px-3 py-2.5 text-right uppercase tracking-wider border-t border-gray-300 dark:border-gray-700">
                            Subtotal Halaman Ini:
                        </td>
                        <td class="px-3 py-2.5 text-right text-emerald-600 dark:text-emerald-400 border-t border-gray-300 dark:border-gray-700">
                            Rp {{ number_format($ssrdList->sum('jumlah_retribusi'), 0, ',', '.') }}
                        </td>
                        <td colspan="3" class="px-3 py-2.5 border-t border-gray-300 dark:border-gray-700"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        {{-- Pagination --}}
        @if ($ssrdList->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                {{ $ssrdList->links() }}
            </div>
        @endif
    </div>

    {{-- =====================================================================
         MODAL REUSABLE ALPINE: TAMBAH & EDIT SSRD
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
        class="fixed inset-0 z-50 flex items-start justify-center bg-black/60 backdrop-blur-sm overflow-y-auto py-8 px-4"
    >
        <div
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="w-full max-w-4xl bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            {{-- Modal Header: Mirip Kop Formulir SSRD --}}
            <div class="flex items-center justify-between px-6 py-4"
                 :class="mode === 'create' ? 'bg-teal-800 text-white' : 'bg-amber-600 text-white'">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-lg">
                        <i class="fas" :class="mode === 'create' ? 'fa-file-circle-plus' : 'fa-file-pen'"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold tracking-tight"
                            x-text="mode === 'create' ? 'SURAT SETORAN RETRIBUSI DAERAH (SSRD) - Buat Baru' : 'EDIT FORMULIR SSRD'">
                        </h2>
                        <p class="text-xs text-white/80">Monitoring Pendapatan Pasar</p>
                    </div>
                </div>
                <button type="button" @click="closeModal()" class="text-white/80 hover:text-white transition text-lg">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- ─── FORM CREATE ──────────────────────────────────────────── --}}
            <div x-show="mode === 'create'">
                <form wire:submit.prevent="store" class="p-6 space-y-5">
                    {{-- 1. IDENTITAS SSRD & PASAR --}}
                    <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-store"></i> 1. Pasar & Identitas Rekening
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            {{-- Pilih Pasar --}}
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Pasar <span class="text-red-500">*</span>
                                </label>
                                <select
                                    wire:model.defer="pasar_id"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500"
                                >
                                    <option value="">-- Pilih Pasar --</option>
                                    @foreach ($pasars as $p)
                                        <option value="{{ $p->id }}">{{ $p->nama_pasar }} (Kec. {{ $p->kecamatan }})</option>
                                    @endforeach
                                </select>
                                @error('pasar_id') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Tahun SSRD --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Tahun SSRD <span class="text-red-500">*</span>
                                </label>
                                <input
                                    wire:model.defer="tahun_ssrd"
                                    type="number"
                                    min="1900"
                                    max="{{ date('Y') + 5 }}"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500"
                                />
                                @error('tahun_ssrd') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Nomor Rekening Bank --}}
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Nomor Rekening Bank
                                </label>
                                <input
                                    wire:model.defer="no_rekening_bank"
                                    type="text" value="2501300007827"
                                    placeholder="2501300007827"
                                    disabled class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 disabled:bg-gray-200 disabled:text-gray-500
                                            disabled:cursor-not-allowed
                                            dark:disabled:bg-gray-600 dark:disabled:text-gray-400"
                                />
                                @error('no_rekening_bank') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Nomor Urut SSRD --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Nomor Urut SSRD
                                </label>
                                <input
                                    wire:model.defer="nomor_urut_ssrd"
                                    type="text"
                                    placeholder="Contoh: 0045/SSRD/2026"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500"
                                />
                                @error('nomor_urut_ssrd') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- 2. MASA & JENIS RETRIBUSI --}}
                    <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-calendar-check"></i> 2. Masa, Tahun & Jenis Retribusi
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            {{-- Masa Retribusi --}}
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Masa Retribusi (Bulan / Periode)
                                </label>
                                <input
                                    wire:model.defer="masa_retribusi"
                                    type="text"
                                    placeholder="Contoh: Januari 2026 / Triwulan I"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500"
                                />
                                @error('masa_retribusi') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Tahun Retribusi --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Tahun Retribusi <span class="text-red-500">*</span>
                                </label>
                                <input
                                    wire:model.defer="tahun_retribusi"
                                    type="number"
                                    min="1900"
                                    max="{{ date('Y') + 5 }}"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500"
                                />
                                @error('tahun_retribusi') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Nomor Ayat --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Nomor Ayat Rekening
                                </label>
                                <input
                                        wire:model.defer="nomor_ayat"
                                        type="text"
                                        placeholder="01.02.2002"
                                        disabled
                                        class="w-full px-3 py-2 text-xs border border-gray-300
                                            dark:border-gray-600 rounded-lg
                                            bg-white dark:bg-gray-700
                                            text-gray-900 dark:text-white
                                            disabled:bg-gray-200 disabled:text-gray-500
                                            disabled:cursor-not-allowed
                                            dark:disabled:bg-gray-600 dark:disabled:text-gray-400">
                                @error('nomor_ayat') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Jenis Retribusi --}}
                            <div class="sm:col-span-4">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Jenis Ketetapan Retribusi <span class="text-red-500">*</span>
                                </label>
                                <select
                                    wire:model.defer="jenis_retribusi"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 font-semibold"
                                >
                                    <option value="SKRD">SKRD (Surat Ketetapan Retribusi Daerah)</option>
                                    <option value="SKRDT">SKRDT (Surat Ketetapan Retribusi Daerah Tambahan)</option>
                                    <option value="SKRDB">SKRDB (Surat Ketetapan Retribusi Daerah Lebih Bayar)</option>
                                    <option value="SKRDKBT">SKRDKBT (Surat Ketetapan Retribusi Daerah Kurang Bayar Tambahan)</option>
                                    <option value="STRD">STRD (Surat Tagihan Retribusi Daerah)</option>
                                    <option value="STPRD">STPRD (Surat Tagihan Pembayaran Retribusi Daerah)</option>
                                    <option value="SK Pembetulan">SK Pembetulan</option>
                                    <option value="SK Keberatan">SK Keberatan</option>
                                    <option value="Lain-lain">Lain-lain</option>
                                </select>
                                @error('jenis_retribusi') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- 3. JUMLAH NOMINAL & TERBILANG OTOMATIS --}}
                    <div class="bg-emerald-50/60 dark:bg-emerald-950/20 p-4 rounded-xl border border-emerald-200 dark:border-emerald-800">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-400 mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-money-bill-wave"></i> 3. Jumlah Setoran Retribusi & Terbilang
                        </h3>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-800 dark:text-gray-200 mb-1">
                                    Jumlah Retribusi (Rupiah) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-emerald-700 dark:text-emerald-400 font-bold text-sm">
                                        Rp
                                    </div>
                                    <input
                                        type="number"
                                        min="0"
                                        step="1"
                                        x-model.number="formCreate.jumlah"
                                        @input="onJumlahChange('create')"
                                        placeholder="0"
                                        class="w-full pl-10 pr-4 py-2 text-sm font-bold border border-emerald-300 dark:border-emerald-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500"
                                    />
                                </div>
                                @error('jumlah_retribusi') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Terbilang Box --}}
                            <div class="p-3 bg-white dark:bg-gray-800 rounded-lg border border-emerald-200 dark:border-emerald-800">
                                <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Terbilang:</span>
                                <p class="text-xs font-semibold text-emerald-800 dark:text-emerald-300 italic mt-0.5" x-text="formCreate.terbilang || 'Nol Rupiah'"></p>
                            </div>
                        </div>
                    </div>

                    {{-- 4. INFORMASI PENYETOR & BANK --}}
                    <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-hand-holding-dollar"></i> 4. Informasi Penyetoran & Pengesahan
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            {{-- Tanggal Penyetoran --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Tanggal Penyetoran <span class="text-red-500">*</span>
                                </label>
                                <input
                                    wire:model.defer="tanggal_penyetoran"
                                    type="date"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500"
                                />
                                @error('tanggal_penyetoran') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Nama Penyetor --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Nama Penyetor <span class="text-red-500">*</span>
                                </label>
                                <input
                                    wire:model.defer="nama_penyetor"
                                    type="text"
                                    placeholder="Nama jelas penyetor"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500"
                                />
                                @error('nama_penyetor') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Petugas Bank --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Petugas Bank / Kasir
                                </label>
                                <input
                                    wire:model.defer="petugas_bank"
                                    type="text"
                                    placeholder="Nama teller / petugas bank"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500"
                                />
                                @error('petugas_bank') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Keterangan --}}
                            <div class="sm:col-span-3">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Keterangan Tambahan
                                </label>

                                <select
                                    wire:model.defer="keterangan"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                                    <option value="">--Pilih Keterangan--</option>
                                    <option value="Ret. Harian">Ret. Harian</option>
                                    <option value="Ret. Sewa Tempat Kios">Ret. Sewa Tempat Kios</option>
                                    <option value="Ret. Sewat Tempat Lapak">Ret. Sewa Tempat Lapak</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>

                                @error('keterangan')
                                    <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Footer Form Create --}}
                    <div class="flex justify-center gap-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="closeModal()" class="px-5 py-2 text-xs font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition">
                            Batal
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="store"
                            class="px-6 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow transition disabled:opacity-70 disabled:cursor-not-allowed min-w-[120px]"
                        >
                            <span wire:loading.remove wire:target="store">
                                <i class="fas fa-save mr-1"></i> Simpan SSRD
                            </span>
                            <span wire:loading wire:target="store" class="flex items-center justify-center gap-2">
                                <i class="fas fa-spinner fa-spin"></i> Menyimpan...
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- ─── FORM EDIT ──────────────────────────────────────────────── --}}
            <div x-show="mode === 'edit'">
                <form wire:submit.prevent="update" class="p-6 space-y-5">
                    {{-- 1. IDENTITAS SSRD & PASAR (EDIT) --}}
                    <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-store"></i> 1. Pasar & Identitas Rekening
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Pasar <span class="text-red-500">*</span>
                                </label>
                                <select
                                    wire:model.defer="edit_pasar_id"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500"
                                >
                                    <option value="">-- Pilih Pasar --</option>
                                    @foreach ($pasars as $p)
                                        <option value="{{ $p->id }}">{{ $p->nama_pasar }} (Kec. {{ $p->kecamatan }})</option>
                                    @endforeach
                                </select>
                                @error('edit_pasar_id') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Tahun SSRD <span class="text-red-500">*</span>
                                </label>
                                <input
                                    wire:model.defer="edit_tahun_ssrd"
                                    type="number"
                                    min="1900"
                                    max="{{ date('Y') + 5 }}"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500"
                                />
                                @error('edit_tahun_ssrd') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Nomor Rekening Bank
                                </label>
                                <input
                                    wire:model.defer="edit_no_rekening_bank"
                                    type="text" value="2501300007827"
                                    placeholder="2501300007827"
                                    disabled class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 disabled:bg-gray-200 disabled:text-gray-500
                                            disabled:cursor-not-allowed dark:disabled:bg-gray-600 dark:disabled:text-gray-400"
                                            
                                />
                                @error('edit_no_rekening_bank') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Nomor Urut SSRD
                                </label>
                                <input
                                    wire:model.defer="edit_nomor_urut_ssrd"
                                    type="text"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500"
                                />
                                @error('edit_nomor_urut_ssrd') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- 2. MASA & JENIS RETRIBUSI (EDIT) --}}
                    <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-calendar-check"></i> 2. Masa, Tahun & Jenis Retribusi
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Masa Retribusi (Bulan / Periode)
                                </label>
                                <input
                                    wire:model.defer="edit_masa_retribusi"
                                    type="text"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500"
                                />
                                @error('edit_masa_retribusi') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Tahun Retribusi <span class="text-red-500">*</span>
                                </label>
                                <input
                                    wire:model.defer="edit_tahun_retribusi"
                                    type="number"
                                    min="1900"
                                    max="{{ date('Y') + 5 }}"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500"
                                />
                                @error('edit_tahun_retribusi') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Nomor Ayat Rekening
                                </label>
                                <input
                                    wire:model.defer="edit_nomor_ayat"
                                    type="text"
                                   value="01.02.2002"
                                    placeholder="01.02.2002"
                                    disabled class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 disabled:bg-gray-200 disabled:text-gray-500
                                            disabled:cursor-not-allowed
                                            dark:disabled:bg-gray-600 dark:disabled:text-gray-400"
                                />
                                @error('edit_nomor_ayat') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="sm:col-span-4">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Jenis Ketetapan Retribusi <span class="text-red-500">*</span>
                                </label>
                                <select
                                    wire:model.defer="edit_jenis_retribusi"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500 font-semibold"
                                >
                                    <option value="SKRD">SKRD (Surat Ketetapan Retribusi Daerah)</option>
                                    <option value="SKRDT">SKRDT (Surat Ketetapan Retribusi Daerah Tambahan)</option>
                                    <option value="SKRDB">SKRDB (Surat Ketetapan Retribusi Daerah Lebih Bayar)</option>
                                    <option value="SKRDKBT">SKRDKBT (Surat Ketetapan Retribusi Daerah Kurang Bayar Tambahan)</option>
                                    <option value="STRD">STRD (Surat Tagihan Retribusi Daerah)</option>
                                    <option value="STPRD">STPRD (Surat Tagihan Pembayaran Retribusi Daerah)</option>
                                    <option value="SK Pembetulan">SK Pembetulan</option>
                                    <option value="SK Keberatan">SK Keberatan</option>
                                    <option value="Lain-lain">Lain-lain</option>
                                </select>
                                @error('edit_jenis_retribusi') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- 3. JUMLAH NOMINAL & TERBILANG (EDIT) --}}
                    <div class="bg-amber-50/60 dark:bg-amber-950/20 p-4 rounded-xl border border-amber-200 dark:border-amber-800">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-400 mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-money-bill-wave"></i> 3. Jumlah Setoran Retribusi & Terbilang
                        </h3>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-800 dark:text-gray-200 mb-1">
                                    Jumlah Retribusi (Rupiah) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-amber-700 dark:text-amber-400 font-bold text-sm">
                                        Rp
                                    </div>
                                    <input
                                        type="number"
                                        min="0"
                                        step="1"
                                        x-model.number="formEdit.jumlah"
                                        @input="onJumlahChange('edit')"
                                        placeholder="0"
                                        class="w-full pl-10 pr-4 py-2 text-sm font-bold border border-amber-300 dark:border-amber-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500"
                                    />
                                </div>
                                @error('edit_jumlah_retribusi') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="p-3 bg-white dark:bg-gray-800 rounded-lg border border-amber-200 dark:border-amber-800">
                                <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Terbilang:</span>
                                <p class="text-xs font-semibold text-amber-800 dark:text-amber-300 italic mt-0.5" x-text="formEdit.terbilang || 'Nol Rupiah'"></p>
                            </div>
                        </div>
                    </div>

                    {{-- 4. INFORMASI PENYETOR & BANK (EDIT) --}}
                    <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-hand-holding-dollar"></i> 4. Informasi Penyetoran & Pengesahan
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Tanggal Penyetoran <span class="text-red-500">*</span>
                                </label>
                                <input
                                    wire:model.defer="edit_tanggal_penyetoran"
                                    type="date"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500"
                                />
                                @error('edit_tanggal_penyetoran') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Nama Penyetor <span class="text-red-500">*</span>
                                </label>
                                <input
                                    wire:model.defer="edit_nama_penyetor"
                                    type="text"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500"
                                />
                                @error('edit_nama_penyetor') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Petugas Bank / Kasir
                                </label>
                                <input
                                    wire:model.defer="edit_petugas_bank"
                                    type="text"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500"
                                />
                                @error('edit_petugas_bank') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>

                           <div class="sm:col-span-3">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Keterangan Tambahan
                                </label>

                                <select
                                    wire:model.defer="edit_keterangan"
                                    class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500">
                                    <option value="">--Pilih Keterangan--</option>
                                    <option value="Ret. Harian">Ret. Harian</option>
                                    <option value="Ret. Sewa Tempat Kios">Ret. Sewa Tempat Kios</option>
                                    <option value="Ret. Sewat Tempat Lapak">Ret. Sewat Tempat Lapak</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>

                                @error('edit_keterangan')
                                    <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Footer Form Edit --}}
                    <div class="flex justify-center gap-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="closeModal()" class="px-5 py-2 text-xs font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition">
                            Batal
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="update"
                            class="px-6 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-lg shadow transition disabled:opacity-70 disabled:cursor-not-allowed min-w-[120px]"
                        >
                            <span wire:loading.remove wire:target="update">
                                <i class="fas fa-save mr-1"></i> Perbarui SSRD
                            </span>
                            <span wire:loading wire:target="update" class="flex items-center justify-center gap-2">
                                <i class="fas fa-spinner fa-spin"></i> Memperbarui...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- =====================================================================
         MODAL DETAIL QUICK-VIEW FORMULIR SSRD (MURNI CLIENT-SIDE ALPINE)
    ====================================================================== --}}
    <div
        x-show="detailItem !== null"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
    >
        <div
            class="w-full max-w-2xl bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            {{-- Header SSRD Receipt --}}
            <div class="bg-white dark:bg-gray-800 text-white p-5 border-b border-gray-700 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-mono tracking-widest text-emerald-400 uppercase font-semibold">Salinan Tanda Bukti Penyetoran</span>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">SURAT SETORAN RETRIBUSI DAERAH (SSRD)</h3>
                </div>
                <button type="button" @click="detailItem = null" class="text-gray-600 hover:text-gray-400 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <template x-if="detailItem">
                <div class="p-6 space-y-4 text-xs text-gray-800 dark:text-gray-200">
                    <div class="grid grid-cols-2 gap-4 pb-4 border-b border-gray-100 dark:border-gray-700">
                        <div>
                            <span class="text-gray-400 text-[10px] block uppercase font-bold">Pasar:</span>
                            <span class="font-bold text-sm text-gray-900 dark:text-white" x-text="detailItem.pasar ? detailItem.pasar.nama_pasar : '-'"></span>
                            <p class="text-gray-500 text-[11px]" x-text="'Kecamatan: ' + (detailItem.pasar ? detailItem.pasar.kecamatan : '-')"></p>
                        </div>
                        <div class="text-right">
                            <span class="text-gray-400 text-[10px] block uppercase font-bold">Nomor SSRD:</span>
                            <span class="font-mono font-bold text-sm text-emerald-600 dark:text-emerald-400" x-text="detailItem.nomor_urut_ssrd || '-'"></span>
                            <p class="text-gray-500 text-[11px]" x-text="'Tahun SSRD: ' + detailItem.tahun_ssrd"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div>
                            <span class="text-gray-400 text-[10px] block uppercase">No. Rekening Bank:</span>
                            <span class="font-mono font-medium" x-text="detailItem.no_rekening_bank || '-'"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[10px] block uppercase">Masa Retribusi:</span>
                            <span class="font-medium" x-text="detailItem.masa_retribusi || '-'"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[10px] block uppercase">Tahun Retribusi:</span>
                            <span class="font-medium" x-text="detailItem.tahun_retribusi || '-'"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[10px] block uppercase">Jenis Retribusi:</span>
                            <span class="font-bold text-emerald-700 dark:text-emerald-400" x-text="detailItem.jenis_retribusi"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[10px] block uppercase">Nomor Ayat:</span>
                            <span class="font-mono" x-text="detailItem.nomor_ayat || '-'"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 text-[10px] block uppercase">Tanggal Penyetoran:</span>
                            <span class="font-medium" x-text="formatDate(detailItem.tanggal_penyetoran)"></span>
                        </div>
                    </div>

                    {{-- Kotak Jumlah Nominal --}}
                    <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl border border-emerald-200 dark:border-emerald-800">
                        <span class="text-[10px] font-bold text-emerald-800 dark:text-emerald-400 uppercase tracking-wider block">Jumlah Setoran Retribusi:</span>
                        <div class="text-xl font-black text-emerald-700 dark:text-emerald-300 mt-1" x-text="formatRupiah(detailItem.jumlah_retribusi)"></div>
                        <div class="mt-2 text-xs italic text-gray-700 dark:text-gray-300">
                            Terbilang: <span class="font-semibold text-emerald-800 dark:text-emerald-200" x-text="detailItem.terbilang"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <div class="p-3 bg-white dark:bg-gray-800 rounded-lg">
                            <span class="text-gray-400 text-[10px] block uppercase font-bold">Penyetor:</span>
                            <span class="font-semibold text-gray-900 dark:text-white" x-text="detailItem.nama_penyetor"></span>
                        </div>
                        <div class="p-3 bg-white dark:bg-gray-800 rounded-lg">
                            <span class="text-gray-400 text-[10px] block uppercase font-bold">Petugas / Teller Bank:</span>
                            <span class="font-semibold text-gray-900 dark:text-white" x-text="detailItem.petugas_bank || '-'"></span>
                        </div>
                    </div>

                    <template x-if="detailItem.keterangan">
                        <div class="text-gray-500 italic text-[11px] pt-1">
                            * Keterangan: <span x-text="detailItem.keterangan"></span>
                        </div>
                    </template>
                </div>
            </template>

            <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900 flex justify-center">
                <button type="button" @click="detailItem = null" class="px-4 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-200 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 rounded-lg transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- =====================================================================
         MODAL DETAIL PASAR & RIWAYAT SSRD (DETAIL BERDASARKAN PASAR)
    ====================================================================== --}}
    @if ($showDetailModal && $detailPasar)
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="w-full max-w-4xl bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden my-6">
            {{-- Header --}}
            <div class="bg-teal-900 text-white p-5 flex items-center justify-between">
                <div>
                    <span class="text-xs text-emerald-300 font-medium">Rekapitulasi Riwayat Setoran Retribusi Pasar</span>
                    <h2 class="text-lg font-bold">{{ $detailPasar->nama_pasar }}</h2>
                    <p class="text-xs text-white/80">Kecamatan {{ $detailPasar->kecamatan }} | Kelurahan {{ $detailPasar->kelurahan }}</p>
                </div>
                <button type="button" wire:click="closePasarDetail" class="text-white/80 hover:text-white transition text-lg">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-6 space-y-4">
                {{-- Banner Total Pendapatan Pasar Ini --}}
                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl border border-emerald-200 dark:border-emerald-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Total Akumulasi Pendapatan Retribusi:</span>
                        <div class="text-2xl font-black text-emerald-700 dark:text-emerald-400">
                            Rp {{ number_format($detailPasar->pendapatan_pasars_sum_jumlah_retribusi ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-600 text-white">
                            {{ $detailPasar->pendapatan_pasars_count ?? 0 }} Total Bukti SSRD
                        </span>
                    </div>
                </div>

                {{-- Tabel Riwayat SSRD Pasar Ini --}}
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-emerald-600"></i> Riwayat Penyetoran SSRD
                </h3>

                <div class="overflow-x-auto max-h-80 border border-gray-200 dark:border-gray-700 rounded-lg">
                    <table class="w-full text-xs text-left text-gray-700 dark:text-gray-300">
                        <thead class="bg-gray-100 dark:bg-gray-700 uppercase font-semibold text-[10px] sticky top-0">
                            <tr>
                                <th class="px-3 py-2">Tgl Setor</th>
                                <th class="px-3 py-2">No. SSRD</th>
                                <th class="px-3 py-2">Jenis</th>
                                <th class="px-3 py-2">Masa & Thn</th>
                                <th class="px-3 py-2 text-right">Nominal (Rp)</th>
                                <th class="px-3 py-2">Penyetor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse ($detailRiwayat as $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition">
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $item->tanggal_penyetoran ? $item->tanggal_penyetoran->format('d/m/Y') : '-' }}</td>
                                    <td class="px-3 py-2 font-mono font-medium">{{ $item->nomor_urut_ssrd ?: '-' }}</td>
                                    <td class="px-3 py-2">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 font-semibold">
                                            {{ $item->jenis_retribusi }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2">{{ $item->masa_retribusi ?: '-' }} ({{ $item->tahun_retribusi }})</td>
                                    <td class="px-3 py-2 text-right font-bold text-emerald-600 dark:text-emerald-400">
                                        Rp {{ number_format($item->jumlah_retribusi, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2">{{ $item->nama_penyetor }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada riwayat penyetoran untuk pasar ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900 flex justify-center">
                <button type="button" wire:click="closePasarDetail" class="px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-200 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 rounded-lg transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- =====================================================================
         MODAL KONFIRMASI HAPUS
    ====================================================================== --}}
    <div x-data="{ showDeleteModal: @entangle('showDeleteModal') }">
        <div
            x-show="showDeleteModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4"
        >
            <div
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="w-full max-w-sm bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6 border border-gray-200 dark:border-gray-700"
            >
                <div class="flex flex-col items-center text-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <i class="fas fa-trash-alt text-red-500 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 dark:text-white text-base">Hapus Data SSRD?</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Anda yakin ingin menghapus data bukti penyetoran ini:
                        </p>
                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mt-1 p-2 bg-gray-50 dark:bg-gray-700 rounded border">
                            {{ $deleteInfo }}
                        </p>
                        <p class="text-[11px] text-red-500 mt-2 font-medium">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                    <div class="flex gap-3 w-full">
                        <button type="button" @click="showDeleteModal = false"
                                class="flex-1 px-4 py-2 text-xs font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition">
                            Batal
                        </button>
                        <button
                            wire:click="delete"
                            wire:target="delete"
                            wire:loading.attr="disabled"
                            class="flex-1 px-4 py-2 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-lg shadow transition disabled:opacity-70 disabled:cursor-not-allowed"
                        >
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

</div>

@push('scripts')
<script>
/**
 * Alpine.js manager untuk fitur Pendapatan Pasar SSRD.
 * Modal tambah/edit dibuka sepenuhnya di sisi klien tanpa request ke server.
 */
function ssrdManager() {
    return {
        tab: 'transaksi', // 'transaksi' | 'ringkasan'
        modalOpen: false,
        mode: 'create', // 'create' | 'edit'
        detailItem: null,

        // State input nominal & terbilang di sisi klien
        formCreate: {
            jumlah: 0,
            terbilang: 'Nol Rupiah'
        },
        formEdit: {
            jumlah: 0,
            terbilang: 'Nol Rupiah'
        },

        notify: { show: false, type: 'success', message: '', _timer: null },

        init() {
            // Inisialisasi awal
        },

        openCreateModal() {
            this.mode = 'create';
            this.formCreate.jumlah = 0;
            this.formCreate.terbilang = 'Nol Rupiah';
            this.$wire.set('jumlah_retribusi', 0);
            this.$wire.set('terbilang', 'Nol Rupiah');
            this.modalOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        openEditModal(item) {
            this.mode = 'edit';

            // Set state lokal Alpine
            this.formEdit.jumlah = Number(item.jumlah_retribusi) || 0;
            this.formEdit.terbilang = item.terbilang || this.terbilangJS(this.formEdit.jumlah);

            // Batch sync ke Livewire properties tanpa server-roundtrip membuka modal
            this.$wire.set('editId', item.id);
            this.$wire.set('edit_pasar_id', item.pasar_id);
            this.$wire.set('edit_tahun_ssrd', item.tahun_ssrd);
            this.$wire.set('edit_no_rekening_bank', item.no_rekening_bank || '');
            this.$wire.set('edit_masa_retribusi', item.masa_retribusi || '');
            this.$wire.set('edit_tahun_retribusi', item.tahun_retribusi);
            this.$wire.set('edit_nomor_urut_ssrd', item.nomor_urut_ssrd || '');
            this.$wire.set('edit_jenis_retribusi', item.jenis_retribusi || 'SKRD');
            this.$wire.set('edit_nomor_ayat', item.nomor_ayat || '');
            this.$wire.set('edit_jumlah_retribusi', this.formEdit.jumlah);
            this.$wire.set('edit_terbilang', this.formEdit.terbilang);

            let tgl = '';
            if (item.tanggal_penyetoran) {
                tgl = item.tanggal_penyetoran.substring(0, 10);
            }
            this.$wire.set('edit_tanggal_penyetoran', tgl);
            this.$wire.set('edit_nama_penyetor', item.nama_penyetor || '');
            this.$wire.set('edit_petugas_bank', item.petugas_bank || '');
            this.$wire.set('edit_keterangan', item.keterangan || '');

            this.modalOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        closeModal() {
            this.modalOpen = false;
            document.body.classList.remove('overflow-hidden');
        },

        viewDetail(item) {
            this.detailItem = item;
        },

        onJumlahChange(targetMode) {
            if (targetMode === 'create') {
                const nominal = Math.max(0, Number(this.formCreate.jumlah) || 0);
                this.formCreate.terbilang = this.terbilangJS(nominal);
                this.$wire.set('jumlah_retribusi', nominal);
                this.$wire.set('terbilang', this.formCreate.terbilang);
            } else {
                const nominal = Math.max(0, Number(this.formEdit.jumlah) || 0);
                this.formEdit.terbilang = this.terbilangJS(nominal);
                this.$wire.set('edit_jumlah_retribusi', nominal);
                this.$wire.set('edit_terbilang', this.formEdit.terbilang);
            }
        },

        showNotify(detail) {
            clearTimeout(this.notify._timer);
            this.notify.type = detail.type ?? 'success';
            this.notify.message = detail.message ?? '';
            this.notify.show = true;
            this.notify._timer = setTimeout(() => { this.notify.show = false; }, 3500);
        },

        formatRupiah(val) {
            const num = Number(val) || 0;
            return 'Rp ' + num.toLocaleString('id-ID');
        },

        formatDate(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return dateStr;
            return d.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
        },

        /**
         * Algoritma Terbilang di sisi klien untuk update instan tanpa request
         */
        terbilangJS(nilai) {
            nilai = Math.floor(Math.abs(Number(nilai) || 0));
            if (nilai === 0) return 'Nol Rupiah';

            const huruf = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];

            function sebut(n) {
                n = Math.floor(n);
                if (n < 12) return ' ' + huruf[n];
                if (n < 20) return sebut(n - 10) + ' Belas';
                if (n < 100) return sebut(Math.floor(n / 10)) + ' Puluh' + sebut(n % 10);
                if (n < 200) return ' Seratus' + sebut(n - 100);
                if (n < 1000) return sebut(Math.floor(n / 100)) + ' Ratus' + sebut(n % 100);
                if (n < 2000) return ' Seribu' + sebut(n - 1000);
                if (n < 1000000) return sebut(Math.floor(n / 1000)) + ' Ribu' + sebut(n % 1000);
                if (n < 1000000000) return sebut(Math.floor(n / 1000000)) + ' Juta' + sebut(n % 1000000);
                if (n < 1000000000000) return sebut(Math.floor(n / 1000000000)) + ' Miliar' + sebut(n % 1000000000);
                if (n < 1000000000000000) return sebut(Math.floor(n / 1000000000000)) + ' Triliun' + sebut(n % 1000000000000);
                return ' Tak Terhingga';
            }

            const res = sebut(nilai).replace(/\s+/g, ' ').trim();
            return res + ' Rupiah';
        }
    };
}
</script>
@endpush

