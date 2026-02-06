<?php

declare(strict_types=1);

use App\Enums\SaleStatusEnum;

it('sale status to array', function (): void {
    expect(SaleStatusEnum::toArray())->toBeArray();
});

it('sale status label', function (): void {
    $value1 = 'Pending';
    $value2 = 'Completed';
    $value3 = 'Cancelled';

    expect(SaleStatusEnum::Pending->label())->toBe($value1)
        ->and(SaleStatusEnum::Completed->label())->toBe($value2)
        ->and(SaleStatusEnum::Cancelled->label())->toBe($value3);
});
