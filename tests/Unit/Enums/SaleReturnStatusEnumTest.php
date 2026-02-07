<?php

declare(strict_types=1);

use App\Enums\SaleReturnStatusEnum;

it('sale status to array', function (): void {
    expect(SaleReturnStatusEnum::toArray())->toBeArray();
});

it('sale status label', function (): void {
    $value1 = 'Pending';
    $value2 = 'Completed';

    expect(SaleReturnStatusEnum::Pending->label())->toBe($value1)
        ->and(SaleReturnStatusEnum::Completed->label())->toBe($value2);
});
