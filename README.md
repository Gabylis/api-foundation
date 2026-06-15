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

Publish the config (optional):

```bash
php artisan vendor:publish --tag=api-foundation-config
```

Publish the stubs to customise the generator template (optional):

```bash
php artisan vendor:publish --tag=api-foundation-stubs
```

---

## What's included

### `ApiBaseController`

Base controller with `#[OA\Info]`, `#[OA\Server]`, and `#[OA\SecurityScheme]` registered once for the whole app. All your API controllers extend this.

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

Base form request that always returns JSON on validation failure — no more 302 redirects from APIs.

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

Then add the route:

```php
Route::apiResource('products', ProductApiController::class);
```

And generate the docs:

```bash
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

## Running tests

```bash
composer install
./vendor/bin/pest
```

---

## License

MIT
