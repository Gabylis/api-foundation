<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Gabylis\ApiFoundation\Requests\ApiFormRequest;

test('makeResponse returns success envelope', function () {
    $r = ApiFormRequest::makeResponse('Done', ['id' => 1]);

    expect($r['success'])->toBeTrue()
        ->and($r['status'])->toBe('success')
        ->and($r['message'])->toBe('Done')
        ->and($r['data'])->toBe(['id' => 1]);
});

test('makeError returns failed envelope without data', function () {
    $r = ApiFormRequest::makeError('Fail');

    expect($r['success'])->toBeFalse()
        ->and($r['status'])->toBe('failed')
        ->and($r)->not->toHaveKey('data');
});

test('makeError includes data when provided', function () {
    $r = ApiFormRequest::makeError('Invalid', ['name' => ['required']]);

    expect($r['data'])->toBe(['name' => ['required']]);
});

test('makePaginatedResponse returns meta and data', function () {
    $p = new LengthAwarePaginator(collect([['id' => 1]]), 20, 5, 1, [
        'path' => 'http://localhost/api/test',
    ]);

    $r = ApiFormRequest::makePaginatedResponse('List', $p);

    expect($r['success'])->toBeTrue()
        ->and($r)->toHaveKeys(['data', 'meta'])
        ->and($r['meta']['total'])->toBe(20)
        ->and($r['meta']['per_page'])->toBe(5);
});
