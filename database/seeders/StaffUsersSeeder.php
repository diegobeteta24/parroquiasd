<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class StaffUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure roles exist
        $secretaria = Role::firstOrCreate(['name' => 'Secretaria']);
        $padre = Role::firstOrCreate(['name' => 'Padre']);
        $super = Role::firstOrCreate(['name' => 'superadmin']);

        // Secretaria user (use env to allow overrides)
        $secEmail = env('SECRETARIA_EMAIL', 'secretaria@parroquiasantodomingo.gt');
        $secPass  = env('SECRETARIA_PASSWORD', 'Secretaria123!');
        $sec = $this->ensureUser(
            email: $secEmail,
            name: 'Secretaría',
            plainPassword: $secPass,
            role: $secretaria,
            forceKey: 'SECRETARIA_PASSWORD_FORCE'
        );

        // Padre user (Fray Geovanni)
        $frayEmail = env('PADRE_EMAIL', 'fray.geovanni@parroquiasantodomingo.gt');
        $frayPass  = env('PADRE_PASSWORD', 'FrayGeovanni123!');
        $this->ensureUser(
            email: $frayEmail,
            name: 'Fray Geovanni',
            plainPassword: $frayPass,
            role: $padre,
            forceKey: 'PADRE_PASSWORD_FORCE'
        );

        // Optionally ensure SuperAdmin has role
        $adminEmail = env('SUPERADMIN_EMAIL', 'admin@parroquiasantodomingo.gt');
        if ($admin = User::where('email', $adminEmail)->first()) {
            $admin->syncRoles([$super]);
        }
    }

    protected function ensureUser(string $email, string $name, ?string $plainPassword, Role $role, ?string $forceKey = null): User
    {
        $user = User::firstOrNew(['email' => $email]);
        $isNew = ! $user->exists;

        $user->name = $name;
        if ($isNew || ! $user->email_verified_at) {
            $user->email_verified_at = now();
        }

        if (($isNew || $this->shouldForcePasswords($forceKey)) && $plainPassword) {
            $user->password = Hash::make($plainPassword);
        }

        $user->save();
        $user->syncRoles([$role]);

        return $user;
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
