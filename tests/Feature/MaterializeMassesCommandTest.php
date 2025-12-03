<?php

use App\Models\MassInstance;
use App\Models\MassTimeTemplate;
use Illuminate\Support\Carbon;

it('materializes up to given until date idempotently', function(){
    // Ensure there is at least one active template
    foreach (range(1,5) as $dow) {
        MassTimeTemplate::create(['dow'=>$dow,'time'=>'07:00','capacity'=>10,'active'=>true]);
    }
    $this->artisan('masses:materialize', ['--until' => '2026-12-31'])
        ->assertSuccessful();
    $count1 = MassInstance::count();
    expect($count1)->toBeGreaterThan(0);
    $this->artisan('masses:materialize', ['--until' => '2026-12-31'])
        ->assertSuccessful();
    $count2 = MassInstance::count();
    expect($count2)->toBe($count1);
});
