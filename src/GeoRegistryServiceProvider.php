<?php

namespace YourVendor\GeoRegistry;

use Illuminate\Support\ServiceProvider;

class GeoRegistryServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/georegistry.php' => $this->app->configPath('georegistry.php'),
        ], 'config');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/georegistry.php', 'georegistry'
        );
    }
}
