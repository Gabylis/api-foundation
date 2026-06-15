<?php

namespace Gabylis\ApiFoundation\Requests;

use Illuminate\Support\Arr;
use Illuminate\Http\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ApiFormRequest extends FormRequest
{
    protected function failedValidation(Validator $validator): void
    {
        $errors   = $validator->errors()->toArray();
        $messages = implode(' ', Arr::flatten($errors));

        throw new HttpResponseException(
            response()->json(static::makeError($messages, $errors), Response::HTTP_UNPROCESSABLE_ENTITY)
        );
    }

    public static function makeResponse(string $message, mixed $data): array
    {
        return [
            'success' => true,
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ];
    }

    public static function makeError(string $message, array $data = []): array
    {
        $res = [
            'success' => false,
            'status'  => 'failed',
            'message' => $message,
        ];

        if (!empty($data)) {
            $res['data'] = $data;
        }

        return $res;
    }

    public static function makePaginatedResponse(string $message, $paginator, ?string $resourceClass = null): array
    {
        return [
            'success' => true,
            'status'  => 'success',
            'message' => $message,
            'data'    => is_null($resourceClass)
                ? $paginator->items()
                : $resourceClass::collection(collect($paginator->items())),
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
        ];
    }
}
