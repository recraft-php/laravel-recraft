<?php

namespace Recraft\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Recraft\RecraftServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            RecraftServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Customize environment for your package, e.g., set up configuration or database
        $app['config']->set('recraft.api_key', 'xxxxxxxx');
    }
}
