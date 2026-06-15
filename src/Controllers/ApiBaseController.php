<?php

namespace Gabylis\ApiFoundation\Controllers;

use Illuminate\Routing\Controller;
use Gabylis\ApiFoundation\Traits\ApiResponse;
use OpenApi\Attributes as OA;

#[OA\Info(title: 'Laravel API', version: '1.0.0')]
#[OA\Server(url: '/api')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer'
)]
abstract class ApiBaseController extends Controller
{
    use ApiResponse;
}
