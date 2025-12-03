<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function(){
    if (config('database.default') === 'sqlite' && ! extension_loaded('pdo_sqlite')) {
        $this->markTestSkipped('pdo_sqlite extension not available');
    }
});

it('secretaria can access reports page and padre cannot', function () {
    $secretaria = User::factory()->create();
    $padre = User::factory()->create();
    $super = User::factory()->create();

    Role::firstOrCreate(['name' => 'Secretaria']);
    Role::firstOrCreate(['name' => 'Padre']);
    Role::firstOrCreate(['name' => 'superadmin']);

    $secretaria->assignRole('Secretaria');
    $padre->assignRole('Padre');
    $super->assignRole('superadmin');

    // Reports page (protected by role: Secretaria|superadmin)
    $this->actingAs($secretaria)->get(route('admin.reports'))->assertOk();
    $this->actingAs($padre)->get(route('admin.reports'))->assertForbidden();
    $this->actingAs($super)->get(route('admin.reports'))->assertOk();
});

it('monthly report PDF endpoint restricted to secretaria and superadmin', function () {
    $secretaria = User::factory()->create();
    $padre = User::factory()->create();
    $super = User::factory()->create();

    Role::firstOrCreate(['name' => 'Secretaria']);
    Role::firstOrCreate(['name' => 'Padre']);
    Role::firstOrCreate(['name' => 'superadmin']);

    $secretaria->assignRole('Secretaria');
    $padre->assignRole('Padre');
    $super->assignRole('superadmin');

    $query = ['year' => now()->year, 'month' => now()->month];
    $this->actingAs($secretaria)->get(route('reports.monthly', $query))->assertOk();
    $this->actingAs($padre)->get(route('reports.monthly', $query))->assertForbidden();
    $this->actingAs($super)->get(route('reports.monthly', $query))->assertOk();
});
