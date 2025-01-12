<?php

namespace Recraft;

use Illuminate\Support\ServiceProvider;

class RecraftServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/recraft.php',
            'recraft'
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->addPublishes();
        //$this->addCommands();
    }

    /**
     * Register Recraft's publishable files.
     *
     * @return void
     */
    public function addPublishes()
    {
        $this->publishes([
            __DIR__ . '/../config/recraft.php' => config_path('recraft.php')
        ], 'recraft.config');
    }
}
