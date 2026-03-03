<?php

declare(strict_types=1);

use App\Exceptions\WarehouseSameException;

it('creates exception with default message', function (): void {
    $exception = new WarehouseSameException();

    expect($exception->getMessage())
        ->toBe('Source and destination warehouse cannot be the same.');
});
