<div class="p-6 text-gray-900 dark:text-gray-100">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-gray-800 dark:text-white">Users Management</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola penugasan akun pengguna per pasar dengan peran OPD, Operator, atau Bapokting.</p>
        </div>

        <!--Add Button-->
        <button wire:target="create" wire:click="create" wire:loading.attr="disabled"
            class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700 transition disabled:opacity-50">
            <span wire:loading.remove wire:target="create">
                <i class="fas fa-plus text-xs"></i> Tambah User
            </span>
            <span wire:loading wire:target="create" class="flex items-center justify-center gap-1">
                <i class="fas fa-spinner fa-spin"></i> Memuat...
            </span>
        </button>
    </div>

    @if (session()->has('success'))
        <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-xs flex items-center gap-2 dark:bg-green-950/40 dark:border-green-800 dark:text-green-300">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-xs text-left">
            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 uppercase font-semibold">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Peran (Role)</th>
                    <th class="px-4 py-3">Lokasi Pasar (Mounting)</th>
                    <th class="px-4 py-3 text-center w-28">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse ($users as $user)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 flex items-center justify-center font-bold text-[11px] uppercase">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <span>{{ $user->name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $user->email }}</td>
                    <td class="px-4 py-3">
                        @foreach ($user->roles as $r)
                            @php
                                $badgeClass = match (strtolower($r->name)) {
                                    'admin' => 'bg-rose-100 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800',
                                    'opd' => 'bg-indigo-100 text-indigo-700 border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800',
                                    'operator' => 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
                                    'bapokting' => 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
                                    default => 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold border {{ $badgeClass }} uppercase">
                                {{ $r->name }}
                            </span>
                        @endforeach
                        @if ($user->roles->isEmpty())
                            <span class="text-gray-400 italic text-[11px]">- Belum ada role -</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if ($user->pasar)
                            <div class="flex items-center gap-1.5 text-gray-800 dark:text-gray-200 font-medium">
                                <i class="fa-solid fa-shop text-emerald-600 text-xs"></i>
                                <span>{{ $user->pasar->nama_pasar }}</span>
                                <span class="text-[10px] text-gray-400">(Kec. {{ $user->pasar->kecamatan }})</span>
                            </div>
                        @else
                            <span class="inline-flex items-center gap-1 text-[11px] text-gray-400 italic">
                                <i class="fas fa-minus-circle"></i> Tidak Terikat Pasar
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            {{-- Edit --}}
                            <button type="button" wire:click="edit({{ $user->id }})" wire:target="edit({{ $user->id }})"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center w-7 h-7 rounded bg-amber-50 text-amber-600 hover:bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400 transition"
                                title="Edit User">
                                <i class="fas fa-edit text-xs"></i>
                            </button>

                            {{-- Delete (cegah hapus admin utama) --}}
                            @if (!$user->hasRole('admin'))
                            <button type="button" wire:click="confirmDelete({{ $user->id }})" wire:target="confirmDelete({{ $user->id }})"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center w-7 h-7 rounded bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 transition"
                                title="Hapus User">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                        Tidak ada data pengguna.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal Tambah / Edit User --}}
    @if ($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-md p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <i class="fas {{ $userId ? 'fa-user-pen text-amber-500' : 'fa-user-plus text-blue-500' }}"></i>
                    <span>{{ $userId ? 'Edit Pengguna' : 'Tambah Pengguna Baru' }}</span>
                </h2>
                <button type="button" wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-3 text-xs">
                {{-- Nama --}}
                <div>
                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input wire:model.defer="name" placeholder="cth. Budi Santoso"
                        class="w-full rounded-lg border px-3 py-2 text-xs bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                    @error('name')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Alamat Email <span class="text-red-500">*</span>
                    </label>
                    <input wire:model.defer="email" type="email" placeholder="nama@sangugade.palukota.go.id"
                        class="w-full rounded-lg border px-3 py-2 text-xs bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                    @error('email')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Kata Sandi {!! $userId ? '<span class="text-gray-400 font-normal">(opsional jika tidak diubah)</span>' : '<span class="text-red-500">*</span>' !!}
                    </label>
                    <input wire:model.defer="password" type="password"
                        placeholder="{{ $userId ? 'Biarkan kosong jika tidak diubah' : 'Minimal 6 karakter' }}"
                        class="w-full rounded-lg border px-3 py-2 text-xs bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                    @error('password')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Role (Hanya OPD, Operator, Bapokting) --}}
                <div>
                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Peran / Role <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.defer="role"
                        class="w-full rounded-lg border px-3 py-2 text-xs bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">-- Pilih Role --</option>
                        @foreach ($roles as $r)
                        <option value="{{ $r }}">{{ strtoupper($r) }}</option>
                        @endforeach
                    </select>
                    @error('role')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Mounting Pasar --}}
                <div>
                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Penempatan Pasar (Mounting) <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.defer="pasar_id"
                        class="w-full rounded-lg border px-3 py-2 text-xs bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">-- Pilih Pasar Penempatan --</option>
                        @foreach ($pasars as $pasar)
                        <option value="{{ $pasar->id }}">{{ $pasar->nama_pasar }} (Kec. {{ $pasar->kecamatan }})</option>
                        @endforeach
                    </select>
                    @error('pasar_id')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end mt-6 gap-2">
                <button type="button" wire:click="$set('showModal', false)"
                    class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 rounded-lg transition">
                    Batal
                </button>

                <!--Save Button-->
                <button wire:target="save" wire:click="save" wire:loading.attr="disabled"
                    class="px-5 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition disabled:opacity-50">
                    <span wire:loading.remove wire:target="save">Simpan</span>
                    <span wire:loading wire:target="save" class="flex items-center justify-center gap-1">
                        <i class="fas fa-spinner fa-spin"></i> Menyimpan...
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Confirmation Modal  --}}
    @if ($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-sm p-6 bg-white rounded-2xl shadow-xl dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
            <div class="text-center">
                <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 dark:bg-red-950/40 dark:text-red-400 mx-auto flex items-center justify-center mb-3 text-lg">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
                <h3 class="mb-2 text-sm font-bold text-gray-800 dark:text-white">Konfirmasi Hapus Pengguna</h3>
                <p class="text-xs text-gray-500 dark:text-gray-300 leading-relaxed mb-5">
                    Apakah Anda yakin ingin menghapus user <span class="font-bold text-gray-900 dark:text-white">{{ $deleteName }}</span>? Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>

            <div class="flex justify-center gap-2">
                <button type="button" wire:click="$set('showDeleteModal', false)"
                    class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 rounded-lg transition">
                    Batal
                </button>

                <button wire:target="delete" wire:click="delete" wire:loading.attr="disabled"
                    class="px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm transition disabled:opacity-50">
                    <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                    <span wire:loading wire:target="delete" class="flex items-center justify-center gap-1">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
