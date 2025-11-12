<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Permissões personalizadas
        $customPermissions = [
            'view_all:advertise',
            'view_all:businesshours',
            'view_all:unavailabilities',
            'edit_all:businesshours'
        ];

        foreach ($customPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // Atribuir permissões ao role super_admin (opcional)
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($customPermissions);
        }
    }
}