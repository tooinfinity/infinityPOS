<?php

declare(strict_types=1);

use App\Enums\PurchaseStatusEnum;

it('Purchase status to array', function (): void {
    expect(PurchaseStatusEnum::toArray())->toBeArray();
});

it('Purchase status label', function (): void {
    $value1 = 'Pending';
    $value2 = 'Ordered';
    $value3 = 'Received';
    $value4 = 'Cancelled';

    expect(PurchaseStatusEnum::Pending->label())->toBe($value1)
        ->and(PurchaseStatusEnum::Ordered->label())->toBe($value2)
        ->and(PurchaseStatusEnum::Received->label())->toBe($value3)
        ->and(PurchaseStatusEnum::Cancelled->label())->toBe($value4);
});
