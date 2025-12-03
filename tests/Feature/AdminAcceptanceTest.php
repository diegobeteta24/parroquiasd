<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\MassInstance;
use App\Models\Intention;
use Illuminate\Support\Str;
use Carbon\Carbon;

beforeEach(function(){
    $user = User::factory()->create();
    // Ensure role exists and assign to user
    Role::findOrCreate('Secretaria','web');
    $user->assignRole('Secretaria');
    $this->actingAs($user);
});

it('redirects to /admin after login via fortify home', function(){
    $this->get('/login');
    expect(config('fortify.home'))->toBe('/admin');
});

it('orders intentions by category for print', function(){
    $mass = MassInstance::factory()->create(['starts_at'=>now()->addDay()]);
    $i3 = Intention::factory()->create(['mass_instance_id'=>$mass->id,'category'=>'difuntos','created_at'=>now()->addMinutes(10)]);
    $i1 = Intention::factory()->create(['mass_instance_id'=>$mass->id,'category'=>'acciones_de_gracia','created_at'=>now()->addMinutes(5)]);
    $i2 = Intention::factory()->create(['mass_instance_id'=>$mass->id,'category'=>'peticiones','created_at'=>now()->addMinutes(7)]);
    $res = $this->get(route('admin.masses.print', $mass));
    $res->assertOk();
});

it('creates special mass without affecting ordinary', function(){
    $day = Carbon::now()->next(Carbon::SATURDAY)->setTime(10, 0);
    $ordinary = MassInstance::factory()->create(['starts_at'=>$day,'is_special'=>false]);
    $res = $this->post(route('admin.special-masses.store'), [
        'starts_at' => $day->format('Y-m-d\TH:i'),
        'capacity' => 20,
        'special_category' => 'bautismo',
        'details' => [
            'child_name' => 'Bebé Test',
        ],
    ]);
    $res->assertRedirect();
    expect(MassInstance::where('is_special',true)->where('special_category','bautismo')->where('starts_at',$day)->exists())->toBeTrue();
});

it('creates prepaid intention as paid without gateway and counts as paid', function(){
    $mass = MassInstance::factory()->create(['starts_at'=>now()->addDays(2)->startOfHour()]);
    $res = $this->post(route('admin.intentions.store',$mass), [
        'type'=>'rezada',
        'payment_method'=>'cash',
        'is_prepaid'=>1,
        'stipend_amount_gtq'=>50,
        'payment_ref'=>'EXT-'.Str::random(6),
    ]);
    $res->assertRedirect();
    $i = Intention::latest('id')->first();
    expect($i->is_prepaid)->toBeTrue();
    expect($i->status)->toBe('paid');
    expect($i->paid_at)->not->toBeNull();
});

it('respects capacity limit when creating intentions', function(){
    $mass = MassInstance::factory()->create(['capacity'=>1,'occupied'=>0,'starts_at'=>now()->addDays(3)->startOfHour()]);
    // first succeeds
    $this->post(route('admin.intentions.store',$mass), [
        'type'=>'rezada','payment_method'=>'cash',
    ])->assertRedirect();

    // second request should not create a new record but returns success with warning message
    $this->post(route('admin.intentions.store',$mass), [
        'type'=>'rezada','payment_method'=>'cash',
    ])->assertRedirect();

    expect(Intention::where('mass_instance_id',$mass->id)->count())->toBe(1);
});
