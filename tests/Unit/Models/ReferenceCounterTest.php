<?php

declare(strict_types=1);

use App\Models\ReferenceCounter;

test('to array', function (): void {
    $counter = ReferenceCounter::factory()->create()->refresh();

    expect(array_keys($counter->toArray()))
        ->toBe([
            'key',
            'last_value',
        ]);
});

test('has string primary key', function (): void {
    $counter = ReferenceCounter::factory()->create(['key' => 'purchase_ref']);

    expect($counter->getKey())->toBe('purchase_ref')
        ->and($counter->getKeyType())->toBe('string')
        ->and($counter->getIncrementing())->toBeFalse();
});

test('does not use timestamps', function (): void {
    $counter = ReferenceCounter::factory()->create();

    expect($counter->timestamps)->toBeFalse()
        ->and($counter->toArray())->not->toHaveKey('created_at')
        ->and($counter->toArray())->not->toHaveKey('updated_at');
});

test('factory can set custom key', function (): void {
    $counter = ReferenceCounter::factory()->withKey('custom_key')->create();

    expect($counter->key)->toBe('custom_key');
});

test('factory can set starting value', function (): void {
    $counter = ReferenceCounter::factory()->startingAt(100)->create();

    expect($counter->last_value)->toBe(100);
});
