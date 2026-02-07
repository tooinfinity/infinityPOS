<?php

declare(strict_types=1);

use App\Enums\ReturnStatusEnum;

it('sale status to array', function (): void {
    expect(ReturnStatusEnum::toArray())->toBeArray();
});

it('sale status label', function (): void {
    $value1 = 'Pending';
    $value2 = 'Completed';

    expect(ReturnStatusEnum::Pending->label())->toBe($value1)
        ->and(ReturnStatusEnum::Completed->label())->toBe($value2);
});
