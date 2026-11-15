<x-dashboard-layout>
    <div class="p-4 sm:p-6 text-gray-900 dark:text-gray-100">
        {{-- Header Halaman Profil --}}
        <div class="mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                {{ __('Pengaturan Profil & Akun') }}
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Kelola informasi akun pengguna, kata sandi, dan preferensi keamanan Anda.
            </p>
        </div>

        <div class="space-y-6 max-w-4xl">
            {{-- Update Profil Information --}}
            <div class="p-6 bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl">
                <div class="max-w-xl">
                    <livewire:profile.update-profile-information-form />
                </div>
            </div>

            {{-- Update Password --}}
            <div class="p-6 bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl">
                <div class="max-w-xl">
                    <livewire:profile.update-password-form />
                </div>
            </div>

            {{-- Delete Account --}}
            {{-- <div class="p-6 bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-xl">
                <div class="max-w-xl">
                    <livewire:profile.delete-user-form />
                </div>
            </div> --}}
        </div>
    </div>
</x-dashboard-layout>
