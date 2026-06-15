<?php

use Illuminate\Support\Facades\File;

$generated = fn () => app_path('Http/Controllers/Api/TestItemController.php');

beforeEach(fn () => File::delete($generated()));
afterEach(fn () => File::delete($generated()));

test('generates a controller file', function () use ($generated) {
    $this->artisan('make:api-controller', ['name' => 'TestItemController'])
        ->assertSuccessful();

    expect(File::exists($generated()))->toBeTrue();
});

test('generated file contains correct class name', function () use ($generated) {
    $this->artisan('make:api-controller', ['name' => 'TestItemController'])
        ->assertSuccessful();

    expect(File::get($generated()))->toContain('class TestItemController');
});

test('generated file extends ApiBaseController', function () use ($generated) {
    $this->artisan('make:api-controller', ['name' => 'TestItemController'])
        ->assertSuccessful();

    expect(File::get($generated()))->toContain('extends ApiBaseController');
});

test('generated file has all 5 CRUD methods', function () use ($generated) {
    $this->artisan('make:api-controller', ['name' => 'TestItemController'])
        ->assertSuccessful();

    $c = File::get($generated());
    expect($c)
        ->toContain('public function index(')
        ->toContain('public function show(')
        ->toContain('public function store(')
        ->toContain('public function update(')
        ->toContain('public function destroy(');
});

test('generated file has OpenAPI attributes', function () use ($generated) {
    $this->artisan('make:api-controller', ['name' => 'TestItemController'])
        ->assertSuccessful();

    $c = File::get($generated());
    expect($c)
        ->toContain('use OpenApi\Attributes as OA')
        ->toContain('#[OA\Get(')
        ->toContain('#[OA\Post(')
        ->toContain('#[OA\Put(')
        ->toContain('#[OA\Delete(');
});

test('fails without --force when file exists', function () use ($generated) {
    $this->artisan('make:api-controller', ['name' => 'TestItemController'])->assertSuccessful();
    $this->artisan('make:api-controller', ['name' => 'TestItemController'])->assertFailed();
});

test('overwrites with --force', function () use ($generated) {
    $this->artisan('make:api-controller', ['name' => 'TestItemController'])->assertSuccessful();
    $this->artisan('make:api-controller', ['name' => 'TestItemController', '--force' => true])
        ->assertSuccessful();

    expect(File::exists($generated()))->toBeTrue();
});

test('uses custom tag option', function () use ($generated) {
    $this->artisan('make:api-controller', [
        'name'  => 'TestItemController',
        '--tag' => 'My Items',
    ])->assertSuccessful();

    expect(File::get($generated()))->toContain("'My Items'");
});
