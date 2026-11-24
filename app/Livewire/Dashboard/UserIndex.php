<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\User;
use App\Helpers\PasarsCacheHelper;
use App\Helpers\RolesCacheHelper;
use App\Helpers\UsersCacheHelper;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserIndex extends Component
{
    public $roles = [];

    // Form inputs
    public $userId = null;
    public $name = '';
    public $email = '';
    public $password = '';
    public $role = '';
    public $pasar_id = null;

    // Delete confirmation
    public $deleteId = null;
    public $deleteName = '';

    // Role yang diizinkan untuk dipilih di form User Management
    public static array $allowedRoles = ['opd', 'operator', 'bapokting'];

    protected function rules()
    {
        return [
            'name' => 'required|string|min:3',
            'email' => 'required|email|unique:users,email,' . $this->userId,
            'password' => $this->userId ? 'nullable|min:6' : 'required|min:6',
            'role' => 'required|in:' . implode(',', self::$allowedRoles),
            'pasar_id' => $this->role === 'opd'
                ? 'nullable|exists:pasars,id'
                : 'required|exists:pasars,id',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama lengkap wajib diisi.',
        'name.min' => 'Nama minimal 3 karakter.',
        'email.required' => 'Alamat email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'email.unique' => 'Email ini sudah terdaftar.',
        'password.required' => 'Kata sandi wajib diisi untuk pengguna baru.',
        'password.min' => 'Kata sandi minimal 6 karakter.',
        'role.required' => 'Pilih peran (role) untuk pengguna.',
        'role.in' => 'Role yang dipilih harus Opd, Operator, atau Bapokting.',
        'pasar_id.required' => 'Pilih pasar untuk menempatkan pengguna ini.',
        'pasar_id.exists' => 'Pasar yang dipilih tidak valid.',
    ];

    public function mount()
    {
        // Pastikan role non-admin sudah ada di database
        foreach (self::$allowedRoles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        $this->roles = self::$allowedRoles;
    }

    public function save()
    {
        $this->validate();

        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'pasar_id' => $this->pasar_id ?: null,
        ];

        if ($this->password) {
            $userData['password'] = Hash::make($this->password);
        }

        $user = User::updateOrCreate(
            ['id' => $this->userId],
            $userData
        );

        $user->syncRoles([$this->role]);

        // Invalidate cache users dan roles secara versioned O(1)
        UsersCacheHelper::refreshAll();
        RolesCacheHelper::refreshAll();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->resetForm();
        $this->dispatch('user-saved');
        $this->dispatch('notify', type: 'success', message: 'Data pengguna berhasil disimpan!');
    }

    public function delete()
    {
        if ($this->deleteId) {
            $user = User::find($this->deleteId);

            // Larang menghapus admin jika ada
            if ($user && !$user->hasRole('admin')) {
                $user->delete();

                UsersCacheHelper::refreshAll();
                RolesCacheHelper::refreshAll();
                app()[PermissionRegistrar::class]->forgetCachedPermissions();

                $this->dispatch('user-deleted');
                $this->dispatch('notify', type: 'success', message: 'Pengguna berhasil dihapus!');
            }
        }

        $this->reset(['deleteId', 'deleteName']);
    }

    public function resetForm()
    {
        $this->reset(['userId', 'name', 'email', 'password', 'role', 'pasar_id']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.dashboard.user-index', [
            'users' => UsersCacheHelper::getAllUsersWithRelations(),
            'pasars' => PasarsCacheHelper::getAllPasar(),
        ])->layout('layouts.dashboard.main');
    }
}
