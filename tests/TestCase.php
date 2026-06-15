<?php

namespace Gabylis\ApiFoundation\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Gabylis\ApiFoundation\ApiFoundationServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [ApiFoundationServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));
    }
}
