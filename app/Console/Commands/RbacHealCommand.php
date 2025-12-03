<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RbacHealth;

class RbacHealCommand extends Command
{
    protected $signature = 'rbac:heal';
    protected $description = 'Ensure roles, permissions, and superadmin user exist and reset permission cache';

    public function handle(RbacHealth $svc): int
    {
        $svc->ensure();
        $this->info('RBAC state verified.');
        return self::SUCCESS;
    }
}
