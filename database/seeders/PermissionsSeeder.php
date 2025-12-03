<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Define basic permissions used by the app
        $perms = [
            'view calendar',
            'download mass pdf',
            'manage intentions',
            'view reports',
        ];

        foreach ($perms as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Ensure roles exist
    $roleSecretaria = Role::firstOrCreate(['name' => 'Secretaria', 'guard_name' => 'web']);
    $rolePadre = Role::firstOrCreate(['name' => 'Padre', 'guard_name' => 'web']);
    $roleSuper = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);

        // Assign permissions to roles
        $roleSecretaria->givePermissionTo($perms);
        $rolePadre->givePermissionTo(['view calendar', 'download mass pdf']);
        // superadmin: assign all (also covered by Gate::before)
        $roleSuper->givePermissionTo(Permission::all());
    }
}
