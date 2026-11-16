<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Helpers\RolesCacheHelper;
use App\Helpers\UsersCacheHelper;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleIndex extends Component
{
    public bool $showPermissionsModal = false;

    public ?int $selectedRoleId = null;
    public string $selectedRoleName = '';
    public array $selectedPermissions = [];

    public function editPermissions(int $roleId)
    {
        $role = Role::with('permissions:id,name')->findOrFail($roleId);

        $this->selectedRoleId = $role->id;
        $this->selectedRoleName = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();

        $this->showPermissionsModal = true;
    }

    public function savePermissions()
    {
        $role = Role::findOrFail($this->selectedRoleId);
        $role->syncPermissions($this->selectedPermissions);

        // Reset cache permission Spatie internal
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Refresh cache helper (RolesCacheHelper & UsersCacheHelper)
        RolesCacheHelper::refreshAll();
        UsersCacheHelper::refreshAll();

        session()->flash('success', "Hak akses (permissions) untuk role {$role->name} berhasil diperbarui.");
        $this->showPermissionsModal = false;
        $this->reset(['selectedRoleId', 'selectedRoleName', 'selectedPermissions']);
    }

    public function render()
    {
        // Cached O(1) & Bebas N+1 (eager load users & permissions)
        return view('livewire.dashboard.role-index', [
            'roles' => RolesCacheHelper::getAllRolesWithRelations(),
            'permissions' => RolesCacheHelper::getAllPermissions(),
        ])->layout('layouts.dashboard.main');
    }
}
