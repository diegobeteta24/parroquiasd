<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetSuperadminPassword extends Command
{
    protected $signature = 'user:superadmin-password {--email=} {--password=}';
    protected $description = 'Force reset the superadmin user password and mark email as verified';

    public function handle(): int
    {
        $email = $this->option('email') ?: env('SUPERADMIN_EMAIL', 'admin@parroquiasantodomingo.gt');
        $pass  = $this->option('password') ?: env('SUPERADMIN_PASSWORD');
        if (! $pass) {
            $this->error('No password provided. Use --password or set SUPERADMIN_PASSWORD in .env');
            return self::INVALID;
        }

        $user = User::where('email', $email)->first();
        if (! $user) {
            $this->error("User with email {$email} not found.");
            return self::FAILURE;
        }

        $user->forceFill([
            'password' => Hash::make($pass),
            'email_verified_at' => now(),
        ])->save();

        $this->info("Superadmin password updated for {$email}.");
        return self::SUCCESS;
    }
}
