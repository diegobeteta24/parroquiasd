<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ResetSuperAdmin extends Command
{
    protected $signature = 'app:superadmin-reset {--email=} {--password=}';
    protected $description = 'Create or reset the Super Admin user with given email/password and ensure superadmin role';

    public function handle(): int
    {
        $email = $this->option('email') ?: env('SUPERADMIN_EMAIL', 'admin@parroquiasantodomingo.gt');
        $password = $this->option('password') ?: env('SUPERADMIN_PASSWORD', 'Gama5649');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email');
            return self::FAILURE;
        }
        if (strlen($password) < 6) {
            $this->error('Password must be at least 6 characters');
            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        $role = Role::firstOrCreate(['name' => 'superadmin']);
        if (! $user->hasRole('superadmin')) {
            $user->assignRole($role);
        }

        $this->info("Superadmin ready: $email");
        return self::SUCCESS;
    }
}
