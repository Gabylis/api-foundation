<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Gabylis\ApiFoundation\Traits\ApiResponse;

$ctrl = new class {
    use ApiResponse;
};

// sendResponse
test('sendResponse returns 200 success envelope', function () use ($ctrl) {
    $r = $ctrl->sendResponse(['id' => 1], 'OK');

    expect($r)->toBeInstanceOf(JsonResponse::class);
    $b = $r->getData(true);
    expect($b['success'])->toBeTrue()
        ->and($b['status'])->toBe('success')
        ->and($b['message'])->toBe('OK')
        ->and($b['data'])->toBe(['id' => 1])
        ->and($r->getStatusCode())->toBe(200);
});

test('sendResponse accepts custom status code', function () use ($ctrl) {
    expect($ctrl->sendResponse([], 'Created', 201)->getStatusCode())->toBe(201);
});

// sendError
test('sendError returns 404 failed envelope by default', function () use ($ctrl) {
    $r = $ctrl->sendError('Not found');
    $b = $r->getData(true);

    expect($b['success'])->toBeFalse()
        ->and($b['status'])->toBe('failed')
        ->and($b['message'])->toBe('Not found')
        ->and($r->getStatusCode())->toBe(404);
});

test('sendError includes data when provided', function () use ($ctrl) {
    $r = $ctrl->sendError('Bad', ['field' => 'required'], 422);
    expect($r->getData(true)['data'])->toBe(['field' => 'required'])
        ->and($r->getStatusCode())->toBe(422);
});

test('sendError omits data key when empty', function () use ($ctrl) {
    expect($ctrl->sendError('Nope')->getData(true))->not->toHaveKey('data');
});

// sendSuccess
test('sendSuccess returns message only', function () use ($ctrl) {
    $r = $ctrl->sendSuccess('Deleted');
    $b = $r->getData(true);

    expect($b['success'])->toBeTrue()
        ->and($b['message'])->toBe('Deleted')
        ->and($b)->not->toHaveKey('data');
});

// sendPaginatedResponse
test('sendPaginatedResponse includes data and meta', function () use ($ctrl) {
    $p = new LengthAwarePaginator(collect([['id' => 1]]), 10, 5, 1, [
        'path' => 'http://localhost/api/items',
    ]);

    $r = $ctrl->sendPaginatedResponse($p, 'Items');
    $b = $r->getData(true);

    expect($b)->toHaveKeys(['success', 'status', 'message', 'data', 'meta']);
});

test('sendPaginatedResponse meta has all pagination keys', function () use ($ctrl) {
    $p = new LengthAwarePaginator(collect([]), 0, 15, 1, [
        'path' => 'http://localhost/api/items',
    ]);

    $meta = $ctrl->sendPaginatedResponse($p, 'Items')->getData(true)['meta'];

    expect($meta)->toHaveKeys([
        'per_page', 'current_page', 'from', 'to',
        'last_page', 'total', 'next_page_url', 'previous_page_url', 'path', 'links',
    ]);
});
