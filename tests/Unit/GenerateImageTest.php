<?php

use Recraft\Recraft;
use Recraft\Tests\TestCase;

test('can generate image', function () {
    Recraft::setKey('your_api_key');
    try {
        /**
         * @var \Recraft\Recraft
         */
        $recraft = app()->make(Recraft::class);
        $response = $recraft->generateImages([
            "prompt" => "race car on a track"
        ]);
        //$image = $response["data"][0]["b64_json"];
        expect(isset($response["data"]))->toBeTrue();
    } catch (Exception $e) {
        echo $e->getMessage();
    }
});
