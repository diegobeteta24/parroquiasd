<?php

use App\Models\Intention;
use App\Models\MassInstance;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

function createSecretaryUser(): User
{
    $role = Role::firstOrCreate(['name' => 'Secretaria']);
    $user = User::factory()->create();
    $user->assignRole($role);
    return $user;
}

it('allows secretaria to register an ordinary intention', function () {
    $user = createSecretaryUser();
    $mass = MassInstance::factory()->create([
        'starts_at' => now()->addDay()->setTime(8, 0),
        'is_special' => false,
        'capacity' => 5,
        'occupied' => 0,
        'status' => 'scheduled',
    ]);

    $payload = [
        'type' => 'rezada',
        'category' => 'peticiones',
        'public_text' => 'Por la salud de la familia',
        'donor_name' => 'María',
        'phone' => '5555-5555',
        'email' => 'maria@example.com',
        'payment_method' => 'cash',
        'dedicatee' => 'Juan',
        'is_prepaid' => false,
    ];

    $response = $this->actingAs($user)
        ->post(route('admin.intentions.store', $mass), $payload);

    $response->assertRedirect(route('admin.masses.show', $mass));

    $intention = Intention::where('mass_instance_id', $mass->id)->first();
    expect($intention)->not->toBeNull();
    expect($intention->type)->toBe('rezada');
    expect((float) $intention->amount)->toBe(50.0);
    expect($intention->status)->toBe('paid');
    expect($intention->channel)->toBe('counter');
    expect($intention->dedicatee?->name)->toBe('Juan');

    $mass->refresh();
    expect($mass->occupied)->toBe(1);
});

it('prevents scheduling on the same day', function () {
    $user = createSecretaryUser();
    $mass = MassInstance::factory()->create([
        'starts_at' => now()->setTime(10, 0),
        'is_special' => false,
        'capacity' => 5,
        'occupied' => 0,
    ]);

    $response = $this->actingAs($user)
        ->from(route('admin.masses.show', $mass))
        ->post(route('admin.intentions.store', $mass), [
            'type' => 'rezada',
            'payment_method' => 'cash',
        ]);

    $response->assertRedirect(route('admin.masses.show', $mass));
    $response->assertSessionHasErrors('mass');
    expect(Intention::count())->toBe(0);
});

it('requires a receipt for transfer payments when not prepaid', function () {
    Storage::fake('public');
    $user = createSecretaryUser();
    $mass = MassInstance::factory()->create([
        'starts_at' => now()->addDay()->setTime(7, 0),
        'is_special' => false,
        'capacity' => 5,
        'occupied' => 0,
    ]);

    $response = $this->actingAs($user)
        ->from(route('admin.masses.show', $mass))
        ->post(route('admin.intentions.store', $mass), [
            'type' => 'rezada',
            'payment_method' => 'transfer',
            'is_prepaid' => false,
        ]);

    $response->assertRedirect(route('admin.masses.show', $mass));
    $response->assertSessionHasErrors('receipt');
});

it('registers novena extras only for allowed ordinary masses', function () {
    $user = createSecretaryUser();
    $baseMass = MassInstance::factory()->create([
        'starts_at' => now()->addDay()->setTime(6, 0),
        'is_special' => false,
        'capacity' => 4,
        'occupied' => 0,
    ]);

    $selectedExtra = MassInstance::factory()->create([
        'starts_at' => now()->addDays(2)->setTime(6, 0),
        'is_special' => false,
        'capacity' => 4,
        'occupied' => 0,
    ]);

    $ordinaryLater = MassInstance::factory()->create([
        'starts_at' => now()->addDays(4)->setTime(6, 0),
        'is_special' => false,
        'capacity' => 4,
        'occupied' => 0,
    ]);

    $rosary = MassInstance::factory()->create([
        'starts_at' => now()->addDays(3)->setTime(6, 0),
        'is_special' => true,
        'special_category' => 'rosario',
        'capacity' => 30,
        'occupied' => 0,
    ]);

    $response = $this->actingAs($user)
        ->post(route('admin.intentions.store', $baseMass), [
            'type' => 'rezada',
            'payment_method' => 'cash',
            'novena' => 1,
            'times' => 3,
            'extra_mass_ids' => [$selectedExtra->id],
        ]);

    $response->assertRedirect(route('admin.masses.show', $baseMass));

    $intentions = Intention::orderBy('mass_instance_id')->get();
    expect($intentions)->toHaveCount(3);

    $expectedMasses = collect([$baseMass->id, $selectedExtra->id, $ordinaryLater->id])->sort()->values();
    expect($intentions->pluck('mass_instance_id')->sort()->values())->toEqual($expectedMasses);
    expect($intentions->pluck('group_code')->unique())->toHaveCount(1);
    expect(Intention::where('mass_instance_id', $rosary->id)->exists())->toBeFalse();
});
