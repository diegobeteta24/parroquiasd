<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Usa variables de entorno si están definidas
        $email = env('SUPERADMIN_EMAIL', 'admin@parroquiasantodomingo.gt');
        $rawPassword = env('SUPERADMIN_PASSWORD', 'Gama5649');

        $user = User::firstOrNew(['email' => $email]);
        $isNew = ! $user->exists;

        $user->name = 'Super Admin';
        if ($isNew || ! $user->email_verified_at) {
            $user->email_verified_at = now();
        }

        if (($isNew || $this->shouldForcePasswords('SUPERADMIN_PASSWORD_FORCE')) && $rawPassword) {
            $user->password = Hash::make($rawPassword);
        }

        $user->save();

        // Asegura que el rol superadmin exista y esté asignado
        if (class_exists(Role::class)) {
            $role = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
            if (! $user->hasRole('superadmin')) {
                $user->assignRole($role);
            }
        }
    }

    protected function shouldForcePasswords(?string $scopedKey = null): bool
    {
        if ($this->envFlag('SEED_FORCE_PASSWORDS', false)) {
            return true;
        }

        return $scopedKey ? $this->envFlag($scopedKey, false) : false;
    }

    protected function envFlag(string $key, $default = false): bool
    {
        return filter_var(env($key, $default), FILTER_VALIDATE_BOOLEAN);
    }
}
