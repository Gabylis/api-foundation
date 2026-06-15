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
            // Publish config
            $this->publishes([
                __DIR__ . '/../config/api-foundation.php' => config_path('api-foundation.php'),
            ], 'api-foundation-config');

            // Publish OpenAPI info file — user edits this to set title, version, server, security
            $this->publishes([
                __DIR__ . '/Publishes/OpenApiInfo.php' => app_path('OpenApi/OpenApiInfo.php'),
            ], 'api-foundation-openapi');

            // Publish stubs — user can customise the controller generator template
            $this->publishes([
                __DIR__ . '/Stubs' => base_path('stubs/api-foundation'),
            ], 'api-foundation-stubs');

            $this->commands([
                MakeApiControllerCommand::class,
            ]);
        }
    }
}
