<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cache role & permission Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // General Permissions
        $permissions = [
            'create',
            'read',
            'update',
            'delete',
            'import',
            'export',
        ];

        // Buat permission jika belum ada
        foreach ($permissions as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        // Sinkronkan ke role admin
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->syncPermissions(Permission::whereIn('name', $permissions)->get());
        }
    }
}
