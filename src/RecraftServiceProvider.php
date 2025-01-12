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
        //$this->app->singleton(RecraftManager::class);
        //$this->app->singleton(RecraftMutatorManager::class);
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
        /*RecraftMoney::formatter('recraft', function () {
            return new RecraftMoneyFormatter(
                new \NumberFormatter('en_US', \NumberFormatter::CURRENCY)
            );
        });

        Recraft::locker('recraft', RecraftLocker::class);

        Recraft::action('transfer', RecraftTransferAction::class);
        Recraft::action('credit_debit', RecraftCreditDebitAction::class);
        */
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
