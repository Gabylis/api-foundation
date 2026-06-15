# gabylis/api-foundation

Opinionated Laravel API base package: structured JSON responses, PHP 8 OpenAPI attribute scaffolding, and an Artisan generator for documented controllers.

Built from real production patterns on Laravel + l5-swagger projects.

---

## Requirements

- PHP 8.1+
- Laravel 10, 11, or 12
- `darkaonline/l5-swagger` ^8.5

---

## Installation

### 1. Install the package

```bash
composer require gabylis/api-foundation
```

### 2. Install and publish l5-swagger

```bash
composer require darkaonline/l5-swagger
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
```

### 3. Publish the OpenAPI info file

```bash
php artisan vendor:publish --tag=api-foundation-openapi
```

This creates `app/OpenApi/OpenApiInfo.php` — edit it to set your API title, version, server URL, and security scheme:

```php
#[OA\Info(
    title: 'My API',
    version: '1.0.0',
    description: 'My API description',
    contact: new OA\Contact(email: 'dev@mycompany.com')
)]
#[OA\Server(url: '/api', description: 'Local')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer'
)]
class OpenApiInfo {}
```

### 4. Configure l5-swagger scanning

In `config/l5-swagger.php`, set the `annotations` path inside `documentations.default.paths`:

```php
'annotations' => [
    base_path('app'),
],
```

> **Note:** You only need to scan `app/` — the published `OpenApiInfo.php` lives there.
> Do **not** add `vendor/gabylis/api-foundation/src` to the annotations paths, as the
> package no longer contains any OpenAPI annotations in the vendor folder.

### 5. Generate docs

```bash
php artisan l5-swagger:generate
```

Open `http://localhost:8000/api/documentation`.

---

## What's included

### `OpenApiInfo.php` (published to `app/OpenApi/`)

Global OpenAPI metadata — edit freely after publishing. Re-generate docs after any change:

```bash
php artisan l5-swagger:generate
```

### `ApiBaseController`

Base controller with the `ApiResponse` trait. All your API controllers extend this:

```php
use Gabylis\ApiFoundation\Controllers\ApiBaseController;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Products', description: 'Product management')]
class ProductApiController extends ApiBaseController
{
    #[OA\Get(
        path: '/products',
        operationId: 'get-products',
        summary: 'List all products',
        tags: ['Products'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Products retrieved successfully'),
        ]
    )]
    public function index(): JsonResponse
    {
        $products = Product::paginate(15);
        return $this->sendPaginatedResponse($products, 'Products retrieved', ProductResource::class);
    }

    public function show(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        return $this->sendResponse(new ProductResource($product), 'Product retrieved');
    }

    public function destroy(int $id): JsonResponse
    {
        Product::findOrFail($id)->delete();
        return $this->sendSuccess('Product deleted');
    }
}
```

### `ApiFormRequest`

Base form request that always returns JSON on validation failure — no more 302 redirects from APIs:

```php
use Gabylis\ApiFoundation\Requests\ApiFormRequest;

class StoreProductRequest extends ApiFormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ];
    }
}
```

Validation error response (always JSON, status 422):

```json
{
    "success": false,
    "status": "failed",
    "message": "The name field is required. The price field is required.",
    "data": {
        "name": ["The name field is required."],
        "price": ["The price field is required."]
    }
}
```

### `make:api-controller` command

Generates a fully documented controller with PHP 8 `#[OA\...]` attributes for all CRUD methods:

```bash
php artisan make:api-controller ProductApiController
php artisan make:api-controller ProductApiController --resource=products --tag="Products"
```

Options:

| Option | Description |
|---|---|
| `--resource` | Route resource name (e.g. `products`). Defaults to snake_case of class name. |
| `--tag` | OpenAPI tag label shown in Swagger UI. |
| `--namespace` | Override default namespace (`App\Http\Controllers\Api`). |
| `--path` | Override output path (`app/Http/Controllers/Api`). |
| `--force` | Overwrite existing file. |

Then add the route and regenerate:

```bash
# routes/api.php
Route::apiResource('products', ProductApiController::class);

php artisan l5-swagger:generate
```

---

## Response envelope

**Success:**
```json
{
    "success": true,
    "status": "success",
    "message": "Products retrieved successfully",
    "data": [...]
}
```

**Paginated:**
```json
{
    "success": true,
    "status": "success",
    "message": "Products retrieved successfully",
    "data": [...],
    "meta": {
        "per_page": 15,
        "current_page": 1,
        "from": 1,
        "to": 15,
        "last_page": 4,
        "total": 60,
        "next_page_url": "...",
        "previous_page_url": null,
        "path": "...",
        "links": {...}
    }
}
```

**Error:**
```json
{
    "success": false,
    "status": "failed",
    "message": "Product not found"
}
```

---

## Available methods

| Method | Description |
|---|---|
| `sendResponse($data, $message, $status = 200)` | Standard success response |
| `sendPaginatedResponse($paginator, $message, $resourceClass = null)` | Paginated response with meta |
| `sendError($message, $data = [], $status = 404)` | Error response |
| `sendSuccess($message, $status = 200)` | Success with message only, no data |

---

## Full setup checklist

```bash
# 1. Install packages
composer require gabylis/api-foundation darkaonline/l5-swagger

# 2. Publish l5-swagger config
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"

# 3. Publish OpenAPI info file
php artisan vendor:publish --tag=api-foundation-openapi
# → Edit app/OpenApi/OpenApiInfo.php

# 4. Set annotations path in config/l5-swagger.php
# 'annotations' => [ base_path('app') ]

# 5. Generate a documented controller
php artisan make:api-controller ProductApiController --resource=products --tag="Products"

# 6. Add route
# Route::apiResource('products', ProductApiController::class);

# 7. Generate docs
php artisan l5-swagger:generate

# 8. Open http://localhost:8000/api/documentation
```

---

## Publishing the stub

To customise the controller generator template:

```bash
php artisan vendor:publish --tag=api-foundation-stubs
```

This creates `stubs/api-foundation/api-controller.stub` — edit it and the generator will use your version instead of the default.

---

## Running tests

```bash
composer install
./vendor/bin/pest
```

---

## License

MIT
