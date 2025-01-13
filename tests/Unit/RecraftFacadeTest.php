<?php

use Recraft\Facade\Recraft as FacadeRecraft;
use Recraft\Recraft;
use Recraft\Tests\TestCase;

test('can access recraft through facade', function () {
    $recraft = FacadeRecraft::model('recraftv3');
    expect($recraft)->toBeInstanceOf(Recraft::class);
});
