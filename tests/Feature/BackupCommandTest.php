<?php

use Illuminate\Support\Facades\Artisan;

it('runs backup command and returns an exit code', function () {
    $exitCode = Artisan::call('backup:database --keep=1');
    expect(is_int($exitCode))->toBeTrue();
});
