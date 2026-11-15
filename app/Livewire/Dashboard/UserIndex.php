<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\User;
use App\Models\Pasar;
use App\Helpers\PasarsCacheHelper;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserIndex extends Component
{
    public $users;
    public $roles;
    public $pasars;

    public $userId = null;
    public $name = '';
    public $email = '';
    public $password = '';
    public $role = '';
    public $pasar_id = null;

    public $showModal = false;
    public $showDeleteModal = false;
    public $deleteId;
    public $deleteName;

    // Role yang diizinkan untuk dipilih di form User Management
    public static array $allowedRoles = ['opd', 'operator', 'bapokting'];

    protected function rules()
    {
        return [
            'name' => 'required|string|min:3',
            'email' => 'required|email|unique:users,email,' . $this->userId,
            'password' => $this->userId ? 'nullable|min:6' : 'required|min:6',
            'role' => 'required|in:' . implode(',', self::$allowedRoles),
            'pasar_id' => 'required|exists:pasars,id',
        ];
    }

    protected $messages = [
        'role.in' => 'Role yang dipilih harus Opd, Operator, atau Bapokting.',
        'pasar_id.required' => 'Pilih pasar untuk menempatkan user ini.',
        'pasar_id.exists' => 'Pasar yang dipilih tidak valid.',
    ];

    public function mount()
    {
        // Pastikan role-role yang diizinkan ada di tabel roles
        foreach (self::$allowedRoles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Dropdown role hanya berisi Opd, Operator, Bapokting (Admin tidak dimunculkan)
        $this->roles = Role::whereIn('name', self::$allowedRoles)
            ->pluck('name')
            ->toArray();

        $this->loadPasars();
        $this->loadUsers();
    }

    public function loadPasars()
    {
        $this->pasars = PasarsCacheHelper::getAllPasar();
    }

    public function loadUsers()
    {
        // Muat user beserta roles dan pasarnya
        $this->users = User::with(['roles', 'pasar'])->get();
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id)
    {
        $user = User::with('roles')->findOrFail($id);

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->pasar_id = $user->pasar_id;

        $userRole = $user->roles->first()?->name ?? '';
        // Jika user yang diedit bukan admin, isi field role
        $this->role = in_array($userRole, self::$allowedRoles) ? $userRole : '';

        $this->showModal = true;
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

        $this->resetForm();
        $this->loadUsers();
        $this->showModal = false;
        session()->flash('success', 'User berhasil disimpan.');
    }

    public function confirmDelete(int $id)
    {
        $user = User::findOrFail($id);
        $this->deleteId = $user->id;
        $this->deleteName = $user->name;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $user = User::findOrFail($this->deleteId);
        $user->delete();
        $this->loadUsers();
        $this->reset(['deleteId', 'deleteName', 'showDeleteModal']);
        session()->flash('success', 'User berhasil dihapus.');
    }

    private function resetForm()
    {
        $this->reset(['userId', 'name', 'email', 'password', 'role', 'pasar_id']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.dashboard.user-index')
            ->layout('layouts.dashboard.main');
    }
}
