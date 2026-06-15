<?php

namespace Gabylis\ApiFoundation;

use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\Filesystem;
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

            // Publish OpenAPI info — stub is copied as .php so swagger-php
            // never scans the original inside the vendor folder
            $this->callAfterResolving('files', function (Filesystem $files) {
                $destination = app_path('OpenApi/OpenApiInfo.php');
                if (!$files->exists($destination)) {
                    $files->ensureDirectoryExists(dirname($destination));
                    $files->copy(
                        __DIR__ . '/Publishes/OpenApiInfo.stub',
                        $destination
                    );
                }
            });

            $this->publishes([
                __DIR__ . '/Publishes/OpenApiInfo.stub' => app_path('OpenApi/OpenApiInfo.php'),
            ], 'api-foundation-openapi');

            // Publish stubs for the controller generator
            $this->publishes([
                __DIR__ . '/Stubs' => base_path('stubs/api-foundation'),
            ], 'api-foundation-stubs');

            $this->commands([
                MakeApiControllerCommand::class,
            ]);
        }
    }
}
