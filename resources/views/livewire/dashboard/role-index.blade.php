<div class="p-6 text-gray-900 dark:text-gray-100">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-gray-800 dark:text-white">Role Management</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola konfigurasi hak akses dan perizinan untuk setiap peran pengguna di sistem Sangu Gade.</p>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-xs flex items-center gap-2 dark:bg-green-950/40 dark:border-green-800 dark:text-green-300">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Role List Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-xs text-left">
            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 uppercase font-semibold">
                <tr>
                    <th class="px-4 py-3 w-40">Peran (Role)</th>
                    <th class="px-4 py-3">Pengguna Terkait</th>
                    <th class="px-4 py-3">Hak Akses (Permissions)</th>
                    <th class="px-4 py-3 text-center w-36">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach ($roles as $role)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                    <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">
                        <div class="flex items-center gap-2">
                            <span class="uppercase tracking-wider">{{ $role->name }}</span>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                {{ $role->users_count }} User
                            </span>
                        </div>
                    </td>

                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-1 max-w-sm">
                            @forelse ($role->users as $user)
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800">
                                {{ $user->name }}
                            </span>
                            @empty
                            <span class="text-gray-400 text-xs italic">
                                Belum ada user
                            </span>
                            @endforelse
                        </div>
                    </td>

                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-1 max-w-md">
                            @forelse ($role->permissions as $permission)
                            <span class="text-[11px] px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800 font-mono">
                                {{ $permission->name }}
                            </span>
                            @empty
                            <span class="text-gray-400 text-xs italic">
                                Belum ada permission
                            </span>
                            @endforelse
                        </div>
                    </td>

                    <td class="px-4 py-3 text-center">
                        <button type="button"
                            wire:click="editPermissions({{ $role->id }})"
                            wire:target="editPermissions({{ $role->id }})"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold
                                bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100
                                dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800 dark:hover:bg-indigo-900/60
                                transition shadow-sm disabled:opacity-50">
                            <i class="fas fa-key text-[11px]"></i>
                            <span wire:loading.remove wire:target="editPermissions({{ $role->id }})">
                                Set Permission
                            </span>
                            <span wire:loading wire:target="editPermissions({{ $role->id }})">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Modal Set Permissions --}}
    @if ($showPermissionsModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-lg p-6 rounded-2xl shadow-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">

            <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100 dark:border-gray-700">
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-shield-halved text-indigo-500"></i>
                        <span>Atur Permission: <span class="uppercase text-indigo-600 dark:text-indigo-400">{{ $selectedRoleName }}</span></span>
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pilih hak akses yang diperbolehkan untuk peran ini.</p>
                </div>
                <button type="button" wire:click="$set('showPermissionsModal', false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- Permissions Checkbox Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6 p-4 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                @foreach ($permissions as $permission)
                <label class="flex items-center gap-2 text-xs font-medium text-gray-700 dark:text-gray-300 cursor-pointer hover:text-blue-600 transition">
                    <input type="checkbox"
                        wire:model="selectedPermissions"
                        value="{{ $permission->name }}"
                        class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:bg-gray-700" />
                    <span>{{ $permission->name }}</span>
                </label>
                @endforeach
            </div>

            {{-- Actions --}}
            <div class="flex justify-end gap-2">
                <button type="button"
                    wire:click="$set('showPermissionsModal', false)"
                    class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 rounded-lg transition">
                    Batal
                </button>

                <button type="button"
                    wire:click="savePermissions"
                    wire:target="savePermissions"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center justify-center gap-1.5 px-5 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition disabled:opacity-50">
                    <span wire:loading.remove wire:target="savePermissions">
                        Simpan Perubahan
                    </span>
                    <span wire:loading wire:target="savePermissions" class="flex items-center gap-1">
                        <i class="fas fa-spinner fa-spin"></i> Menyimpan...
                    </span>
                </button>
            </div>

        </div>
    </div>
    @endif

</div>
