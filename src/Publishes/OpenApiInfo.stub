<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI global metadata.
 *
 * Edit this file to customise your API title, version, server URL and
 * security scheme. It is scanned by l5-swagger alongside your controllers.
 *
 * Re-generate docs after any change:
 *   php artisan l5-swagger:generate
 */
#[OA\Info(
    title: 'My API',
    version: '1.0.0',
    description: 'API documentation',
    contact: new OA\Contact(email: 'your@email.com')
)]
#[OA\Server(
    url: '/api',
    description: 'Local server'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Enter your Bearer token'
)]
class OpenApiInfo {}
