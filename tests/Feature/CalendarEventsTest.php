<?php

use App\Models\MassInstance;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function(){
    if (config('database.default') === 'sqlite' && ! extension_loaded('pdo_sqlite')) {
        $this->markTestSkipped('pdo_sqlite extension not available');
    }
});

it('returns event URLs based on role', function () {
    $mass = MassInstance::factory()->create([
        'starts_at' => now()->addDay()->setTime(18, 0),
        'capacity' => 10,
        'occupied' => 3,
        'status' => 'scheduled',
    ]);

    Role::firstOrCreate(['name' => 'Secretaria']);
    Role::firstOrCreate(['name' => 'Padre']);

    $secretaria = User::factory()->create();
    $padre = User::factory()->create();
    $secretaria->assignRole('Secretaria');
    $padre->assignRole('Padre');

    $params = [
        'start' => now()->subDay()->toDateString(),
        'end' => now()->addDays(2)->toDateString(),
    ];

    // Secretaria sees detail URL
    $respSec = $this->actingAs($secretaria)->getJson(route('admin.mass-events', $params));
    $respSec->assertOk();
    $eventsSec = $respSec->json();
    expect($eventsSec)->toBeArray()->and(count($eventsSec))->toBeGreaterThan(0);
    $event = collect($eventsSec)->firstWhere('id', $mass->id);
    expect($event)->not->toBeNull();
    expect($event['url'])->toBe(route('admin.masses.show', $mass));

    // Padre sees PDF URL
    $respPadre = $this->actingAs($padre)->getJson(route('admin.mass-events', $params));
    $respPadre->assertOk();
    $eventsPadre = $respPadre->json();
    $eventP = collect($eventsPadre)->firstWhere('id', $mass->id);
    expect($eventP)->not->toBeNull();
    expect($eventP['url'])->toBe(route('admin.masses.pdf', $mass));
});
