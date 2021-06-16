<?php

namespace Tanyudii\RajaOngkirLaravel;

use Illuminate\Support\ServiceProvider;
use Tanyudii\RajaOngkirLaravel\Services\RajaOngkirService;

class RajaOngkirLaravelServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->alias('rajaongkir-laravel', RajaOngkirService::class);
        $this->app->singleton('rajaongkir-laravel', function () {
            return new RajaOngkirService;
        });

        $this->registerPublishing();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    private function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            // Lumen lacks a config_path() helper, so we use base_path()
            $this->publishes([
                __DIR__.'/../config/rajaongkir-laravel.php' => base_path('config/rajaongkir-laravel.php'),
            ], 'laravel-rajaongkir-config');
        }
    }
}
