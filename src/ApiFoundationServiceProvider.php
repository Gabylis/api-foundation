<?php

namespace Gabylis\ApiFoundation;

use Illuminate\Support\ServiceProvider;
use Gabylis\ApiFoundation\Commands\MakeApiControllerCommand;

class ApiFoundationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/api-foundation.php',
            'api-foundation'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/api-foundation.php' => config_path('api-foundation.php'),
            ], 'api-foundation-config');

            $this->publishes([
                __DIR__ . '/Stubs' => base_path('stubs/api-foundation'),
            ], 'api-foundation-stubs');

            $this->commands([
                MakeApiControllerCommand::class,
            ]);
        }
    }
}
