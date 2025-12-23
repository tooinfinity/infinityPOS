<?php

declare(strict_types=1);

use App\Data\Pos\AddProductToCartData;

test('it creates data with product id and quantity', function (): void {
    $data = new AddProductToCartData(
        product_id: 123,
        quantity: 5
    );

    expect($data->product_id)->toBe(123);
    expect($data->quantity)->toBe(5);
});

test('it creates data with defaults', function (): void {
    $data = AddProductToCartData::defaults();

    expect($data->product_id)->toBe(0);
    expect($data->quantity)->toBe(1);
});

test('it validates required product id', function (): void {
    $validator = validator(['quantity' => 1], [
        'product_id' => 'required|integer',
        'quantity' => 'integer|min:1',
    ]);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('product_id'))->toBeTrue();
});

test('it validates minimum quantity', function (): void {
    $validator = validator([
        'product_id' => 1,
        'quantity' => 0,
    ], [
        'product_id' => 'required|integer',
        'quantity' => 'integer|min:1',
    ]);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('quantity'))->toBeTrue();
});

test('it accepts valid data', function (): void {
    $validator = validator([
        'product_id' => 1,
        'quantity' => 5,
    ], [
        'product_id' => 'required|integer',
        'quantity' => 'integer|min:1',
    ]);

    expect($validator->fails())->toBeFalse();
});

test('it can be created from array', function (): void {
    $data = AddProductToCartData::from([
        'product_id' => 456,
        'quantity' => 10,
    ]);

    expect($data->product_id)->toBe(456);
    expect($data->quantity)->toBe(10);
});

test('it can be converted to array', function (): void {
    $data = new AddProductToCartData(
        product_id: 789,
        quantity: 3
    );

    $array = $data->toArray();

    expect($array)->toBe([
        'product_id' => 789,
        'quantity' => 3,
    ]);
});
