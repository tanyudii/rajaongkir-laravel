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
        $this->mergeConfigFrom(__DIR__ . "/../config/rajaongkir-laravel.php", "rajaongkir-laravel");

        $this->app->bind("rajaongkir-service", function () {
            return new RajaOngkirService;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    __DIR__ . "/../config/rajaongkir-laravel.php" => config_path(
                        "rajaongkir-laravel.php"
                    ),
                ],
                "rajaongkir-laravel-config"
            );
        }
    }
}
