<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Mass time templates & superadmin
        $this->call([
            \Database\Seeders\MassTimeTemplateSeeder::class,
            \Database\Seeders\PermissionsSeeder::class,
            \Database\Seeders\SuperAdminSeeder::class,
            \Database\Seeders\StaffUsersSeeder::class,
            \Database\Seeders\WeekIntentionsSeeder::class,
        ]);
    }
}
