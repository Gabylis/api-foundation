<?php

namespace Gabylis\ApiFoundation\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class MakeApiControllerCommand extends Command
{
    protected $signature = 'make:api-controller
                            {name : Controller class name, e.g. UserApiController}
                            {--resource= : Route resource name, e.g. users}
                            {--tag= : OpenAPI tag label}
                            {--namespace= : Override default namespace}
                            {--path= : Override output path}
                            {--force : Overwrite existing file}';

    protected $description = 'Generate a documented API controller with PHP 8 OpenAPI attributes (gabylis/api-foundation)';

    public function __construct(private Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $name      = $this->argument('name');
        $className = Str::studly(Str::endsWith($name, 'Controller') ? $name : $name . 'Controller');

        $namespace = $this->option('namespace')
            ?? config('api-foundation.generator.namespace', 'App\\Http\\Controllers\\Api');
        $basePath  = $this->option('path')
            ?? config('api-foundation.generator.path', 'app/Http/Controllers/Api');

        $resource = $this->option('resource')
            ?? Str::snake(Str::beforeLast($className, 'Controller'));
        $route    = Str::slug(Str::replace('_', '-', $resource));
        $tag      = $this->option('tag')
            ?? Str::title(Str::replace(['-', '_'], ' ', $resource));

        $outputPath = base_path($basePath . '/' . $className . '.php');

        if ($this->files->exists($outputPath) && !$this->option('force')) {
            $this->error("Controller already exists: {$outputPath}");
            $this->line('Use --force to overwrite.');
            return self::FAILURE;
        }

        $this->files->ensureDirectoryExists(dirname($outputPath));

        $contents = strtr($this->getStub(), [
            '{{ namespace }}' => $namespace,
            '{{ class }}'     => $className,
            '{{ resource }}'  => Str::title(Str::replace(['-', '_'], ' ', $resource)),
            '{{ route }}'     => $route,
            '{{ route-id }}'  => $route,
            '{{ tag }}'       => $tag,
        ]);

        $this->files->put($outputPath, $contents);

        $this->info("Controller created: {$outputPath}");
        $this->newLine();
        $this->line('Next steps:');
        $this->line("  1. Add to routes/api.php:");
        $this->line("     Route::apiResource('{$route}', {$className}::class);");
        $this->line('  2. Fill in the method bodies.');
        $this->line('  3. Run: php artisan l5-swagger:generate');

        return self::SUCCESS;
    }

    private function getStub(): string
    {
        $custom = base_path('stubs/api-foundation/api-controller.stub');

        return $this->files->exists($custom)
            ? $this->files->get($custom)
            : $this->files->get(__DIR__ . '/../Stubs/api-controller.stub');
    }
}
