<?php

use App\Models\Intention;
use App\Models\MassInstance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

beforeEach(function(){
    if (config('database.default') === 'sqlite' && ! extension_loaded('pdo_sqlite')) {
        $this->markTestSkipped('pdo_sqlite extension not available');
    }
});

it('enforces single dedicatee per intention at DB level', function () {
    $mass = MassInstance::factory()->create();
    $intention = Intention::factory()->create(['mass_instance_id' => $mass->id]);
    // create first dedicatee
    $intention->dedicatees()->create(['name' => 'Juan']);
    // try to insert second dedicatee directly to hit unique index
    $this->expectException(Throwable::class);
    DB::table('intention_dedicatees')->insert([
        'intention_id' => $intention->id,
        'name' => 'Maria',
        'relation' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

it('logs history on update and soft deletes with justification', function () {
    Role::firstOrCreate(['name' => 'Secretaria']);
    $user = User::factory()->create();
    $user->assignRole('Secretaria');

    $mass = MassInstance::factory()->create(['capacity'=>10,'occupied'=>0]);
    $intention = Intention::factory()->create([
        'mass_instance_id' => $mass->id,
        'type' => 'rezada',
        'payment_method' => 'cash',
        'status' => 'confirmed',
        'channel' => 'counter',
    ]);

    // Update
    $respU = $this->actingAs($user)->put(route('admin.intentions.update', $intention), [
        'type' => 'cantada',
        'public_text' => 'Texto',
        'donor_name' => 'Donante',
        'phone' => '555',
        'email' => 'a@b.com',
        'payment_method' => 'cash',
        'dedicatee' => 'Pedro',
        'justification' => 'Corrección de datos',
    ]);
    $respU->assertRedirect();
    $intention->refresh();
    // Amount is stored as DECIMAL; cast may return string. Compare numerically.
    expect((float)$intention->amount)->toBe(150.0);
    expect($intention->dedicatee)->not->toBeNull();
    expect($intention->histories()->count())->toBe(1);

    // Destroy (soft delete)
    $respD = $this->actingAs($user)->delete(route('admin.intentions.destroy', $intention), [
        'justification' => 'Cancelación solicitada',
    ]);
    $respD->assertRedirect();
    $intention->refresh();
    expect($intention->deleted_at)->not->toBeNull();
    expect($intention->histories()->count())->toBe(2);
});
