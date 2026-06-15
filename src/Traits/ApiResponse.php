<?php

namespace Gabylis\ApiFoundation\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    public function sendResponse(mixed $data, string $message, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    public function sendPaginatedResponse(
        LengthAwarePaginator $paginator,
        string $message,
        ?string $resourceClass = null
    ): JsonResponse {
        $data = is_null($resourceClass)
            ? $paginator->items()
            : $resourceClass::collection(collect($paginator->items()));

        return response()->json([
            'success' => true,
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
            'meta'    => [
                'per_page'          => $paginator->perPage(),
                'current_page'      => $paginator->currentPage(),
                'from'              => $paginator->firstItem(),
                'to'                => $paginator->lastItem(),
                'last_page'         => $paginator->lastPage(),
                'total'             => $paginator->total(),
                'next_page_url'     => $paginator->nextPageUrl(),
                'previous_page_url' => $paginator->previousPageUrl(),
                'path'              => $paginator->path(),
                'links'             => $paginator->getUrlRange(1, $paginator->lastPage()),
            ],
        ]);
    }

    public function sendError(string $message, array $data = [], int $status = 404): JsonResponse
    {
        $response = [
            'success' => false,
            'status'  => 'failed',
            'message' => $message,
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    public function sendSuccess(string $message, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'status'  => 'success',
            'message' => $message,
        ], $status);
    }
}
