<div
    class="p-6 text-gray-900 dark:text-gray-100"
    wire:poll.30s.keep-alive
    x-data="userManager()"
    x-init="init()"
    @user-saved.window="closeModal()"
    @user-deleted.window="closeDeleteModal()"
    @notify.window="showNotify($event.detail)"
>
    {{-- Floating Notification --}}
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
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-gray-800 dark:text-white">Users Management</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola penugasan akun pengguna per pasar (OPD, Operator, Bapokting).</p>
        </div>

        <!-- Add Button (Buka modal instan via Alpine tanpa roundtrip) -->
        <button
            type="button"
            @click="openCreateModal()"
            class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition"
        >
            <i class="fas fa-plus text-xs"></i> Tambah Pengguna
        </button>
    </div>

    {{-- Table List Pengguna Non-Admin --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-xs text-left">
            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 uppercase font-semibold">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Peran (Role)</th>
                    <th class="px-4 py-3">Lokasi Pasar (Mounting)</th>
                    <th class="px-4 py-3">Status</th>
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
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300 font-mono">{{ $user->email }}</td>
                    <td class="px-4 py-3">
                        @foreach ($user->roles as $r)
                            @php
                                $badgeClass = match (strtolower($r->name)) {
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
                    </td>
                    <td class="px-4 py-3">
                        @if ($user->pasar)
                            <div class="flex items-center gap-1.5 text-gray-800 dark:text-gray-200 font-medium">
                                <i class="fa-solid fa-shop text-emerald-600 text-xs"></i>
                                <span>{{ $user->pasar->nama_pasar }}</span>
                                <span class="text-[10px] text-gray-400 font-normal">(Kec. {{ $user->pasar->kecamatan }})</span>
                            </div>
                        @else
                            <span class="inline-flex items-center gap-1 text-[11px] text-gray-400 italic">
                                <i class="fas fa-minus-circle"></i> Tidak Terikat Pasar
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if ($user->isOnline())
                            <div class="flex flex-col gap-1">
                                <span class="inline-flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Online
                                </span>
                            </div>
                        @else
                            <div class="flex flex-col gap-0.5 text-gray-500 dark:text-gray-400">
                                <span class="inline-flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                    Offline
                                </span>
                                <span class="text-[10px]">{{ $user->lastSeenLabel() }}</span>
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            {{-- Tombol Edit via Alpine: @click="openEditModal(@js($user))" --}}
                            <button
                                type="button"
                                @click="openEditModal(@js($user))"
                                class="inline-flex items-center justify-center w-7 h-7 rounded bg-amber-50 text-amber-600 hover:bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400 transition"
                                title="Edit Pengguna"
                            >
                                <i class="fas fa-edit text-xs"></i>
                            </button>

                            {{-- Tombol Hapus via Alpine: @click="openDeleteModal(@js($user))" --}}
                            <button
                                type="button"
                                @click="openDeleteModal(@js($user))"
                                class="inline-flex items-center justify-center w-7 h-7 rounded bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 transition"
                                title="Hapus Pengguna"
                            >
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center text-gray-400 dark:text-gray-500">
                        <div class="flex flex-col items-center gap-2">
                            <i class="fa-solid fa-users-slash text-3xl text-gray-300 dark:text-gray-600"></i>
                            <p class="font-medium text-xs">Belum ada akun pengguna non-admin (OPD, Operator, Bapokting).</p>
                            <p class="text-[11px] text-gray-400">Klik tombol "Tambah Pengguna" untuk membuat akun baru.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- =====================================================================
         MODAL ALPINE: TAMBAH & EDIT USER
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
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 overflow-y-auto"
    >
        <div
            @click.outside="closeModal()"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="w-full max-w-md p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700"
        >
            {{-- Modal Header --}}
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <i class="fas" :class="mode === 'create' ? 'fa-user-plus text-blue-500' : 'fa-user-pen text-amber-500'"></i>
                    <span x-text="mode === 'create' ? 'Tambah Pengguna Baru' : 'Edit Pengguna'"></span>
                </h2>
                <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form wire:submit.prevent="save" class="space-y-3.5 text-xs">
                {{-- Nama --}}
                <div>
                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input
                        wire:model.defer="name"
                        type="text"
                        placeholder="cth. Budi Santoso"
                        class="w-full rounded-lg border px-3 py-2 text-xs bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                    @error('name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Alamat Email <span class="text-red-500">*</span>
                    </label>
                    <input
                        wire:model.defer="email"
                        type="email"
                        placeholder="nama@sangugade.palukota.go.id"
                        class="w-full rounded-lg border px-3 py-2 text-xs bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                    @error('email')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Kata Sandi
                        <span x-show="mode === 'edit'" class="text-gray-400 font-normal">(opsional jika tidak diubah)</span>
                        <span x-show="mode === 'create'" class="text-red-500">*</span>
                    </label>
                    <input
                        wire:model.defer="password"
                        type="password"
                        :placeholder="mode === 'create' ? 'Minimal 6 karakter' : 'Biarkan kosong jika tidak diubah'"
                        class="w-full rounded-lg border px-3 py-2 text-xs bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                    @error('password')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Role (Hanya OPD, Operator, Bapokting) --}}
                <div>
                    <label class="block font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Peran / Role <span class="text-red-500">*</span>
                    </label>
                    <select
                        wire:model.live="role"
                        class="w-full rounded-lg border px-3 py-2 text-xs bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent font-medium"
                    >
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
                        Penempatan Pasar (Mounting)
                        @if ($role === 'opd')
                            <span class="text-gray-400 font-normal">(opsional)</span>
                        @else
                            <span class="text-red-500">*</span>
                        @endif
                    </label>
                    <select
                        wire:model.defer="pasar_id"
                        class="w-full rounded-lg border px-3 py-2 text-xs bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="">-- Pilih Pasar Penempatan --</option>
                        @foreach ($pasars as $pasar)
                            <option value="{{ $pasar->id }}">{{ $pasar->nama_pasar }} (Kec. {{ $pasar->kecamatan }})</option>
                        @endforeach
                    </select>
                    @error('pasar_id')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Actions --}}
                <div class="flex justify-end gap-2 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <button
                        type="button"
                        @click="closeModal()"
                        class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 rounded-lg transition"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="px-5 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition disabled:opacity-50 min-w-[90px]"
                    >
                        <span wire:loading.remove wire:target="save">Simpan</span>
                        <span wire:loading wire:target="save" class="flex items-center justify-center gap-1">
                            <i class="fas fa-spinner fa-spin"></i>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- =====================================================================
         MODAL ALPINE: KONFIRMASI HAPUS USER
    ====================================================================== --}}
    <div
        x-show="deleteModalOpen"
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
            @click.outside="closeDeleteModal()"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="w-full max-w-sm p-6 bg-white rounded-2xl shadow-xl dark:bg-gray-800 border border-gray-200 dark:border-gray-700"
        >
            <div class="text-center">
                <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 dark:bg-red-950/40 dark:text-red-400 mx-auto flex items-center justify-center mb-3 text-lg">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
                <h3 class="mb-2 text-sm font-bold text-gray-800 dark:text-white">Konfirmasi Hapus Pengguna</h3>
                <p class="text-xs text-gray-500 dark:text-gray-300 leading-relaxed mb-5">
                    Apakah Anda yakin ingin menghapus user <span class="font-bold text-gray-900 dark:text-white" x-text="targetUser ? targetUser.name : ''"></span>? Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>

            <div class="flex justify-center gap-2">
                <button
                    type="button"
                    @click="closeDeleteModal()"
                    class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 rounded-lg transition"
                >
                    Batal
                </button>

                <button
                    type="button"
                    wire:click="delete"
                    wire:loading.attr="disabled"
                    wire:target="delete"
                    class="px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm transition disabled:opacity-50 min-w-[90px]"
                >
                    <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                    <span wire:loading wire:target="delete" class="flex items-center justify-center gap-1">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                </button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function userManager() {
    return {
        modalOpen: false,
        deleteModalOpen: false,
        mode: 'create', // 'create' | 'edit'
        targetUser: null,
        notify: {
            show: false,
            type: 'success',
            message: '',
            _timer: null,
        },

        init() {
            // Inisialisasi awal
        },

        openCreateModal() {
            this.mode = 'create';
            this.targetUser = null;
            this.$wire.resetForm();
            this.modalOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        openEditModal(item) {
            this.mode = 'edit';
            this.targetUser = item;

            // Mengisi data ke Livewire component
            this.$wire.set('userId', item.id);
            this.$wire.set('name', item.name || '');
            this.$wire.set('email', item.email || '');
            this.$wire.set('password', '');
            this.$wire.set('pasar_id', item.pasar_id || '');

            const roleName = item.roles && item.roles.length > 0 ? item.roles[0].name : '';
            this.$wire.set('role', roleName);

            this.modalOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        closeModal() {
            this.modalOpen = false;
            document.body.classList.remove('overflow-hidden');
        },

        openDeleteModal(item) {
            this.targetUser = item;
            this.$wire.set('deleteId', item.id);
            this.$wire.set('deleteName', item.name || '');
            this.deleteModalOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        closeDeleteModal() {
            this.deleteModalOpen = false;
            this.targetUser = null;
            document.body.classList.remove('overflow-hidden');
        },

        showNotify(detail) {
            clearTimeout(this.notify._timer);
            // Tangani format detail baik berupa object langsung {type, message} maupun array/nested [0]
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
