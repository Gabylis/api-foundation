<?php

namespace Gabylis\ApiFoundation\Controllers;

use Illuminate\Routing\Controller;
use Gabylis\ApiFoundation\Traits\ApiResponse;

/**
 * Base controller for all API controllers.
 *
 * OpenAPI info (#[OA\Info], #[OA\Server], #[OA\SecurityScheme]) lives in
 * app/OpenApi/OpenApiInfo.php — published via:
 *   php artisan vendor:publish --tag=api-foundation-openapi
 */
abstract class ApiBaseController extends Controller
{
    use ApiResponse;
}
