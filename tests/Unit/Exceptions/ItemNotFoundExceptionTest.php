<?php

declare(strict_types=1);

use App\Exceptions\ItemNotFoundException;

it('creates exception with item type, context and reason', function (): void {
    $exception = new ItemNotFoundException('Product', 'original order', 'Product is not part of the original order or batch does not match.');

    expect($exception->getMessage())
        ->toBe('Product is not part of the original order. Product is not part of the original order or batch does not match.');
    expect($exception->itemType)->toBe('Product');
    expect($exception->context)->toBe('original order');
    expect($exception->reason)->toBe('Product is not part of the original order or batch does not match.');
});
