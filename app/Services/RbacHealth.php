<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RbacHealth
{
    /**
     * Ensure baseline roles/permissions and the superadmin user exist and are linked.
     * Idempotent and safe to run multiple times.
     */
    public function ensure(): void
    {
        // Minimal guard against running during tests
        if (app()->runningUnitTests()) {
            return;
        }

        $guard = config('auth.defaults.guard', 'web');

        // Define baseline permissions used by the app
        $perms = [
            'view calendar',
            'download mass pdf',
            'manage intentions',
            'view reports',
        ];

        try {
            // Ensure permissions
            foreach ($perms as $name) {
                Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
            }

            // Ensure roles (guard-specific)
            $roleSecretaria = Role::firstOrCreate(['name' => 'Secretaria', 'guard_name' => $guard]);
            $rolePadre      = Role::firstOrCreate(['name' => 'Padre', 'guard_name' => $guard]);
            $roleSuper      = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => $guard]);

            // Grant perms to roles (superadmin -> all)
            $roleSecretaria->givePermissionTo($perms);
            $rolePadre->givePermissionTo(['view calendar', 'download mass pdf']);
            $roleSuper->givePermissionTo(Permission::where('guard_name',$guard)->get());

            // Ensure superadmin user exists
            $email = env('SUPERADMIN_EMAIL', 'admin@parroquiasantodomingo.gt');
            $raw   = env('SUPERADMIN_PASSWORD');
            $user  = User::where('email',$email)->first();
            if (! $user) {
                $user = User::create([
                    'name' => 'Super Admin',
                    'email' => $email,
                    'password' => Hash::make($raw ?: str()->random(20)),
                    'email_verified_at' => now(),
                ]);
            }

            // Assign role if missing
            if ($user && ! $user->hasRole('superadmin')) {
                $user->assignRole($roleSuper);
            }

            // Reset Spatie permission cache
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        } catch (\Throwable $e) {
            Log::warning('RBAC health ensure failed: '.$e->getMessage());
        }
    }
}
