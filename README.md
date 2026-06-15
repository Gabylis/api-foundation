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

```bash
composer require gabylis/api-foundation
```

### Publish everything

```bash
# OpenAPI global metadata (title, version, server, security) — edit this file
php artisan vendor:publish --tag=api-foundation-openapi

# Package config (optional)
php artisan vendor:publish --tag=api-foundation-config

# Controller generator stub (optional — customise the template)
php artisan vendor:publish --tag=api-foundation-stubs
```

### Configure l5-swagger to scan the published file

In `config/l5-swagger.php`, add both your `app/` folder and the published OpenApi folder to the annotations path:

```php
'annotations' => [
    base_path('app'),
    base_path('vendor/gabylis/api-foundation/src'),
],
```

---

## What's included

### `OpenApiInfo.php` (published to `app/OpenApi/`)

Global OpenAPI metadata — edit freely after publishing:

```php
#[OA\Info(
    title: 'My API',
    version: '2.0.0',
    description: 'My API description',
    contact: new OA\Contact(email: 'dev@mycompany.com')
)]
#[OA\Server(url: '/api', description: 'Production')]
#[OA\Server(url: '/api/v2', description: 'Staging')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer'
)]
class OpenApiInfo {}
```

Re-generate docs after any change:

```bash
php artisan l5-swagger:generate
```

### `ApiBaseController`

Base controller with the `ApiResponse` trait. All your API controllers extend this:

```php
use Gabylis\ApiFoundation\Controllers\ApiBaseController;
use OpenApi\Attributes as OA;

class ProductApiController extends ApiBaseController
{
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

Validation error response:

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

Then add the route and generate docs:

```bash
# routes/api.php
Route::apiResource('products', ProductApiController::class);

# Generate Swagger docs
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
# 1. Install
composer require gabylis/api-foundation

# 2. Publish OpenAPI info
php artisan vendor:publish --tag=api-foundation-openapi
# → Edit app/OpenApi/OpenApiInfo.php with your title, version, contact

# 3. Install and publish l5-swagger
composer require darkaonline/l5-swagger
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
# → In config/l5-swagger.php set annotations to include app/ and vendor/gabylis/api-foundation/src

# 4. Generate a controller
php artisan make:api-controller ProductApiController --resource=products --tag="Products"

# 5. Add route
# Route::apiResource('products', ProductApiController::class);

# 6. Generate docs
php artisan l5-swagger:generate

# 7. Open http://localhost:8000/api/documentation
```

---

## Running tests

```bash
composer install
./vendor/bin/pest
```

---

## License

MIT
