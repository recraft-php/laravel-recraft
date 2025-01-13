<?php

namespace Recraft\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Recraft\Recraft generateImages(array $options)
 * @method static \Recraft\Recraft model(string $model)
 */
class Recraft extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'recraft';
    }
}
